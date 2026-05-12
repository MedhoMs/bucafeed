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
            'content' => 'required|string|min:3|max:5000',
        ]);

        $content = $request->input('content');

        try {
            // 1. Primero checkear lista de palabras guardadas en BD (sin gastar tokens)
            $bannedWords = Cache::remember('banned_words', 3600, fn() =>
                BannedWord::pluck('word')->toArray()
            );

            $contentLower = mb_strtolower($content);
            $foundBanned = array_filter($bannedWords, fn($word) =>
                str_contains($contentLower, mb_strtolower($word))
            );

            if (!empty($foundBanned)) {
                return response()->json([
                    'status'             => 'success',
                    'es_apropiado'       => false,
                    'palabras_detectadas' => array_values($foundBanned),
                    'motivo'             => 'Contiene palabras previamente marcadas como inapropiadas.',
                    'fuente'             => 'cache_local',
                ]);
            }

            // 2. Si pasa el filtro local, consultar Groq con prompt mejorado
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
        Eres un moderador experto de un foro educativo (estudiantes y profesores de todas las edades).
        Tu única función es detectar contenido GENUINAMENTE inapropiado.

        REGLA FUNDAMENTAL: Analiza siempre la INTENCIÓN y el CONTEXTO COMPLETO, nunca palabras sueltas.

        RECHAZA solo si el texto tiene intención claramente maliciosa:
        - Insultos o groserías dirigidas a personas reales ("eres un idiota", "me cago en...")
        - Acoso, amenazas o humillación hacia alguien
        - Contenido sexual explícito
        - Discurso de odio (racismo, xenofobia, etc.)
        - Spam o publicidad sin relación educativa

        APRUEBA aunque contenga palabras que en otro contexto serían ofensivas, si:
        - Son objeto de estudio lingüístico ("analiza el adjetivo 'guapo'", "¿es 'tonto' un sustantivo?")
        - Se citan para corregirlas ("¿está mal decir 'me se olvidó'?")
        - Aparecen en ejercicios, frases de ejemplo o textos literarios
        - Son términos técnicos o científicos (anatomía, medicina, etc.)
        - Describen personajes ficticios o situaciones hipotéticas

        EJEMPLOS DE APROBACIÓN OBLIGATORIA:
        - "¿Es 'guapo' un adjetivo calificativo?" → APROPIADO (análisis gramatical)
        - "Identifica el insulto en esta frase del libro" → APROPIADO (ejercicio literario)
        - "¿Por qué 'matar' es un verbo transitivo?" → APROPIADO (gramática)
        - "El antagonista era cruel y violento" → APROPIADO (análisis literario)
        SYSTEM
                    ],
                    [
                        'role'    => 'user',
                        'content' => <<<PROMPT
        Texto a moderar en el foro educativo:

        "{$content}"

        Responde SOLO con JSON válido sin markdown ni texto adicional:
        {
        "es_apropiado": true/false,
        "palabras_detectadas": ["solo palabras con intención maliciosa real, array vacío si no hay"],
        "motivo": "explicación en una frase de la decisión tomada"
        }
        PROMPT
                    ]
                ],
            ]);

            $result = $response->json();
            $decoded = json_decode(
                $result['choices'][0]['message']['content'] ?? '{}',
                true
            );

            // Asegurar valores por defecto por si la respuesta de la IA es incompleta
            $decoded = array_merge([
                'es_apropiado' => true,
                'palabras_detectadas' => [],
                'motivo' => 'Análisis completado.'
            ], is_array($decoded) ? $decoded : []);

            // 3. Si detectó palabras nuevas problemáticas, guardarlas en BD para futuras consultas
            if (!empty($decoded['palabras_detectadas'])) {
                foreach ($decoded['palabras_detectadas'] as $word) {
                    BannedWord::firstOrCreate(
                        ['word' => mb_strtolower(trim($word))],
                        ['detected_at' => now(), 'context' => $content]
                    );
                }
            }

            return response()->json(array_merge($decoded, [
                'status' => 'success',
                'fuente' => 'groq'
            ]));

        } catch (\Exception $e) {
            Log::error('Moderación fallida: ' . $e->getMessage());
            // Fail-open: ante error técnico, dejar pasar y loguear
            return response()->json([
                'status'             => 'success',
                'es_apropiado'       => true,
                'palabras_detectadas' => [],
                'motivo'             => 'No se pudo validar automáticamente.',
                'fuente'             => 'error_fallback',
            ]);
        }
    }
}
