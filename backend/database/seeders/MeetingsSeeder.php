<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\EducationalCenter;
use Illuminate\Database\Seeder;

class MeetingsSeeder extends Seeder
{
    public function run(): void
    {
        echo "🎤 Creando Charlas (Meetings)...\n";

        $centers = EducationalCenter::all();
        if ($centers->isEmpty()) return;

        foreach ($centers as $center) {
            // Buscamos un profesor o al administrador del centro como tutor por defecto
            $teacher = User::where('role', 'Teacher')->where('educational_center_id', $center->id)->first() 
                      ?? User::find($center->admin_user_id);

            if ($teacher) {
                Meeting::updateOrCreate(['name' => 'Tutoría General - ' . $center->name], [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name . " " . $teacher->last_name,
                    'educational_center_id' => $center->id,
                    'schedule' => now()->format('Y-m-d H:i:s'),
                    'description' => "Sesión de tutoría general para alumnos de " . $center->name
                ]);
            }

            $groups = Group::where('educational_center_id', $center->id)->take(3)->get();
            foreach ($groups as $group) {
                $groupTeacher = $teacher ?? User::find($group->tutor_id);
                
                if ($groupTeacher) {
                    Meeting::updateOrCreate(
                        ['name' => 'Charla - ' . $group->name, 'group_id' => $group->id],
                        [
                            'teacher_id' => $groupTeacher->id,
                            'teacher_name' => $groupTeacher->name . " " . $groupTeacher->last_name,
                            'educational_center_id' => $center->id,
                            'schedule' => now()->addDays(rand(1, 30))->format('Y-m-d H:i:s'),
                            'description' => "Sesión de apoyo para el grupo {$group->name} de " . $center->name
                        ]
                    );
                }
            }
        }

        // Charlas específicas para Administración TelamoNet (Antonio, Daniel, Jason)
        $adminCenter = EducationalCenter::where('name', 'Administración TelamoNet')->first();
        $admins = User::where('role', 'Admin')->get();
        
        if ($adminCenter) {
            foreach ($admins as $admin) {
                Meeting::updateOrCreate(['name' => 'Reunión TelamoNet - ' . $admin->name], [
                    'teacher_id' => $admin->id,
                    'teacher_name' => $admin->name . " " . $admin->last_name,
                    'educational_center_id' => $adminCenter->id,
                    'schedule' => now()->addHours(2)->format('Y-m-d H:i:s'),
                    'description' => "Sesión informativa impartida por {$admin->name} sobre la gestión de la plataforma."
                ]);
            }
        }
    }
}
