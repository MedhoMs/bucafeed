<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ADMINISTRADORES
        User::create([
            'name' => 'inge tony',
            'last_name' => 'Admin',
            'email' => 'inge_tony@telamonet.com',
            'password' => Hash::make('mikaela123'),
            'role' => 'admin',
            'national_id' => 'ADM-001',
        ]);

        User::create([
            'name' => 'inge json',
            'last_name' => 'Admin',
            'email' => 'inge_json@telamonet.com',
            'password' => Hash::make('luigi2005'),
            'role' => 'admin',
            'national_id' => 'ADM-002',
        ]);

        User::create([
            'name' => 'lidel buca',
            'last_name' => 'Admin',
            'email' => 'lidel_bucat@telamonet.com',
            'password' => Hash::make('0000'),
            'role' => 'admin',
            'national_id' => 'ADM-003',
        ]);

        // Roles de usuarios
        // $admin        = Role::create(['name' => 'admin']);
        // $teacher      = Role::create(['name' => 'teacher']);
        // $student      = Role::create(['name' => 'student']);
        // $externalUser = Role::create(['name' => 'externalUser']);
    }
}
