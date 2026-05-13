<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EducationalCenter;
use Illuminate\Database\Seeder;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        echo "📅 Creando 30 eventos variados...\n";

        $now = now();

        $events = [
            // === GENERALES (aparecen en centros específicos) ===
            ['title' => 'Jornada de Puertas Abiertas',           'center' => 'CIFP Zonzamas',     'date' => $now->copy()->addDays(15),  'start' => '09:00', 'end' => '14:00', 'location' => 'Salón de Actos'],
            ['title' => 'Día de la Paz',                         'center' => 'IES Agustín Espinosa', 'date' => $now->copy()->setMonth(1)->setDay(30)->addYear(1), 'start' => '10:00', 'end' => '13:00', 'location' => 'Patio Principal'],
            ['title' => 'Día de Canarias',                       'center' => 'CEO Argana',        'date' => $now->copy()->setMonth(5)->setDay(30)->addYear(1), 'start' => '09:00', 'end' => '14:00', 'location' => 'Instalaciones del Centro'],
            ['title' => 'Carnaval',                              'center' => 'CEIP Adolfo Topham', 'date' => $now->copy()->setMonth(2)->setDay(20)->addYear(1), 'start' => '10:00', 'end' => '14:00', 'location' => 'Salón de Actos'],
            ['title' => 'Día del Libro',                         'center' => 'IES Blas Cabrera Felipe', 'date' => $now->copy()->setMonth(4)->setDay(23)->addYear(1), 'start' => '10:00', 'end' => '13:30', 'location' => 'Biblioteca'],
            ['title' => 'Mercadillo Solidario',                  'center' => 'IES Las Salinas',   'date' => $now->copy()->addDays(60), 'start' => '10:00', 'end' => '17:00', 'location' => 'Hall Principal'],
            ['title' => 'Fiesta de Fin de Curso',                'center' => 'CEIP Titerroy',     'date' => $now->copy()->setMonth(6)->setDay(20)->addYear(1), 'start' => '11:00', 'end' => '15:00', 'location' => 'Salón de Actos'],
            ['title' => 'Día de la Convivencia',                 'center' => 'IES San Bartolomé', 'date' => $now->copy()->addDays(45), 'start' => '09:00', 'end' => '16:00', 'location' => 'Canchas Deportivas'],
            ['title' => 'Día Internacional de la Mujer',         'center' => 'IES César Manrique','date' => $now->copy()->setMonth(3)->setDay(8)->addYear(1), 'start' => '09:30', 'end' => '13:00', 'location' => 'Salón de Actos'],
            ['title' => 'Día de la Tierra',                      'center' => 'CEIP Playa Honda',  'date' => $now->copy()->setMonth(4)->setDay(22)->addYear(1), 'start' => '09:00', 'end' => '13:00', 'location' => 'Aula de la Naturaleza'],

            // === CIFP ZONZAMAS (eventos de FP) ===
            ['title' => 'Feria de la FP',                        'center' => 'CIFP Zonzamas',     'date' => $now->copy()->addDays(30), 'start' => '09:00', 'end' => '18:00', 'location' => 'Talleres del Centro'],
            ['title' => 'Hackathon Web 2026',                    'center' => 'CIFP Zonzamas',     'date' => $now->copy()->addDays(75), 'start' => '08:00', 'end' => '20:00', 'location' => 'Aula de Informática'],
            ['title' => 'Concurso de Cocina Creativa',           'center' => 'CIFP Zonzamas',     'date' => $now->copy()->addDays(50), 'start' => '08:00', 'end' => '15:00', 'location' => 'Cocina Industrial'],
            ['title' => 'Jornadas de Empleabilidad',             'center' => 'CIFP Zonzamas',     'date' => $now->copy()->addDays(90), 'start' => '09:00', 'end' => '14:00', 'location' => 'Salón de Grados'],
            ['title' => 'Muestra de Jardinería y Paisajismo',    'center' => 'CIFP Zonzamas',     'date' => $now->copy()->addDays(40), 'start' => '10:00', 'end' => '17:00', 'location' => 'Invernadero'],

            // === IES (secundaria) ===
            ['title' => 'Semana de la Ciencia',                  'center' => 'IES Las Maretas',   'date' => $now->copy()->addDays(35), 'start' => '09:00', 'end' => '14:00', 'location' => 'Laboratorios'],
            ['title' => 'Olimpiadas Deportivas',                 'center' => 'IES Tías',          'date' => $now->copy()->addDays(55), 'start' => '08:30', 'end' => '15:00', 'location' => 'Instalaciones Deportivas'],
            ['title' => 'Concurso de Debate',                    'center' => 'IES Yaiza',         'date' => $now->copy()->addDays(25), 'start' => '10:00', 'end' => '13:00', 'location' => 'Salón de Actos'],
            ['title' => 'Charla Prevención Acoso Escolar',       'center' => 'IES Tinajo',        'date' => $now->copy()->addDays(20), 'start' => '11:00', 'end' => '13:00', 'location' => 'Salón de Actos'],
            ['title' => 'Viaje de Fin de Curso',                 'center' => 'IES Haría',         'date' => $now->copy()->setMonth(6)->setDay(5)->addYear(1), 'start' => '06:00', 'end' => '20:00', 'location' => 'Fuera del Centro'],

            // === CEIP (primaria) ===
            ['title' => 'Festival de Navidad',                   'center' => 'CEIP Antonio Zerolo','date' => $now->copy()->setMonth(12)->setDay(20)->addYear(1), 'start' => '10:00', 'end' => '13:00', 'location' => 'Salón de Actos'],
            ['title' => 'Semana de la Fruta',                    'center' => 'CEIP Benito Méndez Tarajano', 'date' => $now->copy()->addDays(20), 'start' => '09:30', 'end' => '12:30', 'location' => 'Comedor'],
            ['title' => 'Excursión Fin de Curso',                'center' => 'CEIP Nieves Toledo','date' => $now->copy()->setMonth(6)->setDay(10)->addYear(1), 'start' => '08:00', 'end' => '17:00', 'location' => 'Timanfaya'],
            ['title' => 'Taller de Reciclaje',                   'center' => 'CEIP Ajei',         'date' => $now->copy()->addDays(30), 'start' => '10:00', 'end' => '12:30', 'location' => 'Aula de Plástica'],
            ['title' => 'Día de la Familia',                     'center' => 'CEIP Los Geranios', 'date' => $now->copy()->addDays(50), 'start' => '10:00', 'end' => '14:00', 'location' => 'Todo el Centro'],

            // === PRIVADOS / CONCERTADOS ===
            ['title' => 'Feria de Emprendimiento',               'center' => 'Arenas Internacional', 'date' => $now->copy()->addDays(40), 'start' => '09:00', 'end' => '14:00', 'location' => 'Salón de Actos'],
            ['title' => 'Cultural Week',                         'center' => 'British School of Lanzarote', 'date' => $now->copy()->addDays(60), 'start' => '09:00', 'end' => '15:00', 'location' => 'Instalaciones del Centro'],

            // === UNIVERSIDAD ===
            ['title' => 'Jornadas de Investigación',             'center' => 'ULPGC Lanzarote',   'date' => $now->copy()->addDays(45), 'start' => '09:00', 'end' => '18:00', 'location' => 'Aula Magna'],
            ['title' => 'Feria de Empleo',                       'center' => 'ULPGC Lanzarote',   'date' => $now->copy()->addDays(80), 'start' => '10:00', 'end' => '17:00', 'location' => 'Hall de la Sede'],
        ];

        foreach ($events as $ev) {
            $center = EducationalCenter::where('name', $ev['center'])->first();
            if (!$center) continue;
            if (Event::where('title', $ev['title'])->where('educational_center_id', $center->id)->exists()) continue;

            Event::create([
                'title' => $ev['title'],
                'description' => $this->getDescription($ev['title']),
                'location' => $ev['location'],
                'date' => $ev['date'],
                'start_time' => $ev['start'] . ':00',
                'end_time' => $ev['end'] . ':00',
                'educational_center_id' => $center->id,
            ]);
        }

        echo "✅ 30 eventos creados.\n";
    }

    private function getDescription(string $title): string
    {
        $descriptions = [
            'Jornada de Puertas Abiertas' => 'Ven a conocer nuestras instalaciones, talleres y oferta educativa. Habrá demostraciones en vivo, charlas informativas y visitas guiadas.',
            'Día de la Paz' => 'Celebración escolar con lectura de manifiestos, suelta de globos blancos y actividades por la convivencia y la no violencia.',
            'Día de Canarias' => 'Jornada festiva con música tradicional, exposición de artesanía, gastronomía típica canaria y talleres de baile regional.',
            'Carnaval' => 'Fiesta de disfraces con concurso, actuaciones de murgas y comparsas del centro. Participación de toda la comunidad educativa.',
            'Día del Libro' => 'Intercambio de libros, cuentacuentos, concurso de relatos cortos y encuentro con autores locales.',
            'Mercadillo Solidario' => 'Mercadillo benéfico organizado por el alumnado. Los fondos se destinan a una ONG local. Venta de alimentos, artesanía y ropa.',
            'Fiesta de Fin de Curso' => 'Acto de clausura con entrega de notas, actuaciones del alumnado, reconocimientos y fiesta de despedida.',
            'Día de la Convivencia' => 'Jornada de actividades deportivas, juegos cooperativos y comida compartida entre alumnos, profesores y familias.',
            'Día Internacional de la Mujer' => 'Charlas de mujeres referentes, talleres de sensibilización y actividades para visibilizar el papel de la mujer en la sociedad.',
            'Día de la Tierra' => 'Talleres de reciclaje, limpieza del entorno, charla sobre cambio climático y exposición de proyectos ecológicos.',
            'Feria de la FP' => 'Exposición de proyectos del alumnado de todos los ciclos formativos con demostraciones en vivo de cada familia profesional.',
            'Hackathon Web 2026' => '24 horas para desarrollar una aplicación web completa desde cero. Equipos de 3-4 personas. Premios para los ganadores.',
            'Concurso de Cocina Creativa' => 'Alumnos de cocina compiten elaborando el mejor plato con ingredientes locales. Jurado de chefs de la isla.',
            'Jornadas de Empleabilidad' => 'Empresas del sector realizan talleres, entrevistas simuladas y explican las salidas profesionales de cada especialidad.',
            'Muestra de Jardinería y Paisajismo' => 'Exposición de trabajos de jardinería, composiciones florales y proyectos de paisajismo del alumnado.',
            'Semana de la Ciencia' => 'Experimentos, talleres científicos y charlas divulgativas preparados por el alumnado de ciencias para todo el centro.',
            'Olimpiadas Deportivas' => 'Competiciones interclases de fútbol, baloncesto, voleibol, atletismo y juegos tradicionales canarios.',
            'Concurso de Debate' => 'Torneo de debate entre equipos de diferentes cursos sobre temas de actualidad. El equipo ganador representa al centro.',
            'Charla Prevención Acoso Escolar' => 'Taller de prevención del acoso y ciberacoso con dinámicas de grupo y pautas para identificar y actuar ante el bullying.',
            'Viaje de Fin de Curso' => 'Excursión cultural de varios días a lugares emblemáticos de la isla. Actividades de convivencia y despedida del curso.',
            'Festival de Navidad' => 'Actuaciones navideñas con villancicos, obras de teatro, poesías y la visita de SSMM los Reyes Magos.',
            'Semana de la Fruta' => 'Degustaciones, juegos y actividades para fomentar hábitos saludables de alimentación entre los más pequeños.',
            'Excursión Fin de Curso' => 'Visita al Parque Nacional de Timanfaya, Jardín de Cactus o Cueva de los Verdes. Jornada de convivencia en la naturaleza.',
            'Taller de Reciclaje' => 'Construimos juguetes con material reciclado y aprendemos la importancia del cuidado del medioambiente.',
            'Día de la Familia' => 'Jornada de puertas abiertas para las familias con talleres intergeneracionales, mercadillo y actividades compartidas.',
            'Feria de Emprendimiento' => 'Alumnos presentan sus proyectos empresariales a inversores locales. Concursos de ideas y networking.',
            'Cultural Week' => 'Semana cultural con actividades en inglés: teatro, música, talleres de cocina internacional y juegos lingüísticos.',
            'Jornadas de Investigación' => 'Presentación de trabajos de investigación del alumnado universitario con conferencias de profesores invitados.',
            'Feria de Empleo' => 'Encuentro con empresas de la isla con talleres de currículum, entrevistas y oportunidades de inserción laboral.',
        ];

        return $descriptions[$title] ?? 'Evento educativo organizado por el centro. Próximamente más información.';
    }
}
