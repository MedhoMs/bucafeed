<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Group;
use App\Models\EducationalCenter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AddUsers extends Command
{
    protected $signature = 'telamonet:add-users {count=50 : Número de usuarios a crear} {--center-id= : ID del centro al que asignarlos}';
    protected $description = 'Añade usuarios aleatorios (estudiantes y profesores) a la plataforma';

    private $nombres = ['Carlos', 'Lucía', 'Mateo', 'Valentina', 'Alejandro', 'Sofía', 'Hugo', 'Martina', 'Daniel', 'Julia', 'Pablo', 'Emma', 'Diego', 'Valeria', 'Alba', 'Mario', 'Elena', 'Adrian', 'Paula', 'Marcos', 'Irene', 'Raúl', 'Lara', 'Sergio', 'Claudia', 'Jorge', 'Natalia', 'Álvaro', 'Celia', 'Ismael', 'Ainoa', 'Rubén', 'Cristina', 'Oriol', 'Marina', 'Guille', 'Laia', 'Nacho', 'Rocío', 'Edu', 'Ángela', 'Unai', 'Marta', 'Víctor', 'Silvia', 'Iván', 'Gema', 'Javier', 'Nerea', 'Álex'];
    private $apellidos = ['García', 'Fernández', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Ramírez', 'Torres', 'Díaz', 'Muñoz', 'Romero', 'Alonso', 'Navarro', 'Ruiz', 'Jiménez', 'Moreno', 'Álvarez', 'Gutiérrez', 'Castro', 'Ortiz', 'Rubio', 'Molina', 'Delgado', 'Gil', 'Serrano', 'Blanco', 'Cortés', 'Suárez', 'Mendoza', 'Herrera', 'Medina', 'Garrido', 'Vargas', 'Flores', 'Peña', 'Cabrera', 'Campos', 'Santos', 'Iglesias', 'Cruz', 'Reyes', 'Vega', 'Aguilar', 'Carrasco', 'Benítez', 'Moya', 'Rivas', 'Pascual'];

    public function handle()
    {
        $count = (int) $this->argument('count');
        $centerId = $this->option('center-id');

        $centers = EducationalCenter::all();
        if ($centers->isEmpty()) {
            $this->error('No hay centros educativos. Ejecuta primero php artisan db:seed.');
            return 1;
        }

        if ($centerId) {
            $center = EducationalCenter::find($centerId);
            if (!$center) {
                $this->error("Centro con ID {$centerId} no encontrado.");
                return 1;
            }
            $centers = collect([$center]);
        }

        $created = 0;
        $existingEmails = User::pluck('email')->toArray();

        foreach ($centers as $center) {
            $groups = Group::where('educational_center_id', $center->id)->get();
            if ($groups->isEmpty()) {
                $this->warn("El centro '{$center->name}' no tiene grupos. Saltando...");
                continue;
            }

            $centerCount = intval($count / $centers->count());
            $this->info("Añadiendo ~{$centerCount} usuarios a {$center->name}...");

            for ($i = 0; $i < $centerCount; $i++) {
                $name = $this->nombres[array_rand($this->nombres)];
                $surname = $this->apellidos[array_rand($this->apellidos)];
                $seed = rand(100000, 999999);
                $email = strtolower($name) . "." . strtolower($surname) . "." . $seed . "@" . strtolower(str_replace(' ', '', $center->name)) . ".es";

                if (in_array($email, $existingEmails)) continue;

                $role = rand(0, 4) === 0 ? 'Teacher' : 'Student';
                $group = $groups->random();

                $dni = sprintf('%08d', (10000000 + $seed)) . ($role === 'Teacher' ? 'T' : 'S');
                $existingEmails[] = $email;

                User::create([
                    'name' => $name,
                    'last_name' => $surname,
                    'email' => $email,
                    'password' => Hash::make('12345678'),
                    'role' => $role,
                    'educational_center_id' => $center->id,
                    'dni' => $dni,
                    'institution_name' => $center->name,
                    'education_level' => $center->type,
                ]);

                // Assign to group if student
                if ($role === 'Student') {
                    $group->students()->syncWithoutDetaching([User::where('email', $email)->first()->id]);
                }

                $created++;
            }
        }

        $this->info("¡{$created} usuarios creados correctamente!");
        return 0;
    }
}
