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
            $teacher = User::where('role', 'Teacher')->where('educational_center_id', $center->id)->first();
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
                Meeting::updateOrCreate(
                    ['name' => 'Charla - ' . $group->name, 'group_id' => $group->id],
                    [
                        'teacher_id' => $teacher?->id ?? $group->tutor_id,
                        'teacher_name' => $teacher ? $teacher->name . " " . $teacher->last_name : 'Profesor',
                        'educational_center_id' => $center->id,
                        'schedule' => now()->addDays(rand(1, 30))->format('Y-m-d H:i:s'),
                        'description' => "Sesión de apoyo para el grupo {$group->name} de " . $center->name
                    ]
                );
            }
        }
    }
}
