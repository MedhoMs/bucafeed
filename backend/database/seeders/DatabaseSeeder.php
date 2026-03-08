<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EducationalCenter;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Rol;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "🌱 Iniciando seeding de TelamoNet...\n";

        // Seed roles
        $roles_iniciales = [
            'EU' => 'Usuario externo',
            'Student' => 'Estudiante',
            'Teacher' => 'Profesor',
            'Admin' => 'Administrador',
            'EI' => 'Centro educativo'
        ];

        foreach ($roles_iniciales as $code => $name) {
            Rol::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }

        User::factory()->create([
            'name' => 'Antonio',
            'last_name' => 'Morera Marrero',
            'email' => 'antoniomorera784@gmail.com',
            'dni' => '78845622N',
            'password' => Hash::make('mikaela123'),
            'education_level' => 'TM',
            'institution_name' => 'TelamoNet',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Daniel',
            'last_name' => 'Bucaloiu Morales',
            'email' => 'danielbucaloiu@gmail.com',
            'dni' => '12345678Z',
            'password' => Hash::make('0000'),
            'education_level' => 'TM',
            'institution_name' => 'TelamoNet',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Jason',
            'last_name' => 'Camila Sotto',
            'email' => 'jasoncsotto16@gmail.com',
            'dni' => '12345678A',
            'password' => Hash::make('luigi2005'),
            'education_level' => 'TM',
            'institution_name' => 'TelamoNet',
            'role' => 'admin',
        ]);

        // ----------------- Centros Educativos (EI) -----------------
        $centro1 = EducationalCenter::create([
            'name' => 'TelamoNet Institute',
            'type' => 'SE',
            'location' => 'Lanzarote'
        ]);
        User::factory()->create([
            'name' => 'Instituto',
            'last_name' => 'TelamoNet Center',
            'email' => 'contact@telamonet.edu',
            'dni' => '45678901D',
            'password' => Hash::make('institution123'),
            'education_level' => 'SE',
            'institution_name' => 'TelamoNet Institute',
            'role' => 'EI',
            'educational_center_id' => $centro1->id,
        ]);

        $centro2 = EducationalCenter::create([
            'name' => 'Colegio San Martín',
            'type' => 'PE',
            'location' => 'Arrecife'
        ]);
        User::factory()->create([
            'name' => 'Colegio',
            'last_name' => 'San Martín',
            'email' => 'contact@sanmartin.edu',
            'dni' => '56789012E',
            'password' => Hash::make('institution456'),
            'education_level' => 'PE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'EI',
            'educational_center_id' => $centro2->id,
        ]);

        $centro3 = EducationalCenter::create([
            'name' => 'Academia Futura',
            'type' => 'College',
            'location' => 'Teguise'
        ]);
        User::factory()->create([
            'name' => 'Academia',
            'last_name' => 'Futura',
            'email' => 'info@academiafutura.edu',
            'dni' => '67890123F',
            'password' => Hash::make('institution789'),
            'education_level' => 'College',
            'institution_name' => 'Academia Futura',
            'role' => 'EI',
            'educational_center_id' => $centro3->id,
        ]);

        $centro4 = EducationalCenter::create([
            'name' => 'Centro Avance',
            'type' => 'FP',
            'location' => 'San Bartolomé'
        ]);
        User::factory()->create([
            'name' => 'Centro',
            'last_name' => 'Avance',
            'email' => 'info@centroavance.edu',
            'dni' => '78901234G',
            'password' => Hash::make('institution321'),
            'education_level' => 'FP',
            'institution_name' => 'Centro Avance',
            'role' => 'EI',
            'educational_center_id' => $centro4->id,
        ]);

        // ----------------- Profesores -----------------
        User::factory()->create([
            'name' => 'María',
            'last_name' => 'Gómez Ruiz',
            'email' => 'maria.gomez@teacher.com',
            'dni' => '89012345H',
            'password' => Hash::make('teacher123'),
            'education_level' => 'College',
            'institution_name' => 'TelamoNet University',
            'role' => 'Teacher',
        ]);
        User::factory()->create([
            'name' => 'Carlos',
            'last_name' => 'López Díaz',
            'email' => 'carlos.lopez@teacher.com',
            'dni' => '90123456J',
            'password' => Hash::make('teacher456'),
            'education_level' => 'FP',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Teacher',
        ]);
        User::factory()->create([
            'name' => 'Ana',
            'last_name' => 'Fernández Torres',
            'email' => 'ana.fernandez@teacher.com',
            'dni' => '01234567K',
            'password' => Hash::make('teacher789'),
            'education_level' => 'College',
            'institution_name' => 'Academia Futura',
            'role' => 'Teacher',
        ]);
        User::factory()->create([
            'name' => 'Luis',
            'last_name' => 'Pérez Gómez',
            'email' => 'luis.perez@teacher.com',
            'dni' => '11223344L',
            'password' => Hash::make('teacher321'),
            'education_level' => 'SE',
            'institution_name' => 'TelamoNet Institute',
            'role' => 'Teacher',
        ]);
        User::factory()->create([
            'name' => 'Isabel',
            'last_name' => 'Santos García',
            'email' => 'isabel.santos@teacher.com',
            'dni' => '22334455M',
            'password' => Hash::make('teacher654'),
            'education_level' => 'PE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Teacher',
        ]);

        // ----------------- Estudiantes -----------------
        User::factory()->create([
            'name' => 'Lucas',
            'last_name' => 'Martínez Pérez',
            'email' => 'lucas.martinez@student.com',
            'dni' => '33445566N',
            'password' => Hash::make('student123'),
            'education_level' => 'College',
            'institution_name' => 'TelamoNet University',
            'role' => 'Student',
        ]);
        User::factory()->create([
            'name' => 'Sofía',
            'last_name' => 'García López',
            'email' => 'sofia.garcia@student.com',
            'dni' => '44556677O',
            'password' => Hash::make('student456'),
            'education_level' => 'SE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Student',
        ]);
        User::factory()->create([
            'name' => 'Mateo',
            'last_name' => 'Hernández Díaz',
            'email' => 'mateo.hernandez@student.com',
            'dni' => '55667788P',
            'password' => Hash::make('student789'),
            'education_level' => 'FP',
            'institution_name' => 'Academia Futura',
            'role' => 'Student',
        ]);
        User::factory()->create([
            'name' => 'Valentina',
            'last_name' => 'Ruiz Fernández',
            'email' => 'valentina.ruiz@student.com',
            'dni' => '66778899Q',
            'password' => Hash::make('student321'),
            'education_level' => 'PE',
            'institution_name' => 'TelamoNet Institute',
            'role' => 'Student',
        ]);
        User::factory()->create([
            'name' => 'Martín',
            'last_name' => 'Gómez Sánchez',
            'email' => 'martin.gomez@student.com',
            'dni' => '77889900R',
            'password' => Hash::make('student654'),
            'education_level' => 'College',
            'institution_name' => 'TelamoNet University',
            'role' => 'Student',
        ]);
        User::factory()->create([
            'name' => 'Lucía',
            'last_name' => 'Pérez Díaz',
            'email' => 'lucia.perez@student.com',
            'dni' => '88990011S',
            'password' => Hash::make('student987'),
            'education_level' => 'SE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Student',
        ]);
        User::factory()->create([
            'name' => 'Diego',
            'last_name' => 'López Torres',
            'email' => 'diego.lopez@student.com',
            'dni' => '99001122T',
            'password' => Hash::make('student159'),
            'education_level' => 'FP',
            'institution_name' => 'Academia Futura',
            'role' => 'Student',
        ]);

        // ----------------- Usuarios Externos (EU) -----------------
        User::factory()->create([
            'name' => 'David',
            'last_name' => 'Fernández López',
            'email' => 'david.fernandez@external.com',
            'dni' => '10101010U',
            'password' => Hash::make('external123'),
            'education_level' => 'US',
            'institution_name' => 'External Inc',
            'role' => 'EU',
        ]);
        User::factory()->create([
            'name' => 'Emma',
            'last_name' => 'Santos García',
            'email' => 'emma.santos@external.com',
            'dni' => '11111111V',
            'password' => Hash::make('external456'),
            'education_level' => 'US',
            'institution_name' => 'External Corp',
            'role' => 'EU',
        ]);
        User::factory()->create([
            'name' => 'Adrián',
            'last_name' => 'Moreno Ruiz',
            'email' => 'adrian.moreno@external.com',
            'dni' => '12121212W',
            'password' => Hash::make('external789'),
            'education_level' => 'US',
            'institution_name' => 'External Corp',
            'role' => 'EU',
        ]);
        User::factory()->create([
            'name' => 'Paula',
            'last_name' => 'Ramírez Díaz',
            'email' => 'paula.ramirez@external.com',
            'dni' => '13131313X',
            'password' => Hash::make('external321'),
            'education_level' => 'US',
            'institution_name' => 'External Inc',
            'role' => 'EU',
        ]);
        User::factory()->create([
            'name' => 'Jorge',
            'last_name' => 'Hernández Pérez',
            'email' => 'jorge.hernandez@external.com',
            'dni' => '14141414Y',
            'password' => Hash::make('external654'),
            'education_level' => 'US',
            'institution_name' => 'External Inc',
            'role' => 'EU',
        ]);
        // Helper to convert image to base64
        $imageBase64 = function ($path) {
            $fullPath = base_path('../backend/storage/app/public/' . $path);
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return $path;
        };

        $centro_evento = User::where('role', 'EI')->first();

        Event::create([
            'title' => '¡Acompañame a domar bestias!',
            'description' => 'Acompañame durante esta noche de hoy a domar bestias',
            'date' => '2026-02-20',
            'location' => 'puerto del Carmen',
            'start_time' => '22:00:00',
            'end_time' => '01:00:00',
            'educational_center_id' => $centro_evento->educational_center_id,
            'image' => $imageBase64('events/evento_luigi.png'),
        ]);
        Event::create([
            'title' => 'Taller de robótica',
            'description' => 'Aprende a construir y programar tu primer robot',
            'date' => '2026-02-22',
            'location' => 'Las Palmas',
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'educational_center_id' => $centro_evento->educational_center_id,
            'image' => $imageBase64('events/evento_robotica.png'),
        ]);

        Event::create([
            'title' => 'Clase de astronomía',
            'description' => 'Observaremos las estrellas y planetas con telescopios',
            'date' => '2026-02-25',
            'location' => 'Telde',
            'start_time' => '20:00:00',
            'end_time' => '23:00:00',
            'educational_center_id' => $centro_evento->educational_center_id,
            'image' => $imageBase64('events/evento_astronomia.png'),
        ]);

        Event::create([
            'title' => 'Concierto de música clásica',
            'description' => 'Disfruta de un repertorio de grandes compositores',
            'date' => '2026-03-01',
            'location' => 'Arrecife',
            'start_time' => '18:00:00',
            'end_time' => '20:30:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $imageBase64('events/evento_musica.png'),
        ]);

        Event::create([
            'title' => 'Feria de ciencias',
            'description' => 'Proyectos y experimentos realizados por estudiantes',
            'date' => '2026-03-05',
            'location' => 'Puerto del Carmen',
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $imageBase64('events/evento_ciencias.png'),
        ]);

        Event::create([
            'title' => 'Torneo de ajedrez',
            'description' => 'Compite y demuestra tu habilidad estratégica',
            'date' => '2026-03-10',
            'location' => 'Las Palmas',
            'start_time' => '11:00:00',
            'end_time' => '16:00:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $imageBase64('events/evento_ajedrez.png'),
        ]);
        echo "🏁 Seeding completado exitosamente.\n";
    }
}
