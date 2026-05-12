<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EducationalCenter;
use App\Models\Event;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Cycle;
use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SchoolOrganizationSeeder extends Seeder
{
    public function run()
    {
        echo "🏢 Reconstruyendo Universos Académicos (Lanzarote Determinista)...\n";

        // 1. MATERIAS (Tags)
        echo "🏷️ Creando Materias...\n";
        $materias = [
            'Programación', 'Bases de Datos', 'Despliegue de Aplicaciones', 'Entornos de Desarrollo',
            'Lenguaje de Marcas', 'Diseño de Interfaces Web', 'Sistemas Informáticos', 'FOL',
            'Matemáticas', 'Lengua Castellana', 'Inglés', 'Física', 'Química', 'Historia',
            'Dibujo Técnico', 'Educación Física', 'Artes Plásticas', 'Karate', 'Zen y Meditación'
        ];

        $tagModels = [];
        foreach ($materias as $m) {
            $tagModels[$m] = Tag::updateOrCreate(['name' => $m]);
        }

        // 2. CICLOS Y GRADOS
        echo "📚 Creando Ciclos...\n";
        $fpCycles = [
            'DAW' => Cycle::updateOrCreate(['name' => 'Desarrollo de Aplicaciones Web'], ['level' => 'FP']),
            'DAM' => Cycle::updateOrCreate(['name' => 'Desarrollo de Aplicaciones Multiplataforma'], ['level' => 'FP']),
            'ASIR' => Cycle::updateOrCreate(['name' => 'Administración de Sistemas Informáticos en Red'], ['level' => 'FP']),
        ];

        // Vincular materias a ciclos FP
        $fpCycles['DAW']->tags()->sync([$tagModels['Programación']->id, $tagModels['Diseño de Interfaces Web']->id, $tagModels['Bases de Datos']->id]);
        $fpCycles['DAM']->tags()->sync([$tagModels['Programación']->id, $tagModels['Despliegue de Aplicaciones']->id, $tagModels['Bases de Datos']->id]);

        $esoCycles = [];
        for ($i = 1; $i <= 4; $i++) {
            $c = Cycle::updateOrCreate(['name' => "{$i}º ESO"], ['level' => 'SE']);
            $c->tags()->sync([$tagModels['Matemáticas']->id, $tagModels['Lengua Castellana']->id, $tagModels['Inglés']->id]);
            $esoCycles[] = $c;
        }

        $bachCycles = [];
        for ($i = 1; $i <= 2; $i++) {
            $c = Cycle::updateOrCreate(['name' => "{$i}º Bachillerato"], ['level' => 'SE']);
            $c->tags()->sync([$tagModels['Historia']->id, $tagModels['Física']->id, $tagModels['Inglés']->id]);
            $bachCycles[] = $c;
        }

        $primaryCycles = [];
        for ($i = 1; $i <= 6; $i++) {
            $c = Cycle::updateOrCreate(['name' => "{$i}º Primaria"], ['level' => 'PE']);
            $c->tags()->sync([$tagModels['Matemáticas']->id, $tagModels['Artes Plásticas']->id]);
            $primaryCycles[] = $c;
        }

        $uniCycles = [
            Cycle::updateOrCreate(['name' => 'Grado en Enfermería'], ['level' => 'UR']),
            Cycle::updateOrCreate(['name' => 'Grado en Turismo'], ['level' => 'UR']),
        ];

        // 3. CENTROS EDUCATIVOS
        echo "🏫 Configurando Centros...\n";
        $centersData = [
            ['name' => 'CIFP Zonzamas', 'type' => 'FP', 'category' => 'CIFP', 'location' => 'Arrecife', 'cycles' => array_values($fpCycles)],
            ['name' => 'IES Agustín Espinosa', 'type' => 'SE', 'category' => 'IES', 'location' => 'Arrecife', 'cycles' => array_merge($esoCycles, $bachCycles)],
            ['name' => 'IES Salinas', 'type' => 'SE', 'category' => 'IES', 'location' => 'Arrecife', 'cycles' => array_merge($esoCycles, $bachCycles)],
            ['name' => 'IES Altavista', 'type' => 'SE', 'category' => 'IES', 'location' => 'Arrecife', 'cycles' => array_merge($esoCycles, $bachCycles)],
            ['name' => 'CEIP Adolfo Topham', 'type' => 'PE', 'category' => 'CEIP', 'location' => 'Arrecife', 'cycles' => $primaryCycles],
            ['name' => 'ULPGC Lanzarote', 'type' => 'UR', 'category' => 'Universidad', 'location' => 'Tahíche', 'cycles' => $uniCycles],
            ['name' => 'TelamoNet', 'type' => 'TM', 'category' => 'CIFP', 'location' => 'Lanzarote', 'cycles' => array_merge($esoCycles, $bachCycles, array_values($fpCycles))],
        ];

        $centersMap = [];
        foreach ($centersData as $data) {
            $admin = User::updateOrCreate(['email' => "admin." . strtolower(str_replace(' ', '', $data['name'])) . "@telamonet.es"], [
                'name' => 'Admin', 'last_name' => $data['name'], 'password' => Hash::make('12345678'),
                'role' => 'EI', 'dni' => strtoupper(substr(md5($data['name']), 0, 8)) . 'X', 
                'institution_name' => $data['name'], 'education_level' => 'Centro Educativo'
            ]);

            $center = EducationalCenter::updateOrCreate(['name' => $data['name']], [
                'location' => $data['location'], 'type' => $data['type'], 'category' => $data['category'], 'admin_user_id' => $admin->id
            ]);
            
            $admin->update(['educational_center_id' => $center->id]);
            $center->cycles()->sync(collect($data['cycles'])->pluck('id'));

            $this->seedDemoData($center, $data['cycles']);
            $centersMap[strtolower(str_replace(' ', '', $data['name']))] = $center;
        }

        $this->seedQuestionsAndEvents($centersMap);
        $this->seedMeetings($centersMap);
    }

    private function removeAccents($string)
    {
        return strtr(utf8_decode($string), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    private function seedDemoData($center, $cycles)
    {
        if (empty($cycles)) return;
        
        $nombres = ['Carlos', 'Lucía', 'Mateo', 'Valentina', 'Alejandro', 'Sofía', 'Hugo', 'Martina', 'Daniel', 'Julia', 'Pablo', 'Emma', 'Diego', 'Valeria', 'Alba', 'Mario', 'Elena', 'Adrian', 'Paula', 'Marcos'];
        $apellidos = ['García', 'Fernández', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Ramírez', 'Torres', 'Díaz', 'Muñoz', 'Romero', 'Alonso', 'Navarro', 'Ruiz'];

        // Profesor del centro
        $profName = $nombres[$center->id % count($nombres)];
        $profSurname = $apellidos[$center->id % count($apellidos)];
        $teacherEmail = strtolower($this->removeAccents($profName)) . "." . strtolower($this->removeAccents($profSurname)) . ".profe@" . strtolower(str_replace(' ', '', $center->name)) . ".es";
        
        $teacher = User::updateOrCreate(['email' => $teacherEmail], [
            'name' => $profName, 'last_name' => $profSurname, 'password' => Hash::make('12345678'),
            'role' => 'Teacher', 'educational_center_id' => $center->id, 
            'dni' => sprintf('%08d', ($center->id * 1000) + 900) . 'T',
            'institution_name' => $center->name, 'education_level' => $center->type
        ]);

        // Un par de grupos por centro
        for ($g = 0; $g < 2; $g++) {
            $cycle = $cycles[$g % count($cycles)];
            $letter = $g == 0 ? 'A' : 'B';
            $group = Group::updateOrCreate(['name' => "{$cycle->name} - {$letter}", 'educational_center_id' => $center->id], [
                'cycle_id' => $cycle->id, 'tutor_id' => $teacher->id
            ]);

            // 3 Alumnos por grupo
            for ($s = 0; $s < 3; $s++) {
                $seed = ($center->id * 100) + ($g * 10) + $s;
                $stuName = $nombres[$seed % count($nombres)];
                $stuSurname = $apellidos[$seed % count($apellidos)];
                
                // Forzar Mateo y Valentina en Zonzamas DAW A
                if ($center->name === 'CIFP Zonzamas' && str_contains(strtolower($cycle->name), 'daw') && $letter === 'A') {
                    if ($s === 0) { $stuName = 'Mateo'; $stuSurname = 'García'; }
                    if ($s === 1) { $stuName = 'Valentina'; $stuSurname = 'López'; }
                }

                $email = strtolower($this->removeAccents($stuName)) . "." . strtolower($this->removeAccents($stuSurname)) . "." . $seed . "@" . strtolower(str_replace(' ', '', $center->name)) . ".es";
                $user = User::updateOrCreate(['email' => $email], [
                    'name' => $stuName, 'last_name' => $stuSurname, 'password' => Hash::make('12345678'),
                    'role' => 'Student', 'educational_center_id' => $center->id, 
                    'dni' => sprintf('%08d', 10000000 + $seed) . 'S',
                    'institution_name' => $center->name, 'education_level' => $center->type
                ]);
                $group->students()->syncWithoutDetaching([$user->id]);
            }
        }
    }

    private function seedQuestionsAndEvents($centers)
    {
        $zonzamas = $centers['cifpzonzamas'] ?? null;
        if (!$zonzamas) return;

        $mateo = User::where('email', 'like', 'mateo.garcia%@cifpzonzamas.es')->first();
        $valentina = User::where('email', 'like', 'valentina.lopez%@cifpzonzamas.es')->first();
        $profe = User::where('role', 'Teacher')->where('educational_center_id', $zonzamas->id)->first();

        if ($mateo) {
            $q1 = Question::updateOrCreate(['title' => '¿Cómo se alinea verticalmente un div sin Flexbox?'], [
                'content' => 'Me estoy volviendo loco en clase de Diseño de Interfaces Web. ¿Alguien se acuerda?',
                'user_id' => $mateo->id,
            ]);
            $tag = Tag::where('name', 'Diseño de interfaces web')->first();
            if ($tag) $q1->tags()->sync([$tag->id]);
            
            if ($valentina) {
                \App\Models\Answer::updateOrCreate(['question_id' => $q1->id, 'user_id' => $valentina->id], [
                    'content' => '¡Hola Mateo! Prueba con position absolute y transform translate.'
                ]);
            }
        }

        Event::updateOrCreate(['title' => 'Jornada de Puertas Abiertas', 'educational_center_id' => $zonzamas->id], [
            'description' => 'Ven a conocer nuestras instalaciones.',
            'location' => 'Salón de Actos',
            'date' => now()->addDays(15), 'start_time' => '09:00:00', 'end_time' => '14:00:00'
        ]);
    }

    private function seedMeetings($centers)
    {
        foreach ($centers as $center) {
            $teacher = User::where('role', 'Teacher')->where('educational_center_id', $center->id)->first();
            if ($teacher) {
                \App\Models\Meeting::updateOrCreate(['name' => 'Dudas Generales - ' . $center->name], [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name . " " . $teacher->last_name,
                    'educational_center_id' => $center->id,
                    'schedule' => now()->format('Y-m-d H:i:s'),
                    'description' => "Sesión de apoyo para alumnos de " . $center->name
                ]);
            }
        }
    }
}
