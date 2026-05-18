<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\BannedWord;

class ContentValidationController extends Controller
{
    /**
     * Valida si un texto es apropiado para un contexto estudiantil.
     * Actúa como proxy hacia la API de Groq para no exponer la API key en el frontend.
     *
     * El servicio esta registrado en config/services.php
     *
     * La ruta se encuentra en /api
     *
     * POST /api/validate-content
     * Body: { "content": "texto a validar" }
     */
    public function validate(Request $request)
    {
        $request->validate([
            'title'   => 'nullable|string|min:3|max:200',
            'content' => 'required|string|min:1|max:5000',
        ]);

        $title   = $request->input('title');
        $content = $request->input('content');

        try {
            // 1. Validar Título (si existe)
            if ($title) {
                $titleResult = $this->moderateText($title, 'Título');
                if (!$titleResult['es_apropiado']) {
                    return response()->json(array_merge($titleResult, ['status' => 'success']));
                }
            }

            // 2. Validar Contenido
            $contentResult = $this->moderateText($content, 'Contenido');
            return response()->json(array_merge($contentResult, ['status' => 'success']));

        } catch (\Exception $e) {
            Log::error('Moderación fallida: ' . $e->getMessage());
            return response()->json([
                'status'             => 'success',
                'es_apropiado'       => true,
                'palabras_detectadas' => [],
                'motivo'             => 'No se pudo validar automáticamente.',
                'fuente'             => 'error_fallback',
            ]);
        }
    }

    /**
     * Helper para moderar un texto (Local + IA)
     */
    private function moderateText($text, $fieldName)
    {
        // ── 1. Filtro Local (Banned Words Cache) ──
        $bannedWords = Cache::remember('banned_words', 3600, fn() =>
            BannedWord::pluck('word')->toArray()
        );

        $textLower = mb_strtolower($text);
        $foundBanned = array_filter($bannedWords, fn($word) =>
            str_contains($textLower, mb_strtolower($word))
        );

        if (!empty($foundBanned)) {
            return [
                'es_apropiado'       => false,
                'palabras_detectadas' => array_values($foundBanned),
                'motivo'             => "El campo $fieldName contiene palabras inapropiadas.",
                'fuente'             => 'cache_local',
            ];
        }

        // ── 2. Filtro IA (Groq) ──
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.groq.api_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'       => 'llama-3.3-70b-versatile',
            'temperature' => 0.1,
            'max_tokens'  => 300,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => <<<SYSTEM
            Eres un moderador experto de un foro educativo (estudiantes y profesores).
            Tu función es detectar contenido GENUINAMENTE inapropiado analizando la INTENCIÓN y el CONTEXTO.

            RECHAZA (es_apropiado: false) solo si:
            - Insultos o groserías dirigidas a personas.
            - Acoso, amenazas, humillación o odio.
            - Contenido sexual o spam.

            APRUEBA (es_apropiado: true) si el lenguaje se usa para:
            - Análisis lingüístico o gramatical ("¿es 'tonto' un adjetivo?").
            - Citas literarias, ejercicios académicos o términos científicos.
            - Expresiones coloquiales sin intención de ofender.

            Responde siempre en formato JSON.
            SYSTEM
                            ],
                            [
                                'role'    => 'user',
                                'content' => <<<PROMPT
            Analiza este texto ($fieldName) para el foro:
            "{$text}"

            Responde SOLO con este formato JSON:
            {
            "es_apropiado": boolean,
            "palabras_detectadas": ["solo palabras realmente maliciosas"],
            "motivo": "una frase explicando la decisión"
            }
            PROMPT
                ]
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error en la API de Groq: ' . $response->status());
        }

        $result = $response->json();
        $rawContent = $result['choices'][0]['message']['content'] ?? '{}';
        
        // Limpiar posibles bloques markdown
        $cleanJson = preg_replace('/```json|```/', '', $rawContent);
        $decoded = json_decode(trim($cleanJson), true);

        $decoded = array_merge([
            'es_apropiado' => true,
            'palabras_detectadas' => [],
            'motivo' => 'Análisis completado.'
        ], is_array($decoded) ? $decoded : []);

        // Guardar nuevas palabras detectadas
        if (!empty($decoded['palabras_detectadas'])) {
            foreach ($decoded['palabras_detectadas'] as $word) {
                BannedWord::firstOrCreate(
                    ['word' => mb_strtolower(trim($word))],
                    ['detected_at' => now(), 'context' => $text]
                );
            }
        }

        return array_merge($decoded, ['fuente' => 'groq']);
    }
}
