<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KahootController extends Controller
{
    /**
     * Receive a base64-encoded PDF, send it to Gemini and return structured Kahoot questions.
     */
    public function generateQuestions(Request $request)
    {
        $request->validate([
            'pdf_base64' => 'required|string',
            'num_questions' => 'sometimes|integer|min:1|max:30',
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json([
                'error' => 'Falta configurar GEMINI_API_KEY en el entorno.',
            ], 500);
        }

        $pdfBase64 = $request->input('pdf_base64');
        $numQuestions = $request->input('num_questions', 10);
        $models = $this->getConfiguredModels();
        $apiVersions = $this->getConfiguredApiVersions();

        $prompt = "Eres un asistente educativo. Lee el siguiente documento PDF y genera exactamente {$numQuestions} preguntas de tipo test sobre su contenido, en el mismo idioma que el documento. "
            . "Devuelve SOLO un JSON valido con este formato exacto (sin markdown, sin explicaciones extra):\\n"
            . "[\\n"
            . "  {\\n"
            . "    \\\"question\\\": \\\"Texto de la pregunta\\\",\\n"
            . "    \\\"answers\\\": [\\\"Opcion A\\\", \\\"Opcion B\\\", \\\"Opcion C\\\", \\\"Opcion D\\\"],\\n"
            . "    \\\"correct\\\": 0\\n"
            . "  }\\n"
            . "]\\n"
            . "El campo 'correct' es el indice (0-3) de la respuesta correcta dentro del array 'answers'. "
            . "Asegurate de que cada pregunta tenga exactamente 4 respuestas y solo una correcta.";

        $payload = [
            'contents' => [[
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => 'application/pdf',
                            'data' => $pdfBase64,
                        ],
                    ],
                    [
                        'text' => $prompt,
                    ],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
            ],
        ];

        $attempts = [];
        $lastStatusCode = 502;
        $lastErrorBody = 'Sin detalles';

        try {
            $client = new GuzzleClient([
                'timeout' => 120,
                'http_errors' => false,
            ]);

            foreach ($apiVersions as $apiVersion) {
                foreach ($models as $model) {
                    $url = "https://generativelanguage.googleapis.com/{$apiVersion}/models/{$model}:generateContent?key={$apiKey}";
                    $attempts[] = "{$apiVersion}/{$model}";

                    $guzzleResponse = $client->post($url, [
                        'headers' => ['Content-Type' => 'application/json'],
                        'json' => $payload,
                    ]);

                    $statusCode = $guzzleResponse->getStatusCode();
                    $rawBody = $guzzleResponse->getBody()->getContents();
                    $body = json_decode($rawBody, true);

                    if ($statusCode !== 200) {
                        $lastStatusCode = $statusCode;
                        $lastErrorBody = $body ?? $rawBody ?? 'Sin detalles';

                        Log::warning("Gemini API Error (HTTP {$statusCode}) en {$apiVersion}/{$model}: " . ($rawBody ?: 'Empty Body'));
                        continue;
                    }

                    $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $text = preg_replace('/^```(?:json)?\\s*/i', '', trim($text));
                    $text = preg_replace('/\\s*```$/', '', $text);

                    $questions = json_decode(trim($text), true);

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
                        return response()->json([
                            'error' => 'Gemini no devolvio un JSON valido',
                            'raw' => $text,
                            'model_used' => $model,
                            'api_version_used' => $apiVersion,
                        ], 422);
                    }

                    $sanitized = [];
                    foreach ($questions as $q) {
                        if (!isset($q['question'], $q['answers'], $q['correct'])) {
                            continue;
                        }

                        $answers = array_values(array_slice((array) $q['answers'], 0, 4));
                        while (count($answers) < 4) {
                            $answers[] = '';
                        }

                        $correct = (int) $q['correct'];
                        if ($correct < 0 || $correct > 3) {
                            $correct = 0;
                        }

                        $sanitized[] = [
                            'question' => (string) $q['question'],
                            'answers' => $answers,
                            'correct' => $correct,
                        ];
                    }

                    if (count($sanitized) === 0) {
                        return response()->json([
                            'error' => 'Gemini devolvio preguntas vacias o mal formadas.',
                            'model_used' => $model,
                            'api_version_used' => $apiVersion,
                        ], 422);
                    }

                    return response()->json([
                        'questions' => $sanitized,
                        'model_used' => $model,
                        'api_version_used' => $apiVersion,
                    ]);
                }
            }

            return response()->json([
                'error' => 'Ningun modelo Gemini configurado respondio correctamente.',
                'details' => $lastErrorBody,
                'http_status' => $lastStatusCode,
                'attempts' => $attempts,
            ], 502);
        } catch (\Exception $e) {
            Log::error("Gemini API Exception: " . $e->getMessage() . "\\n" . $e->getTraceAsString());

            return response()->json([
                'error' => 'Excepcion al llamar a Gemini',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function getConfiguredModels(): array
    {
        $raw = (string) config('services.gemini.models', 'gemini-2.5-flash,gemini-2.5-pro,gemini-2.5-flash-lite');
        $models = array_values(array_filter(array_map('trim', explode(',', $raw))));

        return count($models) > 0
            ? $models
            : ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite'];
    }

    private function getConfiguredApiVersions(): array
    {
        $raw = (string) config('services.gemini.api_versions', 'v1,v1beta');
        $versions = array_values(array_filter(array_map('trim', explode(',', $raw))));

        return count($versions) > 0
            ? $versions
            : ['v1', 'v1beta'];
    }
}
