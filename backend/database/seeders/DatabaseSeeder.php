<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "🌱 Iniciando seeding de TelamoNet...\n";

        // 0. Configurar Roles y Permisos (Spatie)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        $accessMeeting = Permission::firstOrCreate(['name' => 'access meeting']);
        $createMeeting = Permission::firstOrCreate(['name' => 'create meeting']);

        $adminRole->givePermissionTo(Permission::all());
        $teacherRole->givePermissionTo([$accessMeeting, $createMeeting]);
        $studentRole->givePermissionTo($accessMeeting);
        $userRole->givePermissionTo($accessMeeting);

        // 0.1 Usuarios Administradores Específicos
        $admins = [
            [
                'email' => 'inge_tony@telamonet.com',
                'name' => 'inge tony',
                'last_name' => 'Admin',
                'password' => Hash::make('mikaela123'),
                'role' => 'admin',
                'national_id' => 'ADM-001',
            ],
            [
                'email' => 'inge_json@telamonet.com',
                'name' => 'inge json',
                'last_name' => 'Admin',
                'password' => Hash::make('luigi2005'),
                'role' => 'admin',
                'national_id' => 'ADM-002',
            ],
            [
                'email' => 'lidel_bucat@telamonet.com',
                'name' => 'lidel buca',
                'last_name' => 'Admin',
                'password' => Hash::make('0000'),
                'role' => 'admin',
                'national_id' => 'ADM-003',
            ],
        ];

        foreach ($admins as $adminData) {
            $user = User::firstOrCreate(['email' => $adminData['email']], $adminData);
            $user->assignRole($adminRole);
            echo "✅ Administrador procesado: " . $user->email . "\n";
        }

        // 1. Centros educativos
        DB::table('educational_centers')->insert([
            ['name' => 'IES Las Salinas', 'type' => 'Secondary', 'location' => 'Lanzarote', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CIFP Zonzamas', 'type' => 'Secondary', 'location' => 'Lanzarote', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ULPGC', 'type' => 'University', 'location' => 'Lanzarote', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Usuarios de Centros (Profesores y Alumnos)
        // IDs empezarán después de los 3 admins
        
        // Profesores IES Las Salinas
        for ($i = 1; $i <= 4; $i++) {
            $user = User::create([
                'name' => 'Profesor', 'last_name' => (string)$i, 'email' => "prof$i@salinas.edu",
                'password' => Hash::make('pass123'), 'role' => 'teacher', 'educational_center_id' => 1,
                'national_id' => "1111111$i" . chr(64 + $i), 'created_at' => now(), 'updated_at' => now()
            ]);
            $user->assignRole($teacherRole);
        }

        // Alumnos IES Las Salinas
        for ($i = 1; $i <= 4; $i++) {
            $user = User::create([
                'name' => 'Alumno', 'last_name' => (string)$i, 'email' => "alu$i@salinas.edu",
                'password' => Hash::make('pass123'), 'role' => 'student', 'educational_center_id' => 1,
                'national_id' => "2222222$i" . chr(64 + $i), 'education_level' => 'secondary',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $user->assignRole($studentRole);
        }

        // Repetir para Zonzamas y ULPGC... (simplificado para brevedad pero funcional)
        $centers = [2 => 'zonzamas.edu', 3 => 'ulpgc.edu'];
        foreach($centers as $cId => $domain) {
            for ($i = 1; $i <= 4; $i++) {
                $u = User::create([
                    'name' => 'Profesor', 'last_name' => (string)$i, 'email' => "prof$i@$domain",
                    'password' => Hash::make('pass123'), 'role' => 'teacher', 'educational_center_id' => $cId,
                    'national_id' => "333333$cId$i", 'created_at' => now(), 'updated_at' => now()
                ]);
                $u->assignRole($teacherRole);
                
                $u2 = User::create([
                    'name' => 'Alumno', 'last_name' => (string)$i, 'email' => "alu$i@$domain",
                    'password' => Hash::make('pass123'), 'role' => 'student', 'educational_center_id' => $cId,
                    'national_id' => "444444$cId$i", 'education_level' => 'secondary', 'created_at' => now(), 'updated_at' => now()
                ]);
                $u2->assignRole($studentRole);
            }
        }

        // 3. Teachers & Students (tablas relacionales)
        // Obtenemos los usuarios creados para vincularlos
        $teachers = User::role('teacher')->get();
        foreach ($teachers as $t) {
            DB::table('teachers')->insert([
                'user_id' => $t->id, 'educational_center_id' => $t->educational_center_id,
                'specialty' => 'Especialidad Genérica', 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        $students = User::role('student')->get();
        foreach ($students as $s) {
            DB::table('students')->insert([
                'user_id' => $s->id, 'educational_center_id' => $s->educational_center_id,
                'course' => '1º', 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 4. Eventos
        DB::table('events')->insert([
            ['educational_center_id' => 1, 'title' => 'Feria de Ciencias', 'description' => 'Exposición de proyectos.', 'start_date' => now(), 'end_date' => now()->addHours(4), 'location_maps' => 'https://goo.gl/maps/abc123', 'created_at' => now(), 'updated_at' => now()],
            ['educational_center_id' => 2, 'title' => 'Jornada FP', 'description' => 'Taller de empleo.', 'start_date' => now(), 'end_date' => now()->addHours(2), 'location_maps' => 'https://goo.gl/maps/def456', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Preguntas y Respuestas
        $qId = DB::table('questions')->insertGetId([
            'user_id' => $students->first()->id, 'title' => '¿Duda sobre Laravel?', 'content' => '¿Cómo funcionan los seeders?', 'is_ai_validated' => true, 'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('answers')->insert([
            'question_id' => $qId, 'user_id' => $teachers->first()->id, 'content' => 'Los seeders sirven para poblar la base de datos.', 'reputation' => 10, 'created_at' => now(), 'updated_at' => now()
        ]);

        echo "🏁 Seeding completado exitosamente.\n";
    }
}
