<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

        Usuario::create([
            'nombre' => 'inge tony',
            'email' => 'inge_tony@telamonet.com',
            'password' => Hash::make('mikaela123'),
        ]);

        Usuario::create([
            'nombre' => 'inge json',
            'email' => 'inge_json@telamonet.com',
            'password' => Hash::make('luigi2005'),
        ]);

        Usuario::create([
            'nombre' => 'lidel buca',
            'email' => 'lidel_bucat@telamonet.com',
            'password' => Hash::make('0000'),
        ]);


        // Roles de usuarios
        // $admin        = Role::create(['name' => 'admin']);
        // $teacher      = Role::create(['name' => 'teacher']);
        // $student      = Role::create(['name' => 'student']);
        // $externalUser = Role::create(['name' => 'externalUser']);

        // Permisos de usuarios
        // $createMeeting = Permission::create(['name' => 'create meeting']);
        // $accessMeeting = Permission::create(['name' => 'access meeting']);
        // $modifyMeeting = Permission::create(['name' => 'modify meeting']);
        // $deleteMeeting = Permission::create(['name' => 'delete meeting']);

        // Asignar roles a cada usuario
        // $admin->givePermissionTo($createMeeting, $accessMeeting, $modifyMeeting, $deleteMeeting);
        // $teacher->givePermissionTo($createMeeting, $accessMeeting, $modifyMeeting);
        // $student->givePermissionTo($accessMeeting);
        // $externalUser->givePermissionTo($accessMeeting);


    }
}
