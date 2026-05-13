<?php

namespace Database\Seeders;

use App\Models\Cycle;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class CyclesSeeder extends Seeder
{
    public function run(): void
    {
        echo "📚 Creando Ciclos y vinculando materias...\n";

        $tagModels = Tag::pluck('id', 'name');

        // === FP: Informática (con cursos 1º/2º como antes) ===
        $fpCycleNames = [
            'DAW' => 'Desarrollo de Aplicaciones Web',
            'DAM' => 'Desarrollo de Aplicaciones Multiplataforma',
            'ASIR' => 'Administración de Sistemas Informáticos en Red',
        ];
        $allFpCycles = [];
        foreach ($fpCycleNames as $short => $long) {
            for ($course = 1; $course <= 2; $course++) {
                $name = "{$course}º{$short}";
                $c = Cycle::updateOrCreate(['name' => $name], ['level' => 'FP']);
                $allFpCycles[] = $c;
            }
        }

        if (isset($allFpCycles[0]) && isset($tagModels['Programación'], $tagModels['Diseño de Interfaces Web'], $tagModels['Bases de Datos']))
            $allFpCycles[0]->tags()->sync([$tagModels['Programación'], $tagModels['Diseño de Interfaces Web'], $tagModels['Bases de Datos']]);
        if (isset($allFpCycles[1]) && isset($tagModels['Programación'], $tagModels['Despliegue de Aplicaciones'], $tagModels['Entornos de Desarrollo']))
            $allFpCycles[1]->tags()->sync([$tagModels['Programación'], $tagModels['Despliegue de Aplicaciones'], $tagModels['Entornos de Desarrollo']]);
        if (isset($allFpCycles[2]) && isset($tagModels['Programación'], $tagModels['Sistemas Informáticos'], $tagModels['Lenguaje de Marcas']))
            $allFpCycles[2]->tags()->sync([$tagModels['Programación'], $tagModels['Sistemas Informáticos'], $tagModels['Lenguaje de Marcas']]);
        if (isset($allFpCycles[3]) && isset($tagModels['Programación'], $tagModels['Despliegue de Aplicaciones'], $tagModels['Bases de Datos']))
            $allFpCycles[3]->tags()->sync([$tagModels['Programación'], $tagModels['Despliegue de Aplicaciones'], $tagModels['Bases de Datos']]);
        if (isset($allFpCycles[4]) && isset($tagModels['Sistemas Informáticos'], $tagModels['Física'], $tagModels['FOL']))
            $allFpCycles[4]->tags()->sync([$tagModels['Sistemas Informáticos'], $tagModels['Física'], $tagModels['FOL']]);
        if (isset($allFpCycles[5]) && isset($tagModels['Sistemas Informáticos'], $tagModels['Bases de Datos'], $tagModels['Inglés']))
            $allFpCycles[5]->tags()->sync([$tagModels['Sistemas Informáticos'], $tagModels['Bases de Datos'], $tagModels['Inglés']]);

        // === FP: Resto de ciclos del CIFP Zonzamas ===
        $restoFp = [
            // Administración y Gestión
            ['name' => 'Gestión Administrativa',                 'level' => 'GM', 'tags' => ['Economía', 'FOL']],
            ['name' => 'Administración y Finanzas',              'level' => 'GS', 'tags' => ['Economía', 'FOL']],
            ['name' => 'Asistencia a la Dirección',              'level' => 'GS', 'tags' => ['Economía', 'Inglés']],
            // Agraria (Jardinería)
            ['name' => 'Jardinería y Floristería',               'level' => 'GM', 'tags' => ['Biología', 'Tecnología']],
            ['name' => 'Paisajismo y Medio Rural',               'level' => 'GS', 'tags' => ['Biología', 'Dibujo Técnico']],
            ['name' => 'Vitivinicultura',                       'level' => 'GS', 'tags' => ['Biología', 'Química']],
            // Comercio y Marketing
            ['name' => 'Actividades Comerciales',                'level' => 'GM', 'tags' => ['Economía', 'Inglés']],
            ['name' => 'Comercio Internacional',                 'level' => 'GS', 'tags' => ['Economía', 'Inglés']],
            ['name' => 'Transporte y Logística',                'level' => 'GS', 'tags' => ['Economía', 'Tecnología']],
            // Electricidad y Electrónica
            ['name' => 'Instalaciones Eléctricas y Automáticas', 'level' => 'GM', 'tags' => ['Física', 'Tecnología']],
            ['name' => 'Sistemas Electrotécnicos y Automatizados','level' => 'GS', 'tags' => ['Física', 'Tecnología']],
            ['name' => 'Automatización y Robótica Industrial',   'level' => 'GS', 'tags' => ['Programación', 'Tecnología']],
            // Energía y Agua
            ['name' => 'Eficiencia Energética y Energía Solar',  'level' => 'GS', 'tags' => ['Física', 'Tecnología']],
            // Hostelería y Turismo
            ['name' => 'Cocina y Gastronomía',                  'level' => 'GM', 'tags' => ['FOL']],
            ['name' => 'Servicios de Restauración',             'level' => 'GM', 'tags' => ['FOL']],
            ['name' => 'Dirección de Cocina',                   'level' => 'GS', 'tags' => ['FOL', 'Economía']],
            ['name' => 'Gestión de Alojamientos Turísticos',    'level' => 'GS', 'tags' => ['Inglés', 'Economía']],
            ['name' => 'Guía, Información y Asistencia Turísticas','level' => 'GS','tags' => ['Inglés', 'Geografía']],
            // Imagen Personal (Peluquería)
            ['name' => 'Estética y Belleza',                    'level' => 'GM', 'tags' => ['Artes Plásticas']],
            ['name' => 'Peluquería y Cosmética Capilar',        'level' => 'GM', 'tags' => ['Artes Plásticas']],
            // Industrias Alimentarias
            ['name' => 'Procesos y Calidad en Industria Alimentaria','level' => 'GS','tags' => ['Química', 'Biología']],
            // Sanidad
            ['name' => 'Cuidados Auxiliares de Enfermería',     'level' => 'GM', 'tags' => ['Biología']],
            ['name' => 'Emergencias Sanitarias',                'level' => 'GM', 'tags' => ['Biología', 'Física']],
            ['name' => 'Farmacia y Parafarmacia',               'level' => 'GM', 'tags' => ['Química', 'Biología']],
            ['name' => 'Laboratorio Clínico y Biomédico',       'level' => 'GS', 'tags' => ['Biología', 'Química']],
            ['name' => 'Dietética',                             'level' => 'GS', 'tags' => ['Biología', 'Física']],
            // Seguridad y Medioambiente
            ['name' => 'Coordinación de Emergencias y Protección Civil','level' => 'GS','tags' => ['Educación Física', 'FOL']],
            ['name' => 'Educación y Control Ambiental',         'level' => 'GS', 'tags' => ['Biología', 'Geografía']],
            // Transporte y Mantenimiento de Vehículos
            ['name' => 'Carrocería',                            'level' => 'GM', 'tags' => ['Tecnología']],
            ['name' => 'Electromecánica de Vehículos Automóviles','level' => 'GM','tags' => ['Física', 'Tecnología']],
            ['name' => 'Automoción',                            'level' => 'GS', 'tags' => ['Física', 'Tecnología']],
        ];

        $addedResto = [];
        foreach ($restoFp as $r) {
            $c = Cycle::updateOrCreate(['name' => $r['name']], ['level' => $r['level']]);
            if (!empty($r['tags'])) {
                $tagIds = [];
                foreach ($r['tags'] as $t) {
                    if (isset($tagModels[$t])) $tagIds[] = $tagModels[$t];
                }
                if (!empty($tagIds)) $c->tags()->sync($tagIds);
            }
            $addedResto[] = $c;
        }

        // ESO
        for ($i = 1; $i <= 4; $i++) {
            $c = Cycle::updateOrCreate(['name' => "{$i}º ESO"], ['level' => 'SE']);
            if (isset($tagModels['Matemáticas'], $tagModels['Lengua Castellana'], $tagModels['Inglés'], $tagModels['Geografía']))
                $c->tags()->sync([$tagModels['Matemáticas'], $tagModels['Lengua Castellana'], $tagModels['Inglés'], $tagModels['Geografía']]);
        }

        // Bachillerato
        for ($i = 1; $i <= 2; $i++) {
            $c = Cycle::updateOrCreate(['name' => "{$i}º Bachillerato"], ['level' => 'SE']);
            if (isset($tagModels['Historia'], $tagModels['Física'], $tagModels['Inglés'], $tagModels['Filosofía']))
                $c->tags()->sync([$tagModels['Historia'], $tagModels['Física'], $tagModels['Inglés'], $tagModels['Filosofía']]);
        }

        // Primaria
        for ($i = 1; $i <= 6; $i++) {
            $c = Cycle::updateOrCreate(['name' => "{$i}º Primaria"], ['level' => 'PE']);
            if (isset($tagModels['Matemáticas'], $tagModels['Lengua Castellana'], $tagModels['Artes Plásticas']))
                $c->tags()->sync([$tagModels['Matemáticas'], $tagModels['Lengua Castellana'], $tagModels['Artes Plásticas']]);
        }

        // Universidad
        Cycle::updateOrCreate(['name' => 'Grado en Enfermería'], ['level' => 'UR']);
        Cycle::updateOrCreate(['name' => 'Grado en Turismo'], ['level' => 'UR']);
        Cycle::updateOrCreate(['name' => 'Grado en Informática'], ['level' => 'UR']);
    }
}
