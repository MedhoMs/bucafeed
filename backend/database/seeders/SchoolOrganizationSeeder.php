<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Tag;
use App\Models\Group;
use App\Models\Question;
use App\Models\Student;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;

class SchoolOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        echo "🏢 Reconstruyendo Mundo Académico Lanzarote (Completo)...\n";

        // 1. Materias y Ciclos
        $tagsData = [
            'DAW' => ['Programación', 'Bases de Datos', 'Despliegue Web', 'Lenguajes de Marcas', 'Entornos de Desarrollo'],
            'ESO' => ['Matemáticas', 'Geografía e Historia', 'Física y Química', 'Biología y Geología', 'Lengua Castellana', 'Inglés'],
            'PRI' => ['Lengua y Literatura', 'Ciencias Naturales', 'Plástica', 'Educación Física']
        ];

        foreach (array_merge(...array_values($tagsData)) as $tagName) {
            Tag::firstOrCreate(['name' => $tagName]);
        }

        $cycles = [
            'DAW' => Cycle::updateOrCreate(['name' => 'DAW'], ['area' => 'Informática', 'level' => 'FP Superior']),
            'ESO' => Cycle::updateOrCreate(['name' => 'ESO'], ['area' => 'General', 'level' => 'Secundaria']),
            'PRI' => Cycle::updateOrCreate(['name' => 'Primaria'], ['area' => 'General', 'level' => 'Primaria'])
        ];

        foreach ($cycles as $key => $cycle) {
            $cycle->tags()->sync(Tag::whereIn('name', $tagsData[$key])->pluck('id'));
        }

        // 2. Centros con Administradores
        $centersData = [
            ['name' => 'CIFP Zonzamas', 'location' => 'Arrecife', 'type' => 'HE'],
            ['name' => 'IES Agustín Espinosa', 'location' => 'Arrecife', 'type' => 'SE'],
            ['name' => 'CEIP Adolfo Topham', 'location' => 'Arrecife', 'type' => 'PE'],
            ['name' => 'IES en Altavista', 'location' => 'Arrecife', 'type' => 'SE'],
        ];

        $centers = [];
        foreach ($centersData as $index => $data) {
            $adminEmail = "admin." . strtolower(str_replace(' ', '', $data['name'])) . "@telamonet.es";
            $adminCenter = User::updateOrCreate(['email' => $adminEmail], [
                'name' => 'Admin',
                'last_name' => $data['name'],
                'password' => Hash::make('12345678'),
                'role' => 'EI',
                'dni' => '0000000' . $index . 'A',
                'institution_name' => $data['name'],
                'education_level' => 'Centro Educativo'
            ]);

            $center = EducationalCenter::updateOrCreate(['name' => $data['name']], [
                'location' => $data['location'],
                'type' => $data['type'],
                'admin_user_id' => $adminCenter->id
            ]);
            
            $adminCenter->update(['educational_center_id' => $center->id]);
            $centers[strtolower(str_replace(' ', '', $data['name']))] = $center;
        }

        // 3. Usuarios y Grupos
        $this->seedGroupsAndUsers($centers, $cycles);

        // 4. Preguntas y Eventos
        $this->seedQuestionsAndEvents($centers);

        echo "✅ Mundo académico reconstruido con éxito.\n";
    }

    private function seedGroupsAndUsers($centers, $cycles)
    {
        $zonzamas = $centers['cifpzonzamas'];
        $profeDaw = User::updateOrCreate(['email' => 'profe.daw@zonzamas.es'], [
            'name' => 'Carlos', 'last_name' => 'Profesor DAW', 'password' => Hash::make('12345678'),
            'role' => 'Teacher', 'educational_center_id' => $zonzamas->id, 'dni' => '99999991T', 
            'institution_name' => 'CIFP Zonzamas', 'education_level' => 'FP'
        ]);

        $groupDaw = Group::updateOrCreate(['name' => '2º DAW', 'educational_center_id' => $zonzamas->id], [
            'cycle_id' => $cycles['DAW']->id,
            'tutor_id' => $profeDaw->id
        ]);

        foreach (['Mateo Hernández', 'Valentina Ruiz'] as $index => $name) {
            $parts = explode(' ', $name);
            $user = User::updateOrCreate(['email' => strtolower($parts[0]) . "@zonzamas.es"], [
                'name' => $parts[0], 'last_name' => $parts[1], 'password' => Hash::make('12345678'),
                'role' => 'Student', 'educational_center_id' => $zonzamas->id, 'dni' => '8888888'.$index.'S', 
                'institution_name' => 'CIFP Zonzamas', 'education_level' => 'FP'
            ]);
            Student::updateOrCreate(['user_id' => $user->id], [
                'educational_center_id' => $zonzamas->id, 'cycle_id' => $cycles['DAW']->id, 'course' => '2'
            ]);
            $groupDaw->students()->syncWithoutDetaching([$user->id]);
        }
    }

    private function seedQuestionsAndEvents($centers)
    {
        $mateo = User::where('email', 'mateo@zonzamas.es')->first();
        $valentina = User::where('email', 'valentina@zonzamas.es')->first();

        // Preguntas
        if ($mateo) {
            $q1 = Question::updateOrCreate(['title' => '¿Cómo funciona el middleware en Laravel?'], [
                'content' => 'Estamos viendo en clase cómo filtrar peticiones. ¿Cuál es la forma correcta de registrarlo?',
                'user_id' => $mateo->id,
            ]);
            $q1->tags()->sync(Tag::where('name', 'Programación')->pluck('id'));
        }

        if ($valentina) {
            $q2 = Question::updateOrCreate(['title' => 'Duda con JOINS en SQL'], [
                'content' => 'No me queda clara la diferencia entre un LEFT JOIN y un INNER JOIN. ¿Alguien me explica?',
                'user_id' => $valentina->id,
            ]);
            $q2->tags()->sync(Tag::where('name', 'Bases de Datos')->pluck('id'));
        }

        // Eventos
        $zonzamas = $centers['cifpzonzamas'];
        Event::updateOrCreate(['title' => 'Jornada de Puertas Abiertas'], [
            'educational_center_id' => $zonzamas->id,
            'description' => 'Ven a conocer nuestras instalaciones y la oferta formativa técnica para el próximo curso.',
            'location' => 'Salón de Actos - Edificio A',
            'date' => now()->addDays(15),
            'start_time' => '09:00:00',
            'end_time' => '14:00:00',
            'target_role' => 'Student'
        ]);

        Event::updateOrCreate(['title' => 'Taller de Ciberseguridad'], [
            'educational_center_id' => $zonzamas->id,
            'description' => 'Introducción práctica a la seguridad ofensiva y defensa en redes locales.',
            'location' => 'Aula de Informática 3',
            'date' => now()->addDays(20),
            'start_time' => '16:00:00',
            'end_time' => '19:00:00',
            'target_role' => 'Student'
        ]);

        $topham = $centers['ceipadolfotopham'];
        Event::updateOrCreate(['title' => 'Fiesta de Fin de Curso'], [
            'educational_center_id' => $topham->id,
            'description' => 'Celebración con música y juegos para todos los alumnos de primaria.',
            'location' => 'Patio Principal',
            'date' => now()->addDays(30),
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'target_role' => null
        ]);
    }
}
