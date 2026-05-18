<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\EducationalCenter;
use Illuminate\Database\Seeder;

class PublicationsSeeder extends Seeder
{
    public function run(): void
    {
        echo "📰 Creando publicaciones y noticias de los Centros Educativos...\n";

        $publications = [
            [
                'center' => 'CIFP Zonzamas',
                'title' => 'Inauguración del nuevo Aula Interactiva de Realidad Virtual',
                'description' => 'El CIFP Zonzamas da un paso al frente en la digitalización de sus familias profesionales con la puesta en marcha de un aula interactiva de realidad virtual. Los alumnos de Automoción, Sanidad y Edificación podrán realizar prácticas y simulaciones complejas en entornos inmersivos de alta fidelidad tecnológica.',
                'image' => 'https://images.unsplash.com/photo-1593508512255-86ab42a8e620?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'CIFP Zonzamas',
                'title' => 'Ganadores del Hackathon de Desarrollo Sostenible de Lanzarote',
                'description' => 'El equipo de alumnos de Desarrollo de Aplicaciones Web (DAW) del CIFP Zonzamas se alza con el primer premio en el Hackathon insular, tras diseñar una aplicación web móvil interactiva orientada a optimizar y gamificar la recogida de residuos en las playas de Lanzarote.',
                'image' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'IES César Manrique',
                'title' => 'Homenaje a César Manrique: mural conmemorativo gigante en el patio principal',
                'description' => 'Los alumnos del Bachillerato Artístico del IES César Manrique finalizan con éxito la creación de un impresionante mural de 20 metros de longitud en honor al legado del artista lanzaroteño, combinando técnicas de grafiti ecológico, texturas volcánicas y pintura mural tradicional.',
                'image' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'IES Blas Cabrera Felipe',
                'title' => 'Proyecto Erasmus+: Intercambio internacional con estudiantes de Múnich',
                'description' => 'Esta semana recibimos en el IES Blas Cabrera Felipe al grupo de intercambio de estudiantes alemanes dentro del marco del programa europeo Erasmus+. Durante diez días debatirán sobre sostenibilidad insular, energías renovables, y realizarán visitas técnicas conjuntas a las plantas eólicas locales.',
                'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'ULPGC Lanzarote',
                'title' => 'Jornadas de Turismo Sostenible, Reservas de la Biosfera y Economía Circular',
                'description' => 'La sede de la ULPGC en Lanzarote acoge un ciclo de ponencias y mesas de debate enfocadas en el futuro del modelo turístico y la protección ambiental de la isla. El evento cuenta con la participación de destacados expertos nacionales y estudiantes del Grado en Turismo.',
                'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'CEO Ignacio Aldecoa',
                'title' => 'Talleres prácticos de biodiversidad marina en La Graciosa',
                'description' => 'El alumnado del CEO Ignacio Aldecoa participa activamente en el proyecto educativo de protección del litoral de La Graciosa, estudiando especies protegidas y recolectando microplásticos de forma controlada en colaboración directa con biólogos marinos.',
                'image' => 'https://images.unsplash.com/photo-1546026423-cc4642628d2b?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'IES Las Salinas',
                'title' => 'Feria Solidaria de la Ciencia y la Tecnología del IES Las Salinas',
                'description' => 'Un año más, el alumnado de robótica y tecnología presenta sus inventos interactivos abiertos al público. La entrada benéfica recaudará fondos íntegros destinados a la Cruz Roja para familias vulnerables del municipio de Arrecife.',
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'Arenas Internacional',
                'title' => 'Jornada Deportiva Intercentros del Colegio Arenas Internacional',
                'description' => 'Éxito rotundo en la edición anual de los juegos deportivos escolares del Colegio Arenas Internacional. Contamos con delegaciones invitadas de más de seis centros de Lanzarote en torneos de atletismo, fútbol y natación adaptada.',
                'image' => 'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'center' => 'IES Agustín Espinosa',
                'title' => 'Encuentro Literario Joven con autores del archipiélago canario',
                'description' => 'El Club de Lectura del IES Agustín Espinosa organiza una tarde literaria en la biblioteca con autores contemporáneos nacidos en Canarias, promoviendo la lectura creativa de poesía, novela juvenil y dramaturgia isleña.',
                'image' => 'https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?auto=format&fit=crop&w=800&q=80'
            ]
        ];

        foreach ($publications as $pub) {
            $center = EducationalCenter::where('name', $pub['center'])->first();
            
            if (!$center) {
                continue;
            }

            Publication::updateOrCreate(
                [
                    'title' => $pub['title'],
                    'educational_center_id' => $center->id
                ],
                [
                    'description' => $pub['description'],
                    'image' => $pub['image'],
                    'created_at' => now()->subDays(rand(1, 15)),
                    'updated_at' => now()
                ]
            );
        }

        echo "✅ " . count($publications) . " publicaciones creadas.\n";
    }
}
