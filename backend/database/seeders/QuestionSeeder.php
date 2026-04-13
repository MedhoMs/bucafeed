<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        if ($users->isEmpty()) {
            echo "No hay usuarios para asociar las preguntas. Abortando.\n";
            return;
        }

        // 1. Crear Etiquetas (Tags)
        $tagsData = [
            'Matemáticas', 'Física', 'Programación', 'Historia', 
            'Biología', 'Lengua y Literatura', 'Inglés', 'Química'
        ];

        $tags = [];
        foreach ($tagsData as $tagName) {
            $tags[] = \App\Models\Tag::updateOrCreate(['name' => $tagName]);
        }

        // 2. Crear Preguntas
        $questionsData = [
            [
                'title' => '¿Cómo se resuelve una ecuación de segundo grado?',
                'content' => 'Hola a todos, estoy teniendo problemas con la fórmula general (la de -b +- ...). ¿Alguien podría explicarme paso a paso con un ejemplo?',
                'tag_indices' => [0] // Matemáticas
            ],
            [
                'title' => 'Duda sobre la fotosíntesis',
                'content' => 'No me queda clara la diferencia entre la fase luminosa y la fase oscura. ¿En cuál se libera el oxígeno exactamente?',
                'tag_indices' => [4] // Biología
            ],
            [
                'title' => '¿Qué es un Array en JavaScript?',
                'content' => 'Estoy empezando con programación y no entiendo muy bien para qué sirven los arreglos o arrays. ¿Son como una lista de variables?',
                'tag_indices' => [2] // Programación
            ],
            [
                'title' => 'Causas de la Revolución Francesa',
                'content' => 'Tengo un examen mañana y necesito un resumen rápido de las causas económicas y sociales de la Revolución Francesa. ¡Gracias!',
                'tag_indices' => [3] // Historia
            ],
            [
                'title' => 'Past Simple vs Present Perfect',
                'content' => 'I always confuse these two tenses. When should I use "I went" and when "I have gone"? Any trick?',
                'tag_indices' => [6] // Inglés
            ]
        ];

        foreach ($questionsData as $qData) {
            $author = $users->random();
            $question = \App\Models\Question::create([
                'user_id' => $author->id,
                'title' => $qData['title'],
                'content' => $qData['content']
            ]);

            // Asociar etiquetas
            foreach ($qData['tag_indices'] as $idx) {
                $question->tags()->attach($tags[$idx]->id);
            }

            // 3. Crear Respuestas ficticias
            $numAnswers = rand(2, 4);
            for ($i = 0; $i < $numAnswers; $i++) {
                $respondent = $users->where('id', '!=', $author->id)->random();
                $isUseful = ($i == 0); // La primera como "útil" para el ejemplo

                $answer = \App\Models\Answer::create([
                    'question_id' => $question->id,
                    'user_id' => $respondent->id,
                    'content' => "Respuesta de ejemplo #" . ($i + 1) . " para la pregunta: " . $qData['title'] . ". " .
                                 "Aquí va una explicación académica detallada sobre el tema solicitado para ayudar al compañero.",
                    'is_useful' => $isUseful,
                    'reputation' => $isUseful ? 10 : rand(0, 5)
                ]);

                if ($isUseful) {
                    $respondent->increment('reputation', 10);
                }
            }
            
            $question->update(['answer_count' => $numAnswers]);
        }
    }
}
