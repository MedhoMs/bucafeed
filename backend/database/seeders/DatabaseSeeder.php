<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "🌱 Iniciando seeding Maestro de TelamoNet...\n";

        // 1. Roles del Sistema
        $roles_iniciales = [
            'EU' => 'Usuario externo',
            'Student' => 'Estudiante',
            'Teacher' => 'Profesor',
            'Admin' => 'Administrador',
            'EI' => 'Centro educativo'
        ];

        foreach ($roles_iniciales as $code => $name) {
            Rol::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        // 2. Administradores del Sistema
        $admins = [
            ['name' => 'Admin', 'last_name' => 'Admin', 'email' =>'admin@gmail.com', 'dni' => '12345678G', 'password' => 'admin123'],

        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(['dni' => $admin['dni']], [
                'name' => $admin['name'],
                'last_name' => $admin['last_name'],
                'email' => $admin['email'],
                'password' => Hash::make($admin['password']),
                'role' => 'Admin',
                'education_level' => 'TM',
                'institution_name' => 'TelamoNet'
            ]);
        }

        // 3. Usuarios Externos
        $externalUsers = [
            ['name' => 'David', 'last_name' => 'Fernández', 'email' => 'david.external@gmail.com', 'dni' => '10101010U'],
            ['name' => 'Emma', 'last_name' => 'Santos', 'email' => 'emma.external@gmail.com', 'dni' => '11111111V'],
        ];

        foreach ($externalUsers as $eu) {
            User::updateOrCreate(['dni' => $eu['dni']], [
                'name' => $eu['name'],
                'last_name' => $eu['last_name'],
                'email' => $eu['email'],
                'password' => Hash::make('external123'),
                'role' => 'EU',
                'education_level' => 'US',
                'institution_name' => 'External'
            ]);
        }

        // 4. Seeders organizados por módulos
        $this->call([
            TagsSeeder::class,
            CyclesSeeder::class,
            CentersSeeder::class,
            UsersAndGroupsSeeder::class,
            MeetingsSeeder::class,
            EventsSeeder::class,
            ForumQuestionsSeeder::class,
        ]);

        echo "🏁 Seeding Maestro completado.\n";
    }
}
