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
        $admins = [
            [
                'email' => 'inge_tony@telamonet.com',
                'nombre' => 'inge tony',
                'password' => 'mikaela123',
            ],
            [
                'email' => 'inge_json@telamonet.com',
                'nombre' => 'inge json',
                'password' => 'luigi2005',
            ],
            [
                'email' => 'lidel_bucat@telamonet.com',
                'nombre' => 'lidel buca',
                'password' => '0000',
            ],
        ];

        foreach ($admins as $adminData) {
            $existing = Usuario::where('email', $adminData['email'])->first();
            if ($existing) {
                $existing->update([
                    'nombre' => $adminData['nombre'],
                    'password' => Hash::make($adminData['password']),
                ]);
            } else {
                Usuario::create([
                    'email' => $adminData['email'],
                    'nombre' => $adminData['nombre'],
                    'password' => Hash::make($adminData['password']),
                ]);
            }
        }


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
