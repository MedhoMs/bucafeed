<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EducationalCenter;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Tag;
use App\Models\Group;
use App\Models\Question;
use App\Models\Student;
use App\Models\Event;
use App\Models\Answer;
use Illuminate\Support\Facades\Hash;

class SchoolOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        echo "🏢 Reconstruyendo Universos Académicos (Lanzarote Realist)...\n";

        // 1. CREACIÓN DE MATERIAS (TAGS)
        echo "🏷️ Creando Materias...\n";
        $subjects = [
            'General' => ['Matemáticas', 'Lengua Castellana', 'Inglés', 'Educación Física', 'Ciencias Naturales', 'Ciencias Sociales', 'Plástica', 'Música'],
            'ESO' => ['Física y Química', 'Geografía e Historia', 'Biología y Geología', 'Tecnología', 'Valores Éticos'],
            'Bachillerato' => ['Filosofía', 'Dibujo Técnico', 'Economía', 'Historia de España'],
            'Informática' => ['Programación', 'Bases de Datos', 'Despliegue Web', 'Entornos de Desarrollo', 'Sistemas Informáticos', 'Redes Locales', 'Seguridad Informática'],
            'Cocina' => ['Oferta gastronómica', 'Preelaboración y conservación de alimentos', 'Técnicas culinarias', 'Repostería', 'Seguridad e higiene en la manipulación de alimentos', 'Gestión de la producción'],
            'Jardinería' => ['Fundamentos agronómicos', 'Taller y equipos', 'Infraestructuras e instalaciones hortícolas', 'Principios de sanidad vegetal', 'Composiciones florales'],
            'Sanidad' => ['Operaciones administrativas sanitarias', 'Técnicas básicas de enfermería', 'Higiene del medio hospitalario', 'Promoción de la salud'],
            'Administración' => ['Gestión contable', 'Ofimática', 'Comunicación empresarial', 'Recursos humanos', 'Contabilidad y fiscalidad'],
            'Comercio' => ['Marketing digital', 'Gestión de compras', 'Ventas', 'Logística', 'Atención al cliente'],
            'Imagen Personal' => ['Peluquería', 'Maquillaje', 'Estética', 'Cosmética', 'Imagen corporal'],
            'Transporte' => ['Mecánica', 'Electromecánica', 'Carrocería', 'Motores', 'Sistemas de seguridad'],
            'Electricidad' => ['Instalaciones eléctricas', 'Automatismos', 'Electrónica', 'Telecomunicaciones'],
            'Energía' => ['Energías renovables', 'Eficiencia energética', 'Solares', 'Eólicas']
        ];

        $tags = [];
        foreach ($subjects as $cat => $names) {
            foreach ($names as $name) {
                $tags[$name] = Tag::updateOrCreate(['name' => $name]);
            }
        }
        $tags['FOL'] = Tag::updateOrCreate(['name' => 'Formación y Orientación Laboral']);

        // 2. CREACIÓN DE CICLOS POR NIVELES
        echo "📚 Creando Ciclos...\n";
        
        // Primaria y Secundaria
        $primaryCycles = [];
        for ($i = 1; $i <= 6; $i++) {
            $cycle = Cycle::updateOrCreate(['name' => "{$i}º Primaria"], ['area' => 'Educación Primaria', 'level' => 'Primaria']);
            $cycle->tags()->sync(Tag::whereIn('name', $subjects['General'])->pluck('id'));
            $primaryCycles[] = $cycle;
        }

        $allEsoSubjects = array_merge($subjects['General'], $subjects['ESO']);
        $esoCycles = [];
        for ($i = 1; $i <= 4; $i++) {
            $cycle = Cycle::updateOrCreate(['name' => "{$i}º ESO"], ['area' => 'ESO', 'level' => 'Secundaria']);
            $cycle->tags()->sync(Tag::whereIn('name', $allEsoSubjects)->pluck('id'));
            $esoCycles[] = $cycle;
        }

        $allBachSubjects = array_merge($subjects['General'], $subjects['Bachillerato']);
        $bachCycles = [];
        foreach (['1º Bachillerato', '2º Bachillerato'] as $name) {
            $cycle = Cycle::updateOrCreate(['name' => $name], ['area' => 'Bachillerato', 'level' => 'Secundaria']);
            $cycle->tags()->sync(Tag::whereIn('name', $allBachSubjects)->pluck('id'));
            $bachCycles[] = $cycle;
        }

        // ZONZAMAS: Exactamente 44 ciclos formativos con sus respectivos MÓDULOS ESPECÍFICOS
        $fpData = [
            'Administración' => [
                'Servicios Administrativos' => ['level' => 'FP Básica', 'modules' => ['Técnicas administrativas básicas', 'Tratamiento informático de datos', 'Atención al cliente', 'Preparación de pedidos', 'Venta de productos']],
                'Gestión Administrativa' => ['level' => 'FP Medio', 'modules' => ['Comunicación empresarial y atención al cliente', 'Operaciones administrativas de compraventa', 'Empresa y administración', 'Técnica contable', 'Operaciones de recursos humanos']],
                'Administración y Finanzas' => ['level' => 'FP Superior', 'modules' => ['Gestión de documentación jurídica y empresarial', 'Recursos humanos y responsabilidad social corporativa', 'Ofimática y proceso de la información', 'Gestión financiera', 'Contabilidad y fiscalidad']],
                'Asistencia a la Dirección' => ['level' => 'FP Superior', 'modules' => ['Comunicación asistente a la dirección', 'Inglés', 'Organización de eventos empresariales', 'Gestión de sistemas de información', 'Protocolo empresarial']]
            ],
            'Informática' => [
                'Informática de Oficina' => ['level' => 'FP Básica', 'modules' => ['Montaje y mantenimiento de sistemas y componentes informáticos', 'Operaciones auxiliares para la configuración y la explotación', 'Ofimática y archivo de documentos', 'Instalación y mantenimiento de redes para transmisión de datos']],
                'Sistemas Microinformáticos y Redes (SMR)' => ['level' => 'FP Medio', 'modules' => ['Montaje y mantenimiento de equipo', 'Sistemas operativos monopuesto', 'Aplicaciones ofimáticas', 'Redes locales', 'Seguridad informática']],
                'Desarrollo de Aplicaciones Web (DAW)' => ['level' => 'FP Superior', 'modules' => ['Desarrollo web en entorno cliente', 'Desarrollo web en entorno servidor', 'Despliegue de aplicaciones web', 'Diseño de interfaces web', 'Sistemas informáticos', 'Bases de datos', 'Programación']],
                'Desarrollo de Aplicaciones Multiplataforma (DAM)' => ['level' => 'FP Superior', 'modules' => ['Programación multimedia y dispositivos móviles', 'Desarrollo de interfaces', 'Acceso a datos', 'Sistemas de gestión empresarial', 'Sistemas informáticos', 'Bases de datos', 'Programación']],
                'Administración de Sistemas Informáticos en Red (ASIR)' => ['level' => 'FP Superior', 'modules' => ['Implantación de sistemas operativos', 'Planificación y administración de redes', 'Fundamentos de hardware', 'Gestión de bases de datos', 'Lenguajes de marcas y sistemas de gestión de información', 'Administración de sistemas operativos', 'Seguridad y alta disponibilidad']]
            ],
            'Hostelería y Turismo' => [
                'Cocina y Restauración' => ['level' => 'FP Básica', 'modules' => ['Técnicas elementales de preelaboración', 'Procesos básicos de preparación de alimentos', 'Aprovisionamiento y conservación de materias primas', 'Servicio básico de restaurante y bar']],
                'Alojamiento y Lavandería' => ['level' => 'FP Básica', 'modules' => ['Puesta a punto de habitaciones', 'Lavado y lavado en seco', 'Planchado y embolsado', 'Atención al cliente en el entorno de lavandería']],
                'Cocina y Gastronomía' => ['level' => 'FP Medio', 'modules' => ['Oferta gastronómica', 'Preelaboración y conservación de alimentos', 'Técnicas culinarias', 'Repostería', 'Seguridad e higiene en la manipulación de alimentos']],
                'Servicios de Restauración' => ['level' => 'FP Medio', 'modules' => ['Operaciones básicas en bar-cafetería', 'Servicios en restaurante', 'Técnicas de comunicación en restauración', 'El vino y su servicio', 'Inglés profesional para servicios de restauración']],
                'Dirección de Cocina' => ['level' => 'FP Superior', 'modules' => ['Control del aprovisionamiento de materias primas', 'Procesos de preelaboración', 'Gestión de la producción en cocina', 'Gestión de la calidad y el medio ambiente', 'Recursos humanos y dirección de equipos en restauración']],
                'Dirección de Servicios de Restauración' => ['level' => 'FP Superior', 'modules' => ['Sumillería', 'Dirección y gestión de servicios de restaurante', 'Diseño y comercialización de ofertas de restauración', 'Gestión de la calidad', 'Recursos humanos y dirección de equipos']],
                'Agencias de Viajes y Gestión de Eventos' => ['level' => 'FP Superior', 'modules' => ['Estructura del mercado turístico', 'Protocolo y relaciones públicas', 'Marketing turístico', 'Dirección de entidades de intermediación turística', 'Venta de servicios turísticos']],
                'Gestión Integral de Alojamientos Turísticos' => ['level' => 'FP Superior', 'modules' => ['Recepción y reservas', 'Gestión de departamentos del área de alojamiento', 'Comercialización de eventos', 'Diseño e intervención en eventos', 'Gestión financiera y recursos humanos']]
            ],
            'Agraria' => [
                'Agrojardinería y Composiciones Florales' => ['level' => 'FP Básica', 'modules' => ['Actividades de riego y abonado', 'Mantenimiento de jardines', 'Preparación del terreno y plantación', 'Instalación de jardines', 'Mantenimiento de plantas en vivero']],
                'Jardinería y Floristería' => ['level' => 'FP Medio', 'modules' => ['Fundamentos agronómicos', 'Taller y equipos', 'Infraestructuras e instalaciones hortícolas', 'Principios de sanidad vegetal', 'Composiciones florales y plantas']],
                'Producción Agroecológica' => ['level' => 'FP Medio', 'modules' => ['Producción vegetal ecológica', 'Producción ganadera ecológica', 'Manejo de equipos fijos', 'Comercialización de productos ecológicos', 'Bases agronómicas']],
                'Paisajismo y Medio Rural' => ['level' => 'FP Superior', 'modules' => ['Botánica agronómica', 'Gestión y organización del vivero', 'Diseño de jardines y restauración del paisaje', 'Conservación de parques y jardines', 'Gestión del medio natural']]
            ],
            'Sanidad' => [
                'Cuidados Auxiliares de Enfermería' => ['level' => 'FP Medio', 'modules' => ['Operaciones administrativas y documentación sanitaria', 'Técnicas básicas de enfermería', 'Higiene del medio hospitalario y limpieza de material', 'Promoción de la salud y apoyo psicológico al paciente', 'Técnicas de ayuda odontológica/estomatológica']],
                'Farmacia y Parafarmacia' => ['level' => 'FP Medio', 'modules' => ['Disposición y venta de productos', 'Oficina de farmacia', 'Dispensación de productos farmacéuticos', 'Dispensación de productos parafarmacéuticos', 'Formulación magistral']],
                'Emergencias Sanitarias' => ['level' => 'FP Medio', 'modules' => ['Mantenimiento mecánico preventivo del vehículo', 'Logística sanitaria en emergencias', 'Dotación sanitaria', 'Atención sanitaria inicial en situaciones de emergencia', 'Evacuación y traslado de pacientes']],
                'Anatomía Patológica y Citodiagnóstico' => ['level' => 'FP Superior', 'modules' => ['Gestión de muestras biológicas', 'Técnicas generales de laboratorio', 'Biología molecular y citogenética', 'Fisiopatología general', 'Necropsias']],
                'Dietética' => ['level' => 'FP Superior', 'modules' => ['Organización y gestión del área de trabajo', 'Alimentación equilibrada', 'Dietoterapia', 'Control alimentario', 'Microbiología e higiene alimentaria']],
                'Higiene Bucodental' => ['level' => 'FP Superior', 'modules' => ['Recepción y logística en la clínica', 'Estudio de la cavidad oral', 'Exploración de la cavidad oral', 'Intervención bucodental', 'Educación para la salud oral']]
            ],
            'Comercio' => [
                'Servicios Comerciales' => ['level' => 'FP Básica', 'modules' => ['Técnicas básicas de merchandising', 'Atención básica al cliente', 'Tratamiento informático de datos', 'Preparación de pedidos y venta', 'Operaciones auxiliares de almacenaje']],
                'Actividades Comerciales' => ['level' => 'FP Medio', 'modules' => ['Marketing en la actividad comercial', 'Gestión de compras', 'Dinamización del punto de venta', 'Técnicas de almacén', 'Gestión de un pequeño comercio', 'Comercio electrónico']],
                'Marketing y Publicidad' => ['level' => 'FP Superior', 'modules' => ['Políticas de marketing', 'Investigación comercial', 'Diseño y elaboración de material de comunicación', 'Medios y soportes de comunicación', 'Trabajo de campo en la investigación comercial']],
                'Transporte y Logística' => ['level' => 'FP Superior', 'modules' => ['Transporte internacional de mercancías', 'Gestión económica y financiera de la empresa', 'Comercialización del transporte y la logística', 'Logística de almacenamiento', 'Logística de aprovisionamiento']]
            ],
            'Imagen Personal' => [
                'Peluquería y Estética' => ['level' => 'FP Básica', 'modules' => ['Lavado y cambios de forma del cabello', 'Cambio de color del cabello', 'Cuidados estéticos básicos de uñas', 'Maquillaje básico', 'Peinados y recogidos básicos']],
                'Estética y Belleza' => ['level' => 'FP Medio', 'modules' => ['Técnicas de higiene facial y corporal', 'Maquillaje', 'Estética de manos y pies', 'Depilación mecánica y decoloración del vello', 'Cosmetología para estética y belleza']],
                'Peluquería y Cosmética Capilar' => ['level' => 'FP Medio', 'modules' => ['Peinados y recogidos', 'Coloración capilar', 'Tratamientos capilares', 'Corte de cabello', 'Análisis capilar', 'Cosmética para peluquería']],
                'Estética Integral y Bienestar' => ['level' => 'FP Superior', 'modules' => ['Aparatología estética', 'Masaje estético', 'Tratamientos estéticos integrales', 'Dermoestética', 'Drenaje linfático estético', 'Micropigmentación']]
            ],
            'Transporte' => [
                'Mantenimiento de Vehículos' => ['level' => 'FP Básica', 'modules' => ['Mecanizado básico', 'Sistemas eléctricos de confort y seguridad', 'Mecánica de vehículos', 'Amovibles y preparación de superficies', 'Electricidad de vehículos']],
                'Electromecánica de Vehículos Automóviles' => ['level' => 'FP Medio', 'modules' => ['Motores', 'Sistemas auxiliares del motor', 'Circuitos de fluidos', 'Sistemas de transmisión y frenado', 'Sistemas de carga y arranque', 'Circuitos eléctricos auxiliares']],
                'Carrocería' => ['level' => 'FP Medio', 'modules' => ['Elementos amovibles', 'Elementos metálicos y sintéticos', 'Elementos fijos', 'Preparación de superficies', 'Embellecimiento de superficies']],
                'Automoción' => ['level' => 'FP Superior', 'modules' => ['Sistemas eléctricos y de seguridad', 'Sistemas de transmisión y frenado', 'Motores térmicos', 'Estructuras del vehículo', 'Gestión de talleres y mantenimiento', 'Tratamiento de superficies']]
            ],
            'Electricidad' => [
                'Electricidad y Electrónica' => ['level' => 'FP Básica', 'modules' => ['Operaciones auxiliares de montaje de instalaciones', 'Equipos eléctricos y electrónicos', 'Instalaciones eléctricas y domóticas', 'Instalaciones de telecomunicaciones', 'Redes de evacuación']],
                'Instalaciones Eléctricas y Automáticas' => ['level' => 'FP Medio', 'modules' => ['Automatismos industriales', 'Instalaciones domóticas', 'Electrónica', 'Máquinas eléctricas', 'Instalaciones de distribución', 'Infraestructuras comunes de telecomunicación']],
                'Sistemas Electrotécnicos y Automatizados' => ['level' => 'FP Superior', 'modules' => ['Procesos en instalaciones de infraestructuras comunes', 'Técnicas y procesos en instalaciones eléctricas', 'Desarrollo de redes eléctricas y centros de transformación', 'Configuración de instalaciones domóticas y automáticas', 'Sistemas y circuitos eléctricos']]
            ],
            'Energía' => [
                'Eficiencia Energética y Energía Solar Térmica' => ['level' => 'FP Superior', 'modules' => ['Promoción del uso eficiente de la energía', 'Evaluación de la eficiencia energética de los edificios', 'Certificación energética de edificios', 'Configuración de instalaciones solares térmicas', 'Gestión del montaje y mantenimiento de instalaciones solares térmicas']],
                'Energías Renovables' => ['level' => 'FP Superior', 'modules' => ['Sistemas de energías renovables', 'Configuración de instalaciones solares fotovoltaicas', 'Gestión del montaje de instalaciones fotovoltaicas', 'Gestión del montaje de parques eólicos', 'Mantenimiento de parques eólicos', 'Subestaciones eléctricas']]
            ]
        ];

        $fpCycles = [];
        foreach ($fpData as $family => $cycles) {
            foreach ($cycles as $name => $cycleData) {
                // Crear o actualizar ciclo
                $cycle = Cycle::updateOrCreate(
                    ['name' => $name], 
                    ['area' => $family, 'level' => $cycleData['level']]
                );

                // Obtener IDs de las etiquetas
                $tagIds = [];
                // Todos llevan FOL y EIE (si son medio o superior)
                $commonModules = ['Formación y Orientación Laboral (FOL)'];
                if (in_array($cycleData['level'], ['FP Medio', 'FP Superior'])) {
                    $commonModules[] = 'Empresa e Iniciativa Emprendedora (EIE)';
                    $commonModules[] = 'Inglés Técnico';
                }

                $allModules = array_merge($cycleData['modules'], $commonModules);

                foreach ($allModules as $moduleName) {
                    $tag = Tag::updateOrCreate(['name' => $moduleName]);
                    $tagIds[] = $tag->id;
                }

                // Sincronizar materias al ciclo
                $cycle->tags()->sync($tagIds);
                $fpCycles[] = $cycle;
            }
        }

        // 2.5 CREACIÓN DE CICLOS PARA DOJO DE KARATE
        echo "🥋 Creando Ciclos de Karate...\n";
        $karateSubjects = ['Katas Básicos', 'Katas Avanzados', 'Defensa Personal', 'Técnicas de Bloqueo', 'Filosofía Miyagi', 'Combate Libre / Kumite', 'Técnicas de Respiración', 'Equilibrio sobre botes'];
        $karateTags = [];
        foreach ($karateSubjects as $name) {
            $karateTags[] = Tag::updateOrCreate(['name' => $name])->id;
        }

        $karateCycles = [];
        foreach (['Cinturón Blanco', 'Cinturón Amarillo', 'Cinturón Verde', 'Cinturón Marrón', 'Cinturón Negro'] as $name) {
            $cycle = Cycle::updateOrCreate(['name' => $name], ['area' => 'Artes Marciales', 'level' => 'Deportiva']);
            $cycle->tags()->sync($karateTags);
            $karateCycles[] = $cycle;
        }

        // 3. CENTROS EDUCATIVOS
        echo "🏫 Configurando Centros y Administradores...\n";
        
        $centersData = [
            ['name' => 'CEIP Adolfo Topham', 'type' => 'PE', 'category' => 'CEIP', 'location' => 'Arrecife', 'cycles' => $primaryCycles],
            ['name' => 'IES Agustín Espinosa', 'type' => 'SE', 'category' => 'IES', 'location' => 'Arrecife', 'cycles' => array_merge($esoCycles, $bachCycles)],
            ['name' => 'IES Salinas', 'type' => 'SE', 'category' => 'IES', 'location' => 'Arrecife', 'cycles' => array_merge($esoCycles, $bachCycles)],
            ['name' => 'IES en Altavista', 'type' => 'SE', 'category' => 'IES', 'location' => 'Arrecife', 'cycles' => array_merge($esoCycles, $bachCycles)],
            ['name' => 'CIFP Zonzamas', 'type' => 'HE', 'category' => 'CIFP', 'location' => 'Arrecife', 'cycles' => array_values($fpCycles)],
            ['name' => 'Miyagi Do Karate', 'type' => 'SE', 'category' => 'IES', 'location' => 'Okinawa', 'cycles' => $karateCycles],
        ];

        $centersMap = [];

        foreach ($centersData as $idx => $data) {
            // Admin del Centro
            $adminCenter = User::updateOrCreate(['email' => "admin." . strtolower(str_replace(' ', '', $data['name'])) . "@telamonet.es"], [
                'name' => 'Admin', 'last_name' => $data['name'], 'password' => Hash::make('12345678'),
                'role' => 'EI', 'dni' => '0000000' . $idx . 'Z', 'institution_name' => $data['name'],
                'education_level' => 'Centro Educativo'
            ]);

            // Centro
            $center = EducationalCenter::updateOrCreate(['name' => $data['name']], [
                'location' => $data['location'], 'type' => $data['type'], 'category' => $data['category'], 'admin_user_id' => $adminCenter->id
            ]);
            
            $adminCenter->update(['educational_center_id' => $center->id]);

            // Vincular Ciclos al Centro
            $center->cycles()->sync(collect($data['cycles'])->pluck('id'));

            // 3. CREAR ALGUNOS GRUPOS Y USUARIOS DE EJEMPLO
            $this->seedDemoData($center, $data['cycles']);
            $centersMap[strtolower(str_replace(' ', '', $data['name']))] = $center;
        }
        
        $this->seedQuestionsAndEvents($centersMap);
    }

    private function seedDemoData($center, $cycles)
    {
        if (empty($cycles)) return;
        
        $targetCycle = $cycles[0]; // Por defecto el primero
        
        // Si estamos en Zonzamas, forzamos que el grupo sea de DAW para que las preguntas de programación tengan sentido
        if ($center->name === 'CIFP Zonzamas') {
            $dawCycle = collect($cycles)->first(fn($c) => str_contains(strtolower($c->name), 'daw') || str_contains(strtolower($c->name), 'desarrollo de aplicaciones web'));
            if ($dawCycle) $targetCycle = $dawCycle;
        }

        $nombres = ['Carlos', 'Lucía', 'Mateo', 'Valentina', 'Alejandro', 'Sofía', 'Hugo', 'Martina', 'Daniel', 'Julia', 'Pablo', 'Emma', 'Diego', 'Valeria', 'Alba', 'Mario'];
        $apellidos = ['García', 'Fernández', 'López', 'Martínez', 'González', 'Pérez', 'Rodríguez', 'Sánchez', 'Ramírez', 'Torres', 'Díaz', 'Muñoz', 'Romero', 'Alonso', 'Navarro', 'Ruiz'];
        
        if ($center->name === 'Miyagi Do Karate') {
            $nombres = ['Daniel', 'Johnny', 'Miguel', 'Samantha', 'Robby', 'Eli', 'Tory', 'Chozen', 'John', 'Terry', 'Carmen', 'Amanda'];
            $apellidos = ['LaRusso', 'Lawrence', 'Diaz', 'Keene', 'Moskowitz', 'Nichols', 'Toguchi', 'Kreese', 'Silver', 'Mills', 'Barnes', 'Payne'];
        }
        
        // Un profesor para el centro
        $profName = $nombres[array_rand($nombres)];
        $profSurname = $apellidos[array_rand($apellidos)];
        
        $teacher = User::updateOrCreate(['email' => strtolower($profName) . ".profe@" . strtolower(str_replace(' ', '', $center->name)) . ".es"], [
            'name' => $profName, 'last_name' => $profSurname, 'password' => Hash::make('12345678'),
            'role' => 'Teacher', 'educational_center_id' => $center->id, 'dni' => rand(10000000, 99999999) . 'T',
            'institution_name' => $center->name, 'education_level' => $center->type
        ]);

        // Un grupo
        $group = Group::updateOrCreate(['name' => "Grupo A - " . $targetCycle->name, 'educational_center_id' => $center->id], [
            'cycle_id' => $targetCycle->id, 'tutor_id' => $teacher->id
        ]);

        // Tres alumnos con nombres reales
        for ($i = 1; $i <= 3; $i++) {
            $stuName = $nombres[array_rand($nombres)];
            $stuSurname = $apellidos[array_rand($apellidos)];
            $safeDomain = strtolower(str_replace(' ', '', $center->name)) . ".es";
            
            // Forzamos la creación de algunos personajes icónicos
            if ($center->name === 'CIFP Zonzamas') {
                if ($i === 1) { $stuName = 'Mateo'; $stuSurname = 'García'; }
                if ($i === 2) { $stuName = 'Valentina'; $stuSurname = 'López'; }
                if ($i === 3) { $stuName = 'Daniel'; $stuSurname = 'Sánchez Martín'; }
            } elseif ($center->name === 'Miyagi Do Karate') {
                if ($i === 1) { $stuName = 'Daniel'; $stuSurname = 'LaRusso'; }
                if ($i === 2) { $stuName = 'Johnny'; $stuSurname = 'Lawrence'; }
                if ($i === 3) { $stuName = 'Miguel'; $stuSurname = 'Diaz'; }
            }

            $emailName = strtolower($stuName);
            $emailSurname = str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['', 'a', 'e', 'i', 'o', 'u'], strtolower($stuSurname));

            $user = User::updateOrCreate(['email' => "$emailName.$emailSurname@$safeDomain"], [
                'name' => $stuName, 'last_name' => $stuSurname, 'password' => Hash::make('12345678'),
                'role' => 'Student', 'educational_center_id' => $center->id, 'dni' => rand(10000000, 99999999) . 'S',
                'institution_name' => $center->name, 'education_level' => $center->type
            ]);
            $group->students()->syncWithoutDetaching([$user->id]);
        }
    }

    private function seedQuestionsAndEvents($centers)
    {
        $mateo = User::where('email', 'mateo.garcia@cifpzonzamas.es')->first();
        $valentina = User::where('email', 'valentina.lopez@cifpzonzamas.es')->first();
        $profe = User::where('role', 'Teacher')->where('educational_center_id', $centers['cifpzonzamas']->id ?? null)->first();

        // Preguntas y Respuestas
        if ($mateo) {
            $q1 = Question::updateOrCreate(['title' => '¿Cómo se alinea verticalmente un div sin Flexbox?'], [
                'content' => 'Me estoy volviendo loco en clase de Diseño de Interfaces Web. El profesor nos ha pedido centrar un div dentro de otro, pero sin usar ni Flexbox ni CSS Grid. ¿Alguien se acuerda de cómo se hacía con position absolute?',
                'user_id' => $mateo->id,
            ]);
            $tagProg = Tag::where('name', 'Diseño de interfaces web')->first() ?? Tag::where('name', 'Programación')->first();
            if ($tagProg) $q1->tags()->sync([$tagProg->id]);
            
            if ($valentina) {
                \App\Models\Answer::updateOrCreate(
                    ['question_id' => $q1->id, 'user_id' => $valentina->id],
                    ['content' => '¡Hola Mateo! Tienes que poner el contenedor padre con `position: relative`. Luego al hijo le pones `position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);`. ¡Espero que te sirva!']
                );
            }
        }

        if ($valentina) {
            $q2 = Question::updateOrCreate(['title' => 'Duda existencial: Diferencia entre LEFT JOIN y INNER JOIN en MySQL'], [
                'content' => 'El viernes en Gestión de Bases de Datos explicaron los JOINS pero me quedé dormida. Entiendo el INNER JOIN que te devuelve las filas que coinciden en ambas tablas. Pero, ¿qué hace exactamente el LEFT JOIN y en qué caso práctico se usaría?',
                'user_id' => $valentina->id,
            ]);
            $tagBd = Tag::where('name', 'Bases de datos')->first() ?? Tag::where('name', 'Bases de Datos')->first();
            if ($tagBd) $q2->tags()->sync([$tagBd->id]);
            
            if ($profe) {
                \App\Models\Answer::updateOrCreate(
                    ['question_id' => $q2->id, 'user_id' => $profe->id],
                    ['content' => 'Valentina, te lo explico con un ejemplo de clase: Imagina que quieres listar TODOS los alumnos (tabla de la IZQUIERDA) y sus notas (tabla de la DERECHA). Con un INNER JOIN, si un alumno aún no tiene notas subidas, no aparecerá en el listado final. Con un LEFT JOIN, saldrán TODOS los alumnos, y los que no tengan notas saldrán con `NULL` en esa columna. ¡A estudiar!']
                );
            }
        }

        // Más preguntas dinámicas para llenar el backend
        $allStudents = User::where('role', 'Student')->with('groupsAsStudent.cycle.tags')->inRandomOrder()->limit(10)->get();
        $allTeachers = User::where('role', 'Teacher')->inRandomOrder()->limit(5)->get();
        
        $titulosGenericos = [
            'Duda con la práctica de {materia}',
            '¿Alguien tiene apuntes del último tema de {materia}?',
            'Problema resolviendo el ejercicio 4 de {materia}',
            '¿Qué entra en el examen de {materia}?',
            'Me he atascado con el proyecto de {materia}',
            'Duda conceptual rápida sobre {materia}',
            '¿Cómo se hacía lo que explicó el profe en {materia}?',
            'Recursos recomendados para estudiar {materia}'
        ];

        $descripciones = [
            'Tengo una duda con esto, no he podido solucionarlo y necesito entregar la práctica mñn. ¿Alguien sabe?',
            'He revisado toda la bibliografía y sigo sin entenderlo bien. Cualquier ayuda es bienvenida.',
            'Llevo 3 horas dándole vueltas a este problema y no doy con la tecla. ¿Una pista?',
            'Si alguien me lo puede explicar con palabras más sencillas me salvaría la vida. ¡Gracias!',
            'Estuve enfermo el día que dieron esto y los apuntes que me pasaron no están muy claros...',
            '¿Alguien que haya sacado buena nota en esto me puede echar un cable?'
        ];

        foreach ($allStudents as $student) {
            // Obtener etiquetas (materias) reales de ESTE estudiante
            $studentTags = collect();
            if ($student->groupsAsStudent) {
                $studentTags = $student->groupsAsStudent->filter(fn($g) => $g->cycle != null)->flatMap(fn($g) => $g->cycle->tags);
            }
            
            if ($studentTags->isEmpty()) continue; // Si no tiene materias asignadas, saltamos

            $tagSelection = $studentTags->random();
            $titleTemplate = $titulosGenericos[array_rand($titulosGenericos)];
            $finalTitle = str_replace('{materia}', $tagSelection->name, $titleTemplate);
            $finalContent = $descripciones[array_rand($descripciones)];

            $q = Question::updateOrCreate(['title' => $finalTitle], [
                'content' => $finalContent,
                'user_id' => $student->id,
            ]);
            
            // Asignar el tag real
            $q->tags()->sync([$tagSelection->id]);
            
            // Añadir respuesta aleatoria
            if (rand(0, 1) === 1 && $allTeachers->count() > 0) {
                \App\Models\Answer::updateOrCreate(
                    ['question_id' => $q->id, 'user_id' => $allTeachers->random()->id],
                    ['content' => '¡Hola! Revisa la documentación oficial y los apuntes subidos a la plataforma. Seguro que encuentras la solución paso a paso.']
                );
            } elseif ($allStudents->count() > 1) {
                \App\Models\Answer::updateOrCreate(
                    ['question_id' => $q->id, 'user_id' => $allStudents->where('id', '!=', $student->id)->random()->id],
                    ['content' => 'A mí me pasó lo mismo ayer. Dale una vuelta al planteamiento base y si no te sale, mañana te enseño cómo lo hice yo.']
                );
            }
        }

        // Eventos
        $zonzamas = $centers['cifpzonzamas'];
        Event::updateOrCreate(['title' => 'Jornada de Puertas Abiertas'], [
            'educational_center_id' => $zonzamas->id,
            'description' => 'Ven a conocer nuestras instalaciones y la oferta formativa técnica para el próximo curso.',
            'location' => 'Salón de Actos - Edificio A',
            'date' => now()->addDays(15),
            'start_time' => '09:00:00',
            'end_time' => '14:00:00',
            'target_role' => 'Student',
            'image' => 'https://images.unsplash.com/photo-1544531585-9847b68c8c86?q=80&w=1000'
        ]);

        Event::updateOrCreate(['title' => 'Taller de Ciberseguridad'], [
            'educational_center_id' => $zonzamas->id,
            'description' => 'Introducción práctica a la seguridad ofensiva y defensa en redes locales.',
            'location' => 'Aula de Informática 3',
            'date' => now()->addDays(20),
            'start_time' => '16:00:00',
            'end_time' => '19:00:00',
            'target_role' => 'Student',
            'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=1000'
        ]);

        $topham = $centers['ceipadolfotopham'];
        Event::updateOrCreate(['title' => 'Fiesta de Fin de Curso'], [
            'educational_center_id' => $topham->id,
            'description' => 'Celebración con música y juegos para todos los alumnos de primaria.',
            'location' => 'Patio Principal',
            'date' => now()->addDays(30),
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'target_role' => null,
            'image' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=1000'
        ]);

        if (isset($centers['miyagidokarate'])) {
            $dojo = $centers['miyagidokarate'];
            
            // Crear el tutor Splinter-Sensei
            User::updateOrCreate(['email' => 'splinter-sensei@miyagidokarate.com'], [
                'name' => 'Senséi', 'last_name' => 'Splinter', 'password' => Hash::make('12345678'),
                'role' => 'Teacher', 'educational_center_id' => $dojo->id, 'dni' => '000NINJAS',
                'institution_name' => $dojo->name, 'education_level' => $dojo->type
            ]);

            Event::updateOrCreate(['title' => 'Artes Marciales del Valle'], [
                'educational_center_id' => $dojo->id,
                'description' => 'Demuestra tus habilidades de Karate. Dar cera, pulir cera.',
                'location' => 'Tatami Principal',
                'date' => now()->addDays(5),
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'target_role' => 'Student',
                'image' => 'https://images.unsplash.com/photo-1552072092-7f9b8d63efcb?q=80&w=1000&auto=format&fit=crop'
            ]);
        }

        // 5. PREGUNTAS REQUERIDAS POR EL USUARIO
        echo "🙋 Creando Preguntas Específicas...\n";
        $jason = User::where('email', 'jasoncsotto16@gmail.com')->first();
        $daniel = User::where('email', 'daniel.sanchezmartin@cifpzonzamas.es')->first();
        $splinter = User::where('email', 'splinter-sensei@miyagidokarate.com')->first();
        $tagProg = Tag::where('name', 'Programación')->first();

        if ($jason) {
            $qReact = Question::updateOrCreate(['title' => '¿Qué es React?'], [
                'content' => 'Tengo curiosidad por saber qué es React y por qué todo el mundo habla de ello.',
                'user_id' => $jason->id,
            ]);
            if ($tagProg) $qReact->tags()->sync([$tagProg->id]);

            if ($daniel) {
                Answer::updateOrCreate(
                    ['question_id' => $qReact->id, 'user_id' => $daniel->id],
                    ['content' => 'una dependencia del vue']
                );
            }
        }

        if ($splinter) {
            Question::updateOrCreate(['title' => '¿Tienes mascotas?'], [
                'content' => 'Una pregunta curiosa para mis alumnos: ¿alguno de vosotros tiene mascotas en casa?',
                'user_id' => $splinter->id,
            ]);
        }
    }
}
