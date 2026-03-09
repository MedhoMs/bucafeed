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


        User::updateOrCreate(['email' => 'antoniomorera784@gmail.com'], [
            'name' => 'Antonio',
            'last_name' => 'Morera Marrero',
            'dni' => '78845622N',
            'password' => Hash::make('mikaela123'),
            'education_level' => 'TM',
            'institution_name' => 'TelamoNet',
            'role' => 'admin',
        ]);

        User::updateOrCreate(['email' => 'danielbucaloiu@gmail.com'], [
            'name' => 'Daniel',
            'last_name' => 'Bucaloiu Morales',
            'dni' => '12345678Z',
            'password' => Hash::make('0000'),
            'education_level' => 'TM',
            'institution_name' => 'TelamoNet',
            'role' => 'admin',
        ]);

        User::updateOrCreate(['email' => 'jasoncsotto16@gmail.com'], [
            'name' => 'Jason',
            'last_name' => 'Camila Sotto',
            'dni' => '12345678A',
            'password' => Hash::make('luigi2005'),
            'education_level' => 'TM',
            'institution_name' => 'TelamoNet',
            'role' => 'admin',
        ]);
        
        // ----------------- Centros Educativos (EI) -----------------

        $centro1 = EducationalCenter::firstOrCreate(['name' => 'TelamoNet Institute'], [
            'type' => 'SE',
            'location' => 'Lanzarote'
        ]);

        User::updateOrCreate(['email' => 'contact@telamonet.edu'], [
            'name' => 'Instituto',
            'last_name' => 'TelamoNet Center',
            'dni' => '45678901D',
            'password' => Hash::make('institution123'),
            'education_level' => 'SE',
            'institution_name' => 'TelamoNet Institute',
            'role' => 'EI',
            'educational_center_id' => $centro1->id,
        ]);
        

        
        $centro2 = EducationalCenter::firstOrCreate(['name' => 'Colegio San Martín'], [
            'type' => 'PE',
            'location' => 'Arrecife'
        ]);
        User::updateOrCreate(['email' => 'contact@sanmartin.edu'], [
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

        $centro3 = EducationalCenter::firstOrCreate(['name' => 'Academia Futura'], [
            'type' => 'College',
            'location' => 'Teguise'
        ]);
        User::updateOrCreate(['email' => 'info@academiafutura.edu'], [
            'name' => 'Academia',
            'last_name' => 'Futura',
            'dni' => '67890123F',
            'password' => Hash::make('institution789'),
            'education_level' => 'College',
            'institution_name' => 'Academia Futura',
            'role' => 'EI',
            'educational_center_id' => $centro3->id,
        ]);

        $centro4 = EducationalCenter::firstOrCreate(['name' => 'Centro Avance'], [
            'type' => 'FP',
            'location' => 'San Bartolomé'
        ]);
        User::updateOrCreate(['email' => 'info@centroavance.edu'], [
            'name' => 'Centro',
            'last_name' => 'Avance',
            'dni' => '78901234G',
            'password' => Hash::make('institution321'),
            'education_level' => 'FP',
            'institution_name' => 'Centro Avance',
            'role' => 'EI',
            'educational_center_id' => $centro4->id,
        ]);

        // ----------------- Profesores -----------------
        User::updateOrCreate(['email' => 'maria.gomez@teacher.com'], [
            'name' => 'María',
            'last_name' => 'Gómez Ruiz',
            'dni' => '89012345H',
            'password' => Hash::make('teacher123'),
            'education_level' => 'College',
            'institution_name' => 'TelamoNet University',
            'role' => 'Teacher',
        ]);
        User::updateOrCreate(['email' => 'carlos.lopez@teacher.com'], [
            'name' => 'Carlos',
            'last_name' => 'López Díaz',
            'dni' => '90123456J',
            'password' => Hash::make('teacher456'),
            'education_level' => 'FP',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Teacher',
        ]);
        User::updateOrCreate(['email' => 'ana.fernandez@teacher.com'], [
            'name' => 'Ana',
            'last_name' => 'Fernández Torres',
            'dni' => '01234567K',
            'password' => Hash::make('teacher789'),
            'education_level' => 'College',
            'institution_name' => 'Academia Futura',
            'role' => 'Teacher',
        ]);
        User::updateOrCreate(['email' => 'luis.perez@teacher.com'], [
            'name' => 'Luis',
            'last_name' => 'Pérez Gómez',
            'dni' => '11223344L',
            'password' => Hash::make('teacher321'),
            'education_level' => 'SE',
            'institution_name' => 'TelamoNet Institute',
            'role' => 'Teacher',
        ]);
        User::updateOrCreate(['email' => 'isabel.santos@teacher.com'], [
            'name' => 'Isabel',
            'last_name' => 'Santos García',
            'dni' => '22334455M',
            'password' => Hash::make('teacher654'),
            'education_level' => 'PE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Teacher',
        ]);

        // ----------------- Estudiantes -----------------
        User::updateOrCreate(['email' => 'lucas.martinez@student.com'], [
            'name' => 'Lucas',
            'last_name' => 'Martínez Pérez',
            'dni' => '33445566N',
            'password' => Hash::make('student123'),
            'education_level' => 'College',
            'institution_name' => 'TelamoNet University',
            'role' => 'Student',
        ]);
        User::updateOrCreate(['email' => 'sofia.garcia@student.com'], [
            'name' => 'Sofía',
            'last_name' => 'García López',
            'dni' => '44556677O',
            'password' => Hash::make('student456'),
            'education_level' => 'SE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Student',
        ]);
        User::updateOrCreate(['email' => 'mateo.hernandez@student.com'], [
            'name' => 'Mateo',
            'last_name' => 'Hernández Díaz',
            'dni' => '55667788P',
            'password' => Hash::make('student789'),
            'education_level' => 'FP',
            'institution_name' => 'Academia Futura',
            'role' => 'Student',
        ]);
        User::updateOrCreate(['email' => 'valentina.ruiz@student.com'], [
            'name' => 'Valentina',
            'last_name' => 'Ruiz Fernández',
            'dni' => '66778899Q',
            'password' => Hash::make('student321'),
            'education_level' => 'PE',
            'institution_name' => 'TelamoNet Institute',
            'role' => 'Student',
        ]);
        User::updateOrCreate(['email' => 'martin.gomez@student.com'], [
            'name' => 'Martín',
            'last_name' => 'Gómez Sánchez',
            'dni' => '77889900R',
            'password' => Hash::make('student654'),
            'education_level' => 'College',
            'institution_name' => 'TelamoNet University',
            'role' => 'Student',
        ]);
        User::updateOrCreate(['email' => 'lucia.perez@student.com'], [
            'name' => 'Lucía',
            'last_name' => 'Pérez Díaz',
            'dni' => '88990011S',
            'password' => Hash::make('student987'),
            'education_level' => 'SE',
            'institution_name' => 'Colegio San Martín',
            'role' => 'Student',
        ]);
        User::updateOrCreate(['email' => 'diego.lopez@student.com'], [
            'name' => 'Diego',
            'last_name' => 'López Torres',
            'dni' => '99001122T',
            'password' => Hash::make('student159'),
            'education_level' => 'FP',
            'institution_name' => 'Academia Futura',
            'role' => 'Student',
        ]);

        // ----------------- Usuarios Externos (EU) -----------------
        User::updateOrCreate(['email' => 'david.fernandez@external.com'], [
            'name' => 'David',
            'last_name' => 'Fernández López',
            'dni' => '10101010U',
            'password' => Hash::make('external123'),
            'education_level' => 'US',
            'institution_name' => 'External Inc',
            'role' => 'EU',
        ]);
        User::updateOrCreate(['email' => 'emma.santos@external.com'], [
            'name' => 'Emma',
            'last_name' => 'Santos García',
            'dni' => '11111111V',
            'password' => Hash::make('external456'),
            'education_level' => 'US',
            'institution_name' => 'External Corp',
            'role' => 'EU',
        ]);
        User::updateOrCreate(['email' => 'adrian.moreno@external.com'], [
            'name' => 'Adrián',
            'last_name' => 'Moreno Ruiz',
            'dni' => '12121212W',
            'password' => Hash::make('external789'),
            'education_level' => 'US',
            'institution_name' => 'External Corp',
            'role' => 'EU',
        ]);
        User::updateOrCreate(['email' => 'paula.ramirez@external.com'], [
            'name' => 'Paula',
            'last_name' => 'Ramírez Díaz',
            'dni' => '13131313X',
            'password' => Hash::make('external321'),
            'education_level' => 'US',
            'institution_name' => 'External Inc',
            'role' => 'EU',
        ]);
        User::updateOrCreate(['email' => 'jorge.hernandez@external.com'], [
            'name' => 'Jorge',
            'last_name' => 'Hernández Pérez',
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

        Event::firstOrCreate(['title' => '¡Acompañame a domar bestias!'], [
            'description' => 'Acompañame durante esta noche de hoy a domar bestias',
            'date' => '2026-02-20',
            'location' => 'puerto del Carmen',
            'start_time' => '22:00:00',
            'end_time' => '01:00:00',
            'educational_center_id' => $centro_evento->educational_center_id,
            'image' => $eventImageHandler('events/evento_luigi.png'),
        ]);
        Event::firstOrCreate(['title' => 'Taller de robótica'], [
            'description' => 'Aprende a construir y programar tu primer robot',
            'date' => '2026-02-22',
            'location' => 'Las Palmas',
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'educational_center_id' => $centro_evento->educational_center_id,
            'image' => $eventImageHandler('events/evento_robotica.png'),
        ]);

        Event::firstOrCreate(['title' => 'Clase de astronomía'], [
            'description' => 'Observaremos las estrellas y planetas con telescopios',
            'date' => '2026-02-25',
            'location' => 'Telde',
            'start_time' => '20:00:00',
            'end_time' => '23:00:00',
            'educational_center_id' => $centro_evento->educational_center_id,
            'image' => $eventImageHandler('events/evento_astronomia.png'),
        ]);

        Event::firstOrCreate(['title' => 'Concierto de música clásica'], [
            'description' => 'Disfruta de un repertorio de grandes compositores',
            'date' => '2026-03-01',
            'location' => 'Arrecife',
            'start_time' => '18:00:00',
            'end_time' => '20:30:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $eventImageHandler('events/evento_musica.png'),
        ]);

        Event::firstOrCreate(['title' => 'Feria de ciencias'], [
            'description' => 'Proyectos y experimentos realizados por estudiantes',
            'date' => '2026-03-05',
            'location' => 'Puerto del Carmen',
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $eventImageHandler('events/evento_ciencias.png'),
        ]);

        Event::firstOrCreate(['title' => 'Torneo de ajedrez'], [
            'description' => 'Compite y demuestra tu habilidad estratégica',
            'date' => '2026-03-10',
            'location' => 'Las Palmas',
            'start_time' => '11:00:00',
            'end_time' => '16:00:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $eventImageHandler('events/evento_ajedrez.png'),
        ]);

        Event::firstOrCreate(['title' => 'Cita mágica con mi sol radiante ☀️💕'], [
            'description' => 'Un encuentro lleno de chispas, risas y miradas que solo nosotros entendemos',
            'date' => '2026-03-14',
            'location' => 'Jardines del Paraíso 🌺✨',
            'start_time' => '20:00:00',
            'end_time' => '23:00:00',
            'educational_center_id' => $centro_evento->id,
            'image' => $eventImageHandler('events/event_3_1773005324.png'),
        ]);
        echo "🏁 Seeding completado exitosamente.\n";
    }
}
