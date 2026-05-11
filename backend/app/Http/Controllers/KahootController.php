<?php

namespace App\Http\Controllers;

use App\Models\KahootSession;
use App\Models\KahootAnswer;
use App\Models\Meeting;
use App\Models\User;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class KahootController extends Controller
{
    public function generateQuestions(Request $request)
    {
        $request->validate([
            'pdf_base64' => 'required|string',
            'num_questions' => 'sometimes|integer|min:1|max:30',
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            $errMsg = 'Falta configurar GEMINI_API_KEY en el entorno.';
            return response()->json([
                'error' => $errMsg,
                'message' => $errMsg,
            ], 500);
        }

        $pdfBase64 = $request->input('pdf_base64');
        $numQuestions = $request->input('num_questions', 10);

        // Extract text from PDF
        $pdfText = null;
        try {
            $pdfBinary = base64_decode($pdfBase64);
            if ($pdfBinary === false) {
                throw new \Exception('Base64 invalido');
            }
            $tempPath = tempnam(sys_get_temp_dir(), 'kahoot_pdf_') . '.pdf';
            file_put_contents($tempPath, $pdfBinary);
            $pdfParser = new PdfParser();
            $pdfDocument = $pdfParser->parseFile($tempPath);
            $pdfText = $pdfDocument->getText();
            @unlink($tempPath);
        } catch (\Exception $e) {
            if (isset($tempPath) && file_exists($tempPath)) @unlink($tempPath);
            Log::warning("PDF text extraction failed: " . $e->getMessage());
            $pdfText = null;
        }

        $models = ['gemini-2.5-flash', 'gemini-2.5-pro'];
        $apiVersions = ['v1'];

        $prompt = "Eres un asistente educativo. Lee el siguiente contenido y genera exactamente {$numQuestions} preguntas de tipo test sobre el mismo, en el mismo idioma que el contenido. "
            . "Devuelve SOLO un JSON valido con este formato exacto (sin markdown, sin explicaciones extra):\n"
            . "[\n"
            . "  {\n"
            . "    \"question\": \"Texto de la pregunta\",\n"
            . "    \"answers\": [\"Opcion A\", \"Opcion B\", \"Opcion C\", \"Opcion D\"],\n"
            . "    \"correct\": 0\n"
            . "  }\n"
            . "]\n"
            . "El campo 'correct' es el indice (0-3) de la respuesta correcta dentro del array 'answers'. "
            . "Asegurate de que cada pregunta tenga exactamente 4 respuestas y solo una correcta.";

        // Build payload: prefer extracted text, fall back to PDF inline_data
        if ($pdfText !== null && trim($pdfText) !== '') {
            $textContent = "Contenido del documento:\n\n" . substr($pdfText, 0, 100000) . "\n\n" . $prompt;
            $payload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $textContent],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 8192,
                ],
            ];
        } else {
            $errMsg = 'No se pudo extraer texto del PDF. El archivo puede ser un documento escaneado (imagen) sin texto seleccionable. Usa un PDF con texto real o copia el contenido manualmente.';
            Log::warning("Kahoot: PDF sin texto extraible");
            return response()->json([
                'error' => $errMsg,
                'message' => $errMsg,
            ], 422);
        }

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
                    $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
                    $text = preg_replace('/\s*```$/', '', $text);

                    $questions = json_decode(trim($text), true);

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
                        $errMsg = 'Gemini no devolvio un JSON valido';
                        return response()->json([
                            'error' => $errMsg,
                            'message' => $errMsg,
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
                        $errMsg = 'Gemini devolvio preguntas vacias o mal formadas.';
                        return response()->json([
                            'error' => $errMsg,
                            'message' => $errMsg,
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

            $errorMsg = 'Ningun modelo Gemini configurado respondio correctamente.';
            if (is_array($lastErrorBody) && isset($lastErrorBody['error']['message'])) {
                $errorMsg .= ' Ultimo error: ' . $lastErrorBody['error']['message'];
            } elseif (is_string($lastErrorBody)) {
                $errorMsg .= ' Detalle: ' . substr($lastErrorBody, 0, 200);
            }
            return response()->json([
                'error' => $errorMsg,
                'message' => $errorMsg,
                'details' => $lastErrorBody,
                'http_status' => $lastStatusCode,
                'attempts' => $attempts,
            ], 502);
        } catch (\Exception $e) {
            Log::error("Gemini API Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $errMsg = 'Excepcion al llamar a Gemini: ' . $e->getMessage();

            return response()->json([
                'error' => $errMsg,
                'message' => $errMsg,
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function createSession(Request $request, Meeting $meeting)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'nullable|string',
            'questions.*.answers' => 'required|array|size:4',
            'questions.*.answers.*' => 'nullable|string',
            'questions.*.correct' => 'required|integer|between:0,3',
            'time_per_question' => 'sometimes|integer|min:5|max:120',
        ]);

        $session = KahootSession::create([
            'meeting_id' => $meeting->id,
            'teacher_id' => $request->user()->id,
            'title' => $request->input('title', 'Kahoot'),
            'questions' => $request->input('questions'),
            'status' => 'pending',
            'current_question_index' => 0,
            'time_per_question' => $request->input('time_per_question', 30),
        ]);

        return response()->json($session, 201);
    }

    public function getActiveSession(Meeting $meeting)
    {
        $session = KahootSession::where('meeting_id', $meeting->id)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (!$session) {
            return response()->json(['session' => null]);
        }

        $session->load('teacher');
        return response()->json(['session' => $session]);
    }

    public function startSession(Request $request, KahootSession $session)
    {
        if ($session->teacher_id !== $request->user()->id) {
            return response()->json(['error' => 'Solo el profesor puede iniciar el kahoot.'], 403);
        }

        $session->update([
            'status' => 'active',
            'current_question_index' => 0,
        ]);

        $questions = $session->questions;
        $current = $questions[0] ?? null;

        if (!$current) {
            return response()->json(['error' => 'No hay preguntas en el kahoot.'], 400);
        }

        return response()->json([
            'session' => $session,
            'question' => [
                'index' => 0,
                'question' => $current['question'],
                'answers' => $current['answers'],
            ],
            'time_per_question' => $session->time_per_question,
            'total_questions' => count($questions),
        ]);
    }

    public function getCurrentQuestion(Request $request, KahootSession $session)
    {
        if ($session->status === 'pending') {
            return response()->json(['error' => 'El kahoot no ha comenzado.'], 400);
        }

        $questions = $session->questions;
        $index = $session->current_question_index;

        if ($session->status === 'finished' || $index >= count($questions)) {
            return response()->json([
                'finished' => true,
                'total_questions' => count($questions),
            ]);
        }

        $current = $questions[$index];

        // Check if user already answered this question
        $userAnswer = null;
        if ($request->user()) {
            $existingAnswer = KahootAnswer::where('session_id', $session->id)
                ->where('user_id', $request->user()->id)
                ->where('question_index', $index)
                ->first();
            if ($existingAnswer) {
                $allUserScores = KahootAnswer::where('session_id', $session->id)
                    ->where('user_id', $request->user()->id)
                    ->sum('score');
                $userAnswer = [
                    'selected_answer' => $existingAnswer->selected_answer,
                    'is_correct' => $existingAnswer->is_correct,
                    'score' => $existingAnswer->score,
                    'total_score' => (int) $allUserScores,
                ];
            }
        }

        return response()->json([
            'session_status' => $session->status,
            'question' => [
                'index' => $index,
                'question' => $current['question'],
                'answers' => $current['answers'],
                'correct' => $current['correct'],
            ],
            'user_answer' => $userAnswer,
            'time_per_question' => $session->time_per_question,
            'total_questions' => count($questions),
        ]);
    }

    public function submitAnswer(Request $request, KahootSession $session)
    {
        $request->validate([
            'question_index' => 'required|integer|min:0',
            'selected_answer' => 'required|integer|between:0,3',
            'time_remaining' => 'sometimes|numeric|min:0',
        ]);

        if ($session->status !== 'active') {
            return response()->json(['error' => 'El kahoot no esta activo.'], 400);
        }

        $questions = $session->questions;
        $index = $request->input('question_index');

        if (!isset($questions[$index])) {
            return response()->json(['error' => 'Indice de pregunta invalido.'], 400);
        }

        $question = $questions[$index];
        $selected = $request->input('selected_answer');
        $isCorrect = ($selected === $question['correct']);

        $score = 0;
        if ($isCorrect) {
            $timeRemaining = $request->input('time_remaining', $session->time_per_question);
            $score = max(100, (int)(1000 * ($timeRemaining / $session->time_per_question)));
        }

        $answer = KahootAnswer::updateOrCreate(
            [
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
                'question_index' => $index,
            ],
            [
                'selected_answer' => $selected,
                'is_correct' => $isCorrect,
                'score' => $score,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_answer' => $question['correct'],
            'score' => $score,
            'total_score' => KahootAnswer::where('session_id', $session->id)
                ->where('user_id', $request->user()->id)
                ->sum('score'),
        ]);
    }

    public function nextQuestion(Request $request, KahootSession $session)
    {
        if ($session->teacher_id !== $request->user()->id) {
            return response()->json(['error' => 'Solo el profesor puede avanzar.'], 403);
        }

        $questions = $session->questions;
        $nextIndex = $session->current_question_index + 1;

        if ($nextIndex >= count($questions)) {
            $session->update([
                'status' => 'finished',
                'current_question_index' => $nextIndex,
            ]);

            return response()->json([
                'finished' => true,
                'total_questions' => count($questions),
            ]);
        }

        $session->update([
            'current_question_index' => $nextIndex,
        ]);

        $current = $questions[$nextIndex];

        return response()->json([
            'finished' => false,
            'session' => $session,
            'question' => [
                'index' => $nextIndex,
                'question' => $current['question'],
                'answers' => $current['answers'],
            ],
            'time_per_question' => $session->time_per_question,
            'total_questions' => count($questions),
        ]);
    }

    public function getLeaderboard(Request $request, KahootSession $session)
    {
        $scores = KahootAnswer::where('session_id', $session->id)
            ->selectRaw('user_id, SUM(score) as total_score, COUNT(*) as answered, SUM(is_correct) as correct_count')
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->get();

        $users = User::whereIn('id', $scores->pluck('user_id'))->get()->keyBy('id');

        $leaderboard = $scores->map(function ($score) use ($users) {
            $user = $users->get($score->user_id);
            return [
                'user_id' => $score->user_id,
                'username' => $user ? "{$user->name} {$user->last_name}" : 'Desconocido',
                'total_score' => (int) $score->total_score,
                'correct_count' => (int) $score->correct_count,
                'answered' => (int) $score->answered,
            ];
        });

        return response()->json([
            'leaderboard' => $leaderboard,
            'total_questions' => count($session->questions),
        ]);
    }

    public function getResults(Request $request, KahootSession $session)
    {
        $totalQuestions = count($session->questions);

        $scores = KahootAnswer::where('session_id', $session->id)
            ->selectRaw('user_id, SUM(score) as total_score, COUNT(*) as answered, SUM(is_correct) as correct_count')
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->get();

        $users = User::whereIn('id', $scores->pluck('user_id'))->get()->keyBy('id');

        $leaderboard = $scores->map(function ($score) use ($users) {
            $user = $users->get($score->user_id);
            return [
                'user_id' => $score->user_id,
                'username' => $user ? "{$user->name} {$user->last_name}" : 'Desconocido',
                'total_score' => (int) $score->total_score,
                'correct_count' => (int) $score->correct_count,
                'answered' => (int) $score->answered,
            ];
        });

        $questions = collect($session->questions)->map(function ($q, $i) use ($session) {
            $answersForQuestion = KahootAnswer::where('session_id', $session->id)
                ->where('question_index', $i)
                ->get();

            $answerDistribution = [0, 0, 0, 0];
            $correctCount = 0;
            foreach ($answersForQuestion as $a) {
                $answerDistribution[$a->selected_answer]++;
                if ($a->is_correct) $correctCount++;
            }

            return [
                'question' => $q['question'],
                'correct' => $q['correct'],
                'answers' => $q['answers'],
                'answer_distribution' => $answerDistribution,
                'total_answers' => $answersForQuestion->count(),
                'correct_count' => $correctCount,
            ];
        });

        return response()->json([
            'session' => $session,
            'leaderboard' => $leaderboard,
            'questions' => $questions,
            'total_questions' => $totalQuestions,
        ]);
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
