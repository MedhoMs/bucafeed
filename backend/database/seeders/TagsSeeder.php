<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    public function run(): void
    {
        echo "🏷️ Creando Materias...\n";
        $materias = [
            'Programación', 'Bases de Datos', 'Despliegue de Aplicaciones', 'Entornos de Desarrollo',
            'Lenguaje de Marcas', 'Diseño de Interfaces Web', 'Sistemas Informáticos', 'FOL',
            'Matemáticas', 'Lengua Castellana', 'Inglés', 'Física', 'Química', 'Historia',
            'Dibujo Técnico', 'Educación Física', 'Artes Plásticas', 'Karate', 'Zen y Meditación',
            'Biología', 'Geografía', 'Filosofía', 'Economía', 'Tecnología'
        ];

        foreach ($materias as $m) {
            Tag::updateOrCreate(['name' => $m]);
        }
    }
}
