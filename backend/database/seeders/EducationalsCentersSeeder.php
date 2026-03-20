<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Cycle;
use Illuminate\Support\Facades\Hash;

class EducationalsCentersSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n📦 Iniciando instalación de centros...\n";

        // 🔹 FP
        $cycles = [
            'Gestión Administrativa (GM)',
            'Gestión Administrativa Semipresencial (GM)',
            'Administración y Finanzas (GS)',
            'Administración y Finanzas Semipresencial (GS)',
            'Asistencia a la Dirección (GS)',
            'Jardinería y Floristería (GM)',
            'Paisajismo y Medio Rural Semipresencial (GS)',
            'Actividades Comerciales (GM)',
            'Comercio Internacional (GS)',
            'Transporte y Logística (GS)',
            'Gestión de Ventas y Espacios Comerciales (GS)',
            'Instalaciones Eléctricas y Automáticas (GM)',
            'Instalaciones Eléctricas y Automáticas Semipresencial (GM)',
            'Sistemas Electrotécnicos y Automatizados (GS)',
            'Energías Renovables (GS)',
            'Cocina y Gastronomía (GM)',
            'Servicios en Restauración (GM)',
            'Dirección de Cocina (GS)',
            'Dirección de Servicios en Restauración (GS)',
            'Gestión de Alojamientos Turísticos (GS)',
            'Guía, Información y Asistencias Turísticas (GS)',
            'Peluquería y Cosmética Capilar (GM)',
            'Estética y Belleza (GM)',
            'Vitivinicultura (GS)',
            'Sistemas Microinformáticos y Redes (GM)',
            'Administración de Sistemas Informáticos en Red (GS)',
            'Desarrollo de Aplicaciones Web (GS)',
            'Inteligencia Artificial y Big Data (CE)',
            'Cuidados Auxiliares de Enfermería (GM)',
            'Cuidados Auxiliares de Enfermería Semipresencial (GM)',
            'Emergencias Sanitarias (GM)',
            'Emergencias Sanitarias Semipresencial (GM)',
            'Farmacia y Parafarmacia (GM)',
            'Imagen para el Diagnóstico y Medicina Nuclear (GS)',
            'Laboratorio Clínico y Biomédico (GS)',
            'Dietética (GS)',
            'Seguridad (GM)',
            'Coordinación de Emergencias y Protección Civil (GS)',
            'Prevención de Riesgos Profesionales (GS)',
            'Carrocería (GM)',
            'Electromecánica de Vehículos Automóviles (GM)',
            'Automoción (GS)',
            'Matemáticas',
            'Lengua Castellana',
            'Inglés',
            'Ciencias Naturales',
            'Geografía e Historia',
            'Educación Física',
            'Tecnología',
            'Física y Química'
        ];

        $centers = [

            // 🟢 PRIMARIA
            ['name' => 'CEIP Adolfo Topham',           'type' => 'PE'],
            ['name' => 'CEIP Argana Alta',             'type' => 'PE'],
            ['name' => 'CEIP Benito Méndez Tarajano',  'type' => 'PE'],
            ['name' => 'CEIP Los Geranios',            'type' => 'PE'],
            ['name' => 'CEIP Titerroy',                'type' => 'PE'],
            ['name' => 'CEIP Sanjurjo Maneje',         'type' => 'PE'],
            ['name' => 'CEIP Capellanía del Yágabo',   'type' => 'PE'],

            // 🔵 SECUNDARIA
            ['name' => 'IES Agustín Espinosa',         'type' => 'SE'],
            ['name' => 'IES Blas Cabrera Felipe',      'type' => 'SE'],
            ['name' => 'IES César Manrique',           'type' => 'SE'],
            ['name' => 'IES Las Salinas',              'type' => 'SE'],

            // 🟣 FP
            ['name' => 'CIFP Zonzamas',                'type' => 'FP'],
        ];
        foreach ($centers as $centerData) {

            echo "\n➡️ Centro: {$centerData['name']}\n";

            $center = EducationalCenter::firstOrCreate(
                ['name' => $centerData['name']],
                [
                    'type' => $centerData['type'],
                    'location' => 'Arrecife',
                ]
            );

            $cycleIds = [];

            if ($centerData['type'] === 'FP') {
                foreach ($cycles as $name) {
                    $c = Cycle::firstOrCreate(['name' => $name]);
                    $cycleIds[] = $c->id;
                }
                $cycleIds = Cycle::whereIn('name', array_slice($cycles, 0, 42))->pluck('id')->toArray();
            }

            $center->cycles()->sync($cycleIds);

            echo "   ✔️ Asignaturas y ciclos asignados\n";

            // 👤 Usuario
            $email = strtolower(str_replace(' ', '', $centerData['name'])) . '@admin.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin',
                    'last_name' => $centerData['name'],
                    'password' => Hash::make('12345678'),
                    'role' => 'EI',
                    'educational_center_id' => $center->id
                ]
            );

            echo "   👤 Usuario: $email creado\n";
        }

        echo "\n✅ Instalación completada\n";
    }
}