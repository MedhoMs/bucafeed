<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cycle;
use App\Models\EducationalCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentersSeeder extends Seeder
{
    public function run(): void
    {
        echo "🏫 Centros Educativos de Lanzarote...\n";

        $fpCycles  = Cycle::whereIn('level', ['FP', 'GM', 'GS'])->pluck('id')->toArray();
        $seCycles  = Cycle::where('level', 'SE')->pluck('id')->toArray();
        $peCycles  = Cycle::where('level', 'PE')->pluck('id')->toArray();
        $urCycles  = Cycle::where('level', 'UR')->pluck('id')->toArray();

        $centers = [
            // === FORMACIÓN PROFESIONAL ===
            ['name' => 'CIFP Zonzamas',              'type' => 'FP', 'category' => 'CIFP',        'location' => 'Arrecife',     'cycles' => $fpCycles],

            // === INSTITUTOS SECUNDARIA ===
            ['name' => 'IES Agustín Espinosa',        'type' => 'SE', 'category' => 'IES',         'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'IES Blas Cabrera Felipe',     'type' => 'SE', 'category' => 'IES',         'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'IES César Manrique',          'type' => 'SE', 'category' => 'IES',         'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'IES Las Salinas',             'type' => 'SE', 'category' => 'IES',         'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'IES Las Maretas',             'type' => 'SE', 'category' => 'IES',         'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'IES San Bartolomé',           'type' => 'SE', 'category' => 'IES',         'location' => 'San Bartolomé','cycles' => $seCycles],
            ['name' => 'IES Playa Honda',             'type' => 'SE', 'category' => 'IES',         'location' => 'San Bartolomé','cycles' => $seCycles],
            ['name' => 'IES Tías',                    'type' => 'SE', 'category' => 'IES',         'location' => 'Tías',         'cycles' => $seCycles],
            ['name' => 'IES Tinajo',                  'type' => 'SE', 'category' => 'IES',         'location' => 'Tinajo',       'cycles' => $seCycles],
            ['name' => 'IES Yaiza',                   'type' => 'SE', 'category' => 'IES',         'location' => 'Yaiza',         'cycles' => $seCycles],
            ['name' => 'IES Haría',                   'type' => 'SE', 'category' => 'IES',         'location' => 'Haría',         'cycles' => $seCycles],
            ['name' => 'IES Altavista',               'type' => 'SE', 'category' => 'IES',         'location' => 'Arrecife',     'cycles' => $seCycles],

            // === CENTROS OBLIGATORIA ===
            ['name' => 'CEO Argana',                  'type' => 'SE', 'category' => 'CEO',         'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'CEO Ignacio Aldecoa',         'type' => 'SE', 'category' => 'CEO',         'location' => 'Teguise',      'cycles' => $seCycles],

            // === COLEGIOS PRIMARIA (selección representativa) ===
            ['name' => 'CEIP Adolfo Topham',          'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Antonio Zerolo',         'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Benito Méndez Tarajano', 'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Capellanía del Yagabo',  'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Los Geranios',           'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Mercedes Medina Díaz',   'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Nieves Toledo',          'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Titerroy',               'type' => 'PE', 'category' => 'CEIP',        'location' => 'Arrecife',     'cycles' => $peCycles],
            ['name' => 'CEIP Ajei',                   'type' => 'PE', 'category' => 'CEIP',        'location' => 'San Bartolomé','cycles' => $peCycles],
            ['name' => 'CEIP Playa Honda',            'type' => 'PE', 'category' => 'CEIP',        'location' => 'San Bartolomé','cycles' => $peCycles],
            ['name' => 'CEIP Concepción Rodríguez Artiles', 'type' => 'PE', 'category' => 'CEIP',  'location' => 'San Bartolomé','cycles' => $peCycles],
            ['name' => 'CEIP Alcalde Rafael Cedrés',  'type' => 'PE', 'category' => 'CEIP',        'location' => 'Tías',         'cycles' => $peCycles],
            ['name' => 'CEIP La Asomada Macher',      'type' => 'PE', 'category' => 'CEIP',        'location' => 'Tías',         'cycles' => $peCycles],
            ['name' => 'CEIP Playa Blanca',           'type' => 'PE', 'category' => 'CEIP',        'location' => 'Yaiza',        'cycles' => $peCycles],
            ['name' => 'CEIP Güime',                  'type' => 'PE', 'category' => 'CEIP',        'location' => 'San Bartolomé','cycles' => $peCycles],
            ['name' => 'CEIP Dr. Alfonso Spínola',    'type' => 'PE', 'category' => 'CEIP',        'location' => 'Teguise',      'cycles' => $peCycles],
            ['name' => 'CEIP La Caleta',              'type' => 'PE', 'category' => 'CEIP',        'location' => 'Teguise',      'cycles' => $peCycles],
            ['name' => 'CEIP Santa Bárbara',          'type' => 'PE', 'category' => 'CEIP',        'location' => 'Haría',        'cycles' => $peCycles],

            // === PRIVADOS / CONCERTADOS ===
            ['name' => 'Arenas Internacional',        'type' => 'SE', 'category' => 'Privado',     'location' => 'Tahíche',      'cycles' => $seCycles],
            ['name' => 'British School of Lanzarote', 'type' => 'SE', 'category' => 'Privado',     'location' => 'Tahíche',      'cycles' => $seCycles],
            ['name' => 'Colegio Dominicas',           'type' => 'SE', 'category' => 'Concertado',  'location' => 'Arrecife',     'cycles' => $seCycles],
            ['name' => 'Colegio Santa María de los Volcanes', 'type' => 'SE', 'category' => 'Concertado', 'location' => 'Arrecife','cycles' => $seCycles],

            // === UNIVERSIDAD ===
            ['name' => 'ULPGC Lanzarote',             'type' => 'UR', 'category' => 'Universidad', 'location' => 'Tahíche',      'cycles' => $urCycles],
        ];

        foreach ($centers as $data) {
            $safeName = strtolower(str_replace(
                [' ', 'í', 'é', 'ó', 'ú', 'á', 'ä', 'è', 'ü', 'ñ', "'", '.'],
                ['', 'i', 'e', 'o', 'u', 'a', 'a', 'e', 'u', 'n', '', ''],
                $data['name']
            ));
            $adminEmail = "admin.{$safeName}@telamonet.es";

            $admin = User::updateOrCreate(['email' => $adminEmail], [
                'name' => 'Admin', 'last_name' => $data['name'], 'password' => Hash::make('12345678'),
                'role' => 'EI', 'dni' => strtoupper(substr(md5($data['name']), 0, 8)) . 'X',
                'institution_name' => $data['name'], 'education_level' => 'Centro Educativo'
            ]);

            $center = EducationalCenter::updateOrCreate(['name' => $data['name']], [
                'location' => $data['location'], 'type' => $data['type'],
                'category' => $data['category'], 'admin_user_id' => $admin->id
            ]);

            $admin->update(['educational_center_id' => $center->id]);
            $center->cycles()->sync($data['cycles']);
        }
    }
}
