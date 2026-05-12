<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                'Content-Type'  => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => 'llama-3.3-70b-versatile',
                'temperature' => 0.1,
                'max_tokens'  => 300,
                'messages'    => [
                    [
                        'role'    => 'user',
                        'content' => <<<PROMPT
Analiza si este texto es apropiado para publicarse en un foro educativo entre estudiantes y profesores.

Rechaza el texto si contiene:
- Palabras malsonantes, groserías o insultos
- Adjetivos físicos sobre personas
- Contenido ofensivo, acoso o discriminación
- Contenido completamente ajeno al ámbito educativo o académico

El texto es: "{$content}"

Responde SOLO con un JSON válido sin markdown:
{
  "es_apropiado": true/false,
  "palabras_detectadas": ["lista de palabras problemáticas si las hay"]
}
PROMPT
                    ]
                ],
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Error al contactar con el servicio de validación: ' . $response->status(),
                ], 502);
            }

            $body        = $response->json();
            $rawText     = $body['choices'][0]['message']['content'] ?? '';

            //Limpiar posibles bloques markdown que el modelo añada
            $cleanJson = preg_replace('/```json|```/', '', $rawText);
            $result    = json_decode(trim($cleanJson), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Respuesta inesperada del servicio de validación',
                ], 502);
            }

            return response()->json([
                'status'             => 'success',
                'es_apropiado'       => (bool) ($result['es_apropiado'] ?? true),
                'motivo'             => $result['motivo'] ?? '',
                'palabras_detectadas'=> $result['palabras_detectadas'] ?? [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error interno al validar el contenido: ' . $e->getMessage(),
            ], 500);
        }
    }
}