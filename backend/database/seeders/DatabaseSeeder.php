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

        echo "🏁 Seeding completado exitosamente.\n";
    }
}


