<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\EducationalCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersAndGroupsSeeder extends Seeder
{
    private $nombres = [
        'Carlos', 'Lucía', 'Mateo', 'Valentina', 'Alejandro', 'Sofía', 'Hugo', 'Martina', 'Daniel', 'Julia',
        'Pablo', 'Emma', 'Diego', 'Valeria', 'Alba', 'Mario', 'Elena', 'Adrián', 'Paula', 'Marcos',
        'Irene', 'Raúl', 'Lara', 'Sergio', 'Claudia', 'Jorge', 'Natalia', 'Álvaro', 'Celia', 'Ismael',
        'Ainoa', 'Rubén', 'Cristina', 'Oriol', 'Marina', 'Guille', 'Laia', 'Nacho', 'Rocío', 'Edu',
        'Ángela', 'Unai', 'Marta', 'Víctor', 'Silvia', 'Iván', 'Gema', 'Javier', 'Nerea', 'Álex',
        'Mohamed', 'Aisha', 'Yuto', 'Sakura', 'John', 'Emily', 'Liam', 'Olivia', 'Noah', 'Emma',
        'Wei', 'Lin', 'Raj', 'Priya', 'Omar', 'Fatima', 'Hassan', 'Layla', 'Ibrahim', 'Zahra',
        'Lukas', 'Elena', 'Niklas', 'Hanna', 'Pedro', 'Ana', 'João', 'María', 'Thiago', 'Isabela',
        'Santiago', 'Florencia', 'Matías', 'Camila', 'Benjamín', 'Antonella',
        'David', 'Sara', 'Miguel', 'Rosa', 'Jose', 'Carmen', 'Antonio', 'Dolores', 'Francisco', 'Pilar',
        'Jesús', 'Teresa', 'Manuel', 'Lourdes', 'Ángel', 'Rafael', 'Fernando', 'Mercedes',
        'Enrique', 'Victoria', 'Alberto', 'Eva', 'Ramón', 'Juan', 'Mar',
    ];
    private $apellidos = [
        'García', 'Fernández', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Ramírez', 'Torres',
        'Díaz', 'Muñoz', 'Romero', 'Alonso', 'Navarro', 'Ruiz', 'Jiménez', 'Moreno', 'Álvarez', 'Gutiérrez',
        'Castro', 'Ortiz', 'Rubio', 'Molina', 'Delgado', 'Gil', 'Serrano', 'Blanco', 'Cortés', 'Suárez',
        'Mendoza', 'Herrera', 'Medina', 'Garrido', 'Vargas', 'Flores', 'Peña', 'Cabrera', 'Campos', 'Santos',
        'Iglesias', 'Cruz', 'Reyes', 'Vega', 'Aguilar', 'Carrasco', 'Benítez', 'Moya', 'Rivas', 'Pascual',
        'Marín', 'Lorenzo', 'Soto', 'Hidalgo', 'Montero', 'Cano', 'León', 'Márquez', 'Franco', 'Espinosa',
        'Kim', 'Park', 'Chen', 'Wang', 'Patel', 'Sharma', 'Ali', 'Khan', 'Johansson', 'Andersen',
        'Silva', 'Santos', 'Oliveira', 'Lima', 'Costa', 'Pereira', 'Kimura', 'Tanaka', 'Watanabe', 'Nakamura',
        'Dubois', 'Lefebvre', 'Schmidt', 'Weber', 'Fischer', 'Wagner', 'Becker', 'Hoffmann',
    ];

    private const MAX_STUDENTS = 320;

    public function run(): void
    {
        echo "👥 Creando Profesores, Grupos y Alumnos (máx " . self::MAX_STUDENTS . " alumnos)...\n";

        $centers = EducationalCenter::all();
        if ($centers->isEmpty()) { echo "No hay centros.\n"; return; }

        $globalCounter = 0;
        $totalStudents = 0;
        $existingEmails = User::pluck('email')->toArray();

        foreach ($centers as $center) {
            if ($totalStudents >= self::MAX_STUDENTS) break;

            $cycles = $center->cycles;
            if ($cycles->isEmpty()) continue;

            // Limit cycles per center type to control total students
            if ($center->type === 'PE') {
                $cycles = $cycles->take(2);     // just 2 primary grades
            } elseif ($center->type === 'SE' && $center->category === 'CEO') {
                $cycles = $cycles->take(3);     // 3 secondary cycles for CEO
            } elseif ($center->type === 'SE' && $center->category === 'Privado') {
                $cycles = $cycles->take(2);     // 2 cycles for private schools
            } elseif ($center->type === 'SE' && $center->category === 'Concertado') {
                $cycles = $cycles->take(2);
            } elseif ($center->type === 'SE') {
                $cycles = $cycles->take(4);     // 4 cycles for regular IES
            } elseif ($center->type === 'FP') {
                $cycles = $cycles->take(10);    // 10 FP cycles
            } elseif ($center->type === 'UR') {
                $cycles = $cycles->take(2);     // 2 university grades
            }

            $teacherIndex = 0;

            foreach ($cycles as $cycle) {
                if ($totalStudents >= self::MAX_STUDENTS) break;

                $teacher = null;
                if ($teacherIndex % 2 === 0) {
                    $teacher = $this->createTeacher($center, $teacherIndex, $globalCounter, $existingEmails);
                }
                $teacherIndex++;
                if (!$teacher) {
                    $teacher = User::where('role', 'Teacher')->where('educational_center_id', $center->id)->first();
                }

                $isFp = $center->type === 'FP';
                $isPe = $center->type === 'PE';
                $isUr = $center->type === 'UR';
                $isPrivate = in_array($center->category, ['Privado', 'Concertado']);

                // Letters: A/B for regular SE, single group for others
                $letters = ($isFp || $isPe || $isUr || $isPrivate) ? [''] : ['A', 'B'];

                foreach ($letters as $letter) {
                    if ($totalStudents >= self::MAX_STUDENTS) break;

                    $groupName = $isFp ? $cycle->name
                                : ($isPe ? $cycle->name
                                : ($isUr ? $cycle->name
                                : "{$cycle->name} {$letter}"));

                    $group = Group::updateOrCreate(
                        ['name' => $groupName, 'educational_center_id' => $center->id],
                        ['cycle_id' => $cycle->id, 'tutor_id' => $teacher?->id]
                    );

                    $studentsInGroup = min(
                        2 + ($globalCounter % 3),        // 2-4 students per group
                        self::MAX_STUDENTS - $totalStudents
                    );

                    for ($s = 0; $s < $studentsInGroup; $s++) {
                        $nameIdx = ($globalCounter * 7 + $s * 3) % count($this->nombres);
                        $surnameIdx = ($globalCounter * 11 + $s * 5) % count($this->apellidos);
                        $stuName = $this->nombres[$nameIdx];
                        $stuSurname = $this->apellidos[$surnameIdx];

                        if ($center->name === 'CIFP Zonzamas' && $cycle->name === '1ºDAW' && $letter === '') {
                            if ($s === 0) { $stuName = 'Mateo'; $stuSurname = 'García'; }
                            if ($s === 1) { $stuName = 'Valentina'; $stuSurname = 'López'; }
                        }

                        $dni = sprintf('%08d', 20000000 + $globalCounter * 10 + $s) . 'S';
                        $email = strtolower($this->removeAccents($stuName)) . "." . strtolower($this->removeAccents($stuSurname)) . "." . $globalCounter . $s . "@" . strtolower(str_replace(' ', '', $center->name)) . ".es";

                        $baseEmail = $email;
                        $emailCounter = 0;
                        while (in_array($email, $existingEmails)) {
                            $emailCounter++;
                            $email = str_replace('.es', "_$emailCounter.es", $baseEmail);
                        }
                        $existingEmails[] = $email;

                        $user = User::updateOrCreate(['email' => $email], [
                            'name' => $stuName, 'last_name' => $stuSurname, 'password' => Hash::make('12345678'),
                            'role' => 'Student', 'educational_center_id' => $center->id,
                            'dni' => $dni,
                            'institution_name' => $center->name, 'education_level' => $center->type
                        ]);

                        Student::updateOrCreate(['user_id' => $user->id], [
                            'educational_center_id' => $center->id,
                            'cycle_id' => $cycle->id,
                            'course' => 1,
                            'verified' => true,
                        ]);

                        $group->students()->syncWithoutDetaching([$user->id]);
                        $totalStudents++;
                    }
                    $globalCounter++;
                }
            }
        }

        echo "✅ {$totalStudents} alumnos creados.\n";
    }

    private function createTeacher($center, $teacherIndex, &$globalCounter, &$existingEmails): ?User
    {
        $nameIdx = ($center->id * 13 + $teacherIndex * 7) % count($this->nombres);
        $surnameIdx = ($center->id * 17 + $teacherIndex * 11) % count($this->apellidos);
        $profName = $this->nombres[$nameIdx];
        $profSurname = $this->apellidos[$surnameIdx];

        $teacherEmail = strtolower($this->removeAccents($profName)) . "." . strtolower($this->removeAccents($profSurname)) . ".profe" . $globalCounter . "@" . strtolower(str_replace(' ', '', $center->name)) . ".es";

        $baseEmail = $teacherEmail;
        $counter = 0;
        while (in_array($teacherEmail, $existingEmails)) {
            $counter++;
            $teacherEmail = str_replace('.es', "_$counter.es", $baseEmail);
        }
        $existingEmails[] = $teacherEmail;

        $dni = sprintf('%08d', 10000000 + $globalCounter * 10) . 'T';
        $globalCounter++;

        $user = User::updateOrCreate(['email' => $teacherEmail], [
            'name' => $profName, 'last_name' => $profSurname, 'password' => Hash::make('12345678'),
            'role' => 'Teacher', 'educational_center_id' => $center->id,
            'dni' => $dni,
            'institution_name' => $center->name, 'education_level' => $center->type
        ]);

        Teacher::updateOrCreate(['user_id' => $user->id], [
            'educational_center_id' => $center->id,
            'specialty'             => $user->education_level ?? '',
            'verified'              => true,
        ]);

        return $user;
    }

    private function removeAccents($string): string
    {
        $search = ['à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý'];
        $replace = ['a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y'];
        return str_replace($search, $replace, $string);
    }
}
