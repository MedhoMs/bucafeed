<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ForumQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        echo "💬 Creando preguntas y respuestas del foro...\n";

        $students = User::whereIn('role', ['Student', 'student', 'Alumno'])->get();
        $teachers = User::whereIn('role', ['Teacher', 'teacher', 'Profesor'])->get();
        $allUsers = User::all();

        if ($students->isEmpty()) {
            echo "No hay estudiantes.\n";
            return;
        }

        $tags = Tag::pluck('id', 'name');

        $questions = [
            // === PROGRAMACIÓN ===
            ['title' => 'Duda con los bucles for en JavaScript',
             'content' => 'Estoy haciendo un ejercicio: "Suma los números del 1 al 100 usando un bucle for". He escrito:\n\nlet suma = 0;\nfor (let i = 1; i <= 100; i++) {\n  suma += i;\n}\nconsole.log(suma);\n\n¿Está bien? ¿Hay otra forma más eficiente?',
             'tags' => ['Programación'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Tu código es correcto y funciona perfectamente. Otra forma más eficiente sería usar la fórmula matemática: n*(n+1)/2. Para n=100 sería 100*101/2 = 5050. ¡Así evitas el bucle por completo!'],
                 ['role' => 'student', 'content' => 'Yo lo hice igual que tú y el profe me dijo que estaba bien. También puedes usar reduce: Array.from({length:100},(_,i)=>i+1).reduce((a,b)=>a+b,0)'],
             ]],
            ['title' => '¿Cuál es la diferencia entre == y === en PHP?',
             'content' => 'No termino de entender cuándo usar == y cuándo === en PHP. ¿Alguien me explica con ejemplos?',
             'tags' => ['Programación'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Buena pregunta. == compara solo el VALOR, mientras que === compara el VALOR y el TIPO. Ejemplo: "5" == 5 es true, pero "5" === 5 es false porque uno es string y el otro integer. Usa === siempre que puedas para evitar errores inesperados.'],
                 ['role' => 'student', 'content' => 'Regla fácil: si usas === nunca te llevas sorpresas. Con ==, "0" == false es true y eso da errores raros.'],
             ]],
            ['title' => 'No me funciona el JOIN en SQL',
             'content' => 'Quiero mostrar todos los usuarios aunque no tengan pedidos:\n\nSELECT * FROM usuarios JOIN pedidos ON usuarios.id = pedidos.usuario_id\n\nPero solo veo los que tienen pedidos. ¿Qué falla?',
             'tags' => ['Bases de Datos', 'Programación'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Estás usando INNER JOIN, que solo muestra registros que existen en ambas tablas. Usa LEFT JOIN para mostrar todos los usuarios aunque no tengan pedidos:\n\nSELECT * FROM usuarios LEFT JOIN pedidos ON usuarios.id = pedidos.usuario_id'],
                 ['role' => 'student', 'content' => 'Me pasó lo mismo. LEFT JOIN es la solución. Los que no tengan pedidos saldrán con NULL en las columnas de pedidos.'],
             ]],
            ['title' => 'Duda con funciones flecha vs function en JS',
             'content' => '¿Cuándo uso () => {} y cuándo uso function()? He visto que a veces dan error si cambio una por la otra.',
             'tags' => ['Programación'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'La diferencia clave es el THIS. Las funciones flecha NO tienen su propio this, heredan el del contexto donde se crean. Las function() sí tienen su propio this. Usa flecha para callbacks y cuando quieras mantener el this de afuera.'],
                 ['role' => 'student', 'content' => 'Además las arrow functions no pueden usarse como constructores (con new) y no tienen arguments. Por lo demás son casi iguales.'],
             ]],
            ['title' => '¿Cómo se hace un fetch en JavaScript?',
             'content' => 'En clase de Diseño de Interfaces nos pidieron consumir una API con fetch. No me queda claro lo de las promesas. ¿Alguien tiene un ejemplo sencillo?',
             'tags' => ['Programación', 'Diseño de Interfaces Web'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Ejemplo básico:\n\nfetch("https://api.ejemplo.com/datos")\n  .then(res => res.json())\n  .then(data => console.log(data))\n  .catch(err => console.error(err));\n\nCon async/await es más legible:\n\nasync function obtenerDatos() {\n  const res = await fetch("url");\n  const data = await res.json();\n  return data;\n}'],
                 ['role' => 'student', 'content' => 'Lo importante es que fetch devuelve una promesa, y tienes que esperar a que se resuelva con then() o async/await. Sin eso solo obtienes "Promise {<pending>}".'],
             ]],

            // === DISEÑO WEB ===
            ['title' => 'No consigo centrar un div con CSS',
             'content' => 'He probado margin:0 auto, text-align:center, pero no se centra. Uso Flexbox. ¿Alguien me da el código mágico para centrar algo horizontal y verticalmente?',
             'tags' => ['Diseño de Interfaces Web'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Con Flexbox es muy fácil:\n\n.contenedor {\n  display: flex;\n  justify-content: center;\n  align-items: center;\n}\n\nEl contenedor padre debe tener una altura definida. Si no, align-items no funciona porque no hay altura de referencia.'],
                 ['role' => 'student', 'content' => 'También con CSS Grid:\n.contenedor {\n  display: grid;\n  place-items: center;\n}\n¡Esto centra en las dos direcciones con una sola línea!'],
             ]],
            ['title' => 'Mi web no es responsive, ¿qué hago mal?',
             'content' => 'La página se ve bien en el ordenador pero en el móvil los textos se salen y las imágenes se enciman. ¿Cómo lo arreglo?',
             'tags' => ['Diseño de Interfaces Web', 'Lenguaje de Marcas'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Usa @media queries para adaptar el diseño:\n\n/* Móvil */\n@media (max-width: 768px) {\n  body { font-size: 16px; }\n  img { max-width: 100%; height: auto; }\n}\n\nAñade también: <meta name="viewport" content="width=device-width, initial-scale=1"> en el <head>.'],
                 ['role' => 'student', 'content' => 'Además usa unidades relativas (%, vw, vh, rem) en vez de px fijos. Y display: grid con auto-fit para las tarjetas.'],
             ]],
            ['title' => 'Duda con CSS Grid: fr vs %',
             'content' => '¿Cuál es la diferencia entre fracciones (1fr) y porcentajes en grid-template-columns?',
             'tags' => ['Diseño de Interfaces Web'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'fr reparte el espacio DISPONIBLE después de restar los tamaños fijos (px, etc). % se calcula sobre el ancho total del contenedor. Si tienes gap, padding o bordes, % puede desbordar mientras fr se ajusta automáticamente.'],
             ]],

            // === MATEMÁTICAS ===
            ['title' => '¿Cómo se resuelve una ecuación de segundo grado?',
             'content' => 'Problema: 2x² + 5x - 3 = 0. ¿Alguien me explica paso a paso la fórmula general?',
             'tags' => ['Matemáticas'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Fórmula general: x = [-b ± √(b² - 4ac)] / 2a\n\nPara 2x² + 5x - 3 = 0:\na=2, b=5, c=-3\n\nDiscriminante: b² - 4ac = 25 - 4·2·(-3) = 25 + 24 = 49\n√49 = 7\n\nx = [-5 ± 7] / 4\nx₁ = (-5+7)/4 = 2/4 = 0.5\nx₂ = (-5-7)/4 = -12/4 = -3'],
                 ['role' => 'student', 'content' => 'Truco: primero calcula siempre el discriminante (lo de dentro de la raíz). Si es negativo, no hay solución real. Si es cero, una solución. Si es positivo, dos soluciones.'],
             ]],
            ['title' => 'Derivadas: regla de la cadena',
             'content' => 'No entiendo cuándo aplicar la regla de la cadena. Por ejemplo: derivar f(x) = sen(3x² + 2).',
             'tags' => ['Matemáticas'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'La regla de la cadena se aplica cuando tienes una FUNCIÓN dentro de otra FUNCIÓN. f(x) = sen(3x²+2) es: sen(g(x)) donde g(x)=3x²+2.\n\nf\'(x) = cos(g(x)) · g\'(x) = cos(3x²+2) · 6x\n\n"Deriva la función externa, y multiplica por la derivada de la interna".'],
                 ['role' => 'student', 'content' => 'Regla mnemotécnica: "derivo lo de fuera, dejando lo de dentro igual, y multiplico por la derivada de lo de dentro".'],
             ]],
            ['title' => 'Duda con logaritmos: propiedades',
             'content' => 'Me lío con las propiedades. ¿Alguien tiene un truco para acordarse?',
             'tags' => ['Matemáticas'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Tres reglas básicas:\n1. log(a·b) = log(a) + log(b) ← Multiplicación suma\n2. log(a/b) = log(a) - log(b) ← División resta\n3. log(aⁿ) = n·log(a) ← Exponente baja multiplicando\n\nEjemplo: log(100x²) = log(100) + 2·log(x) = 2 + 2·log(x)'],
             ]],

            // === INGLÉS ===
            ['title' => 'Past Simple vs Present Perfect',
             'content' => 'I always confuse "I went" and "I have gone". When should I use each?',
             'tags' => ['Inglés'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Past Simple: acción terminada en un momento específico del pasado. "I went to London last year" (cuándo importa).\n\nPresent Perfect: acción que conecta pasado con presente. "I have gone to London" (experiencia, sin decir cuándo, o el resultado importa ahora).\n\nTruco: si puedes decir cuándo pasó → Past Simple. Si no → Present Perfect.'],
                 ['role' => 'student', 'content' => 'Fíjate en las palabras clave:\n- Past Simple: yesterday, last week, in 2020, ago\n- Present Perfect: ever, never, already, yet, just, since, for'],
             ]],
            ['title' => 'Diferencia entre "some" y "any"',
             'content' => 'Siempre me equivoco. ¿Regla fácil para saber cuándo usar cada uno?',
             'tags' => ['Inglés'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Regla simple: some en afirmativas, any en negativas y preguntas.\n\n- I have SOME friends. ✅\n- I don\'t have ANY friends. ✅\n- Do you have ANY friends? ✅\n\nExcepción: en preguntas donde OFRECES algo, usa some: "Would you like SOME coffee?"'],
             ]],
            ['title' => 'Phrasal verbs con give',
             'content' => 'Give up, give in, give away... ¿cuál es la diferencia exacta?',
             'tags' => ['Inglés'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Give up = rendirse / dejar un hábito ("I give up smoking").\nGive in = ceder tras resistencia ("I give in to pressure").\nGive away = regalar / revelar un secreto ("I gave away my old books").\nGive out = distribuir / agotarse ("The teacher gave out the exams").'],
             ]],

            // === SISTEMAS ===
            ['title' => '¿Cómo particiono un disco en Linux?',
             'content' => 'En clase nos pidieron instalar Ubuntu con particiones separadas para /, /home y swap. ¿Alguien tiene una guía?',
             'tags' => ['Sistemas Informáticos'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Con fdisk:\n1. sudo fdisk /dev/sda\n2. n → crear partición (p=primaria, número 1)\n3. Especifica tamaño (+20G para /, +10G para /home, +2G para swap)\n4. t → cambiar tipo (swap es tipo 82)\n5. w → escribir cambios\n\nLuego mkfs.ext4 para / y /home, mkswap para swap. ¡Siempre haz backup antes!'],
                 ['role' => 'student', 'content' => 'Si te da miedo fdisk, el instalador de Ubuntu tiene particionado guiado. Elige "Particionado manual" y te deja hacerlo visualmente.'],
             ]],
            ['title' => 'Duda con permisos chmod: 755 vs 644',
             'content' => 'No entiendo los permisos. ¿Qué significa chmod 755?',
             'tags' => ['Sistemas Informáticos', 'Despliegue de Aplicaciones'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Los números son: dueño-grupo-otros. Cada uno es suma de: 4=leer, 2=escribir, 1=ejecutar.\n\n755 = dueño(7=4+2+1: rwx), grupo(5=4+1: r-x), otros(5=4+1: r-x)\n644 = dueño(6=4+2: rw-), grupo(4: r--), otros(4: r--)\n\n755 para directorios y ejecutables, 644 para archivos normales.'],
             ]],

            // === CIENCIAS ===
            ['title' => 'Duda sobre la fotosíntesis',
             'content' => '¿En qué fase se libera el oxígeno? ¿Luminosa u oscura?',
             'tags' => ['Biología'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'El oxígeno se libera en la FASE LUMINOSA. Durante la fotólisis del agua (H₂O → 2H⁺ + 2e⁻ + ½O₂), las moléculas de agua se rompen y liberan oxígeno. La fase oscura (Ciclo de Calvin) usa el ATP y NADPH de la fase luminosa para fijar CO₂ en glucosa, pero no produce oxígeno.'],
                 ['role' => 'student', 'content' => 'Resumen: fase luminosa → produce O₂, ATP y NADPH. Fase oscura → usa ATP y NADPH para hacer glucosa.'],
             ]],
            ['title' => 'Ejercicio de cinemática',
             'content' => 'Un coche va a 20 m/s y frena con a = -2 m/s². ¿Cuánto tarda en pararse?',
             'tags' => ['Física'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Usamos v = v₀ + a·t. Cuando se para, v = 0.\n\n0 = 20 + (-2)·t\n2t = 20\nt = 10 segundos\n\nTe salía negativo porque quizás pusiste: 0 = 20 + 2·t (olvidando que la aceleración es negativa porque frena).'],
             ]],
            ['title' => '¿Cómo balancear Fe + O₂ → Fe₂O₃?',
             'content' => 'Me pierdo con los coeficientes. ¿Método paso a paso?',
             'tags' => ['Química'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Método de tanteo:\n1. Fe: 1 izq, 2 der → pon 2Fe\n2. O: 2 izq, 3 der → mínimo común múltiplo 6: 3O₂ (6 O) y 2Fe₂O₃ (6 O)\n3. Ajustamos Fe: 4Fe → 2Fe₂O₃\n\nResultado: 4Fe + 3O₂ → 2Fe₂O₃'],
             ]],

            // === HISTORIA / LENGUA ===
            ['title' => 'Causas de la Revolución Francesa',
             'content' => 'Necesito un resumen rápido de las causas. ¡Gracias!',
             'tags' => ['Historia'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Tres causas principales:\n1. ECONÓMICA: Crisis financiera por guerras y malas cosechas, el pueblo pasaba hambre mientras la nobleza no pagaba impuestos.\n2. SOCIAL: Desigualdad extremadel Antiguo Régimen (nobleza/clero vs tercer estado).\n3. POLÍTICA: Ideas ilustradas (Libertad, Igualdad, Fraternidad) y el despotismo de Luis XVI.'],
             ]],
            ['title' => 'Análisis sintáctico: oración compuesta',
             'content' => '"El profesor dijo que el examen será el viernes". ¿Es subordinada sustantiva o adjetiva?',
             'tags' => ['Lengua Castellana'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Es una SUBORDINADA SUSTANTIVA. "que el examen será el viernes" funciona como CD (complemento directo) del verbo "dijo". Puedes sustituirla por "eso": "El profesor dijo ESO". Las adjetivas complementan a un nombre y se pueden sustituir por un adjetivo.'],
             ]],
            ['title' => 'Diferencia entre el siglo XVIII y XIX en España',
             'content' => 'Se me mezclan la Guerra de Independencia, Fernando VII, la Constitución de 1812...',
             'tags' => ['Historia'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Línea rápida:\n- XVIII: Reinado de los Borbones (Felipe V, Carlos III). Despotismo Ilustrado.\n- 1808: Guerra de Independencia contra Napoleón.\n- 1812: Constitución de Cádiz (La Pepa), primera constitución española.\n- 1814-1833: Fernando VII (restaura absolutismo, década ominosa).\n- XIX en general: Luchas entre liberales y absolutistas, pérdida de colonias.'],
             ]],

            // === FP ESPECÍFICAS ===
            ['title' => 'Duda con Docker: ¿contenedor vs máquina virtual?',
             'content' => 'No entiendo la diferencia entre una VM y un contenedor Docker.',
             'tags' => ['Despliegue de Aplicaciones', 'Sistemas Informáticos'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Imagina que tu servidor es un edificio:\n- VM: cada apartamento tiene su propia cocina, baño, electricidad (Sistema Operativo completo). Ocupa mucho.\n- Docker: todos comparten la cocina central (kernel del host), cada uno solo tiene lo necesario para funcionar. Son mucho más ligeros y arrancan en segundos.'],
                 ['role' => 'student', 'content' => 'Ventajas de Docker: pesan MB (no GB), arrancan en segundos (no minutos), puedes tener decenas en un servidor. Desventaja: todos comparten el kernel del host, así que no puedes correr Windows en un host Linux.'],
             ]],
            ['title' => '¿Cómo se configura un VirtualHost en Apache?',
             'content' => 'Tengo un VPS y quiero tener dos dominios en el mismo servidor con contenido diferente.',
             'tags' => ['Despliegue de Aplicaciones'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'En /etc/apache2/sites-available/misitio.conf:\n\n<VirtualHost *:80>\n    ServerName dominio1.com\n    DocumentRoot /var/www/dominio1\n</VirtualHost>\n\n<VirtualHost *:80>\n    ServerName dominio2.com\n    DocumentRoot /var/www/dominio2\n</VirtualHost>\n\nLuego: sudo a2ensite misitio.conf && sudo systemctl reload apache2'],
             ]],
            ['title' => 'Git: conflicto al hacer merge',
             'content' => 'Hicimos git merge y salió "CONFLICT in index.html". ¿Cómo lo resuelvo?',
             'tags' => ['Entornos de Desarrollo', 'Programación'],
             'answers' => [
                 ['role' => 'teacher', 'content' => '1. Abre index.html y busca las marcas:\n<<<<<<< HEAD (tu código)\n=======\n>>>>>>> branch (código del otro)\n\n2. Decide qué código conservar (o combínalos manualmente).\n3. Borra las marcas <<<<, ====, >>>>.\n4. git add index.html && git commit.'],
             ]],
            ['title' => '¿Qué es Scrum y cómo se aplica?',
             'content' => 'No me queda claro cómo se aplica Scrum en un proyecto real. ¿Qué hace el Scrum Master?',
             'tags' => ['Entornos de Desarrollo'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Scrum es un marco ágil con roles (Product Owner, Scrum Master, Dev Team), eventos (Sprint Planning, Daily, Review, Retro) y artefactos (Product Backlog, Sprint Backlog).\n\nEl Scrum Master NO es un jefe. Es un facilitador que elimina impedimentos, asegura que se sigan las prácticas Scrum y protege al equipo de interrupciones externas.'],
                 ['role' => 'student', 'content' => 'En la práctica: divides el proyecto en Sprints de 2 semanas. Cada día reunión de 15 min (Daily). Al final enseñas lo que hiciste (Review) y mejoráis el proceso (Retro).'],
             ]],
            ['title' => 'Duda con MER: relación N:M',
             'content' => 'Un libro puede tener varios autores y un autor varios libros. ¿Cómo se representa en MySQL?',
             'tags' => ['Bases de Datos'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Es N:M (muchos a muchos). Necesitas una tabla intermedia:\n\nCREATE TABLE libros_autores (\n  libro_id INT,\n  autor_id INT,\n  PRIMARY KEY (libro_id, autor_id),\n  FOREIGN KEY (libro_id) REFERENCES libros(id),\n  FOREIGN KEY (autor_id) REFERENCES autores(id)\n);\n\nAsí cada libro puede tener muchos autores y cada autor muchos libros.'],
             ]],

            // === COCINA / HOSTELERÍA ===
            ['title' => '¿Cómo se hace un arroz caldoso de marisco?',
             'content' => 'Quiero hacer un arroz caldoso pero siempre se me queda seco o pasado. ¿Cuál es el truco para que quede en su punto?',
             'tags' => ['FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'El truco está en el caldo: usa un buen fumet de pescado y marisco (cuece las cabezas y espinas 30 min). La proporción es 3 partes de caldo por 1 de arroz. El arroz nunca se remueve después de echar el caldo. Cocina a fuego medio 18 minutos exactos y deja reposar 5 tapado.'],
                 ['role' => 'student', 'content' => 'Importante: el sofrito es la base. Pocha cebolla, ajo, tomate y pimiento verde primero. Luego añade el marisco y por último el arroz. ¡Y usa arroz bomba o senia, nunca redondo!'],
             ]],
            ['title' => 'Técnicas de cocina: diferencia entre pochar y sofreír',
             'content' => 'En cocina siempre dicen "pocha la cebolla" o "sofríe el ajo". ¿Es lo mismo?',
             'tags' => ['FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'No es lo mismo:\n\nPOCHAR: cocer en aceite a fuego lento, tapado, para que sude sin coger color. La cebolla pochada queda transparente y dulce.\n\nSOFREÍR: cocer en aceite a fuego medio-alto, sin tapar, para que coja color. El ajo sofrito queda dorado y suelta todo su aroma.\n\nCada técnica da un sabor diferente al plato.'],
             ]],
            ['title' => '¿Cuánto tiempo se cocina un huevo duro perfecto?',
             'content' => 'Siempre me sale o demasiado hecho (yema verde) o poco hecho. ¿El tiempo exacto?',
             'tags' => ['FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Huevo duro perfecto:\n1. Agua fría, huevo dentro, llevar a ebullición.\n2. Desde que HIERVE: 8 minutos exactos para huevo duro (yema cocida pero no verde).\n3. Sacar y meter en agua con hielo para cortar la cocción.\n\nSi quieres yema cremosa (mollet): 6 minutos. Yema líquida: 4 minutos.'],
             ]],

            // === DEPORTES / EDUCACIÓN FÍSICA ===
            ['title' => '¿Cómo mejorar mi resistencia corriendo?',
             'content' => 'Quiero empezar a correr pero me canso a los 5 minutos. ¿Hay algún método para ir mejorando poco a poco?',
             'tags' => ['Educación Física'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Método CaCo (Camina-Corre): intercala 1 min corriendo con 2 minutos andando. Repite 5 veces. Cada semana aumenta el tiempo corriendo y reduce el andando. En un mes puedes correr 20 minutos seguidos sin parar.'],
                 ['role' => 'student', 'content' => 'Importante: no salgas a correr en ayunas, hidrátate bien, y estírate después (nunca en frío). El calzado es clave para evitar lesiones.'],
             ]],
            ['title' => '¿Cuál es la diferencia entre stretching estático y dinámico?',
             'content' => 'En educación física a veces hacemos estiramientos antes y a veces después. No sé cuál es el orden correcto.',
             'tags' => ['Educación Física'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Dinámico ANTES del ejercicio (balanceos de piernas, círculos de brazos, torsiones de tronco). Prepara los músculos.\n\nEstático DESPUÉS del ejercicio (mantener posiciones 20-30 segundos). Ayuda a la recuperación y flexibilidad.\n\nNunca estires en frío ni hagas estático antes de deporte porque aumentas el riesgo de lesión.'],
             ]],

            // === ARTES ===
            ['title' => 'Diferencia entre pintura al óleo y acrílica',
             'content' => 'Quiero empezar a pintar y no sé qué técnica elegir. ¿Cuál es mejor para principiantes?',
             'tags' => ['Artes Plásticas'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Acrílica: se seca rápido (minutos), se diluye con agua, no huele, más barata. Ideal para principiantes.\nÓleo: tarda días en secar, permite correcciones y mezclas en el lienzo, necesitas disolventes. Más profesional pero más complicada.\n\nRecomendación: empieza con acrílicas y cuando domines la técnica, pásate al óleo.'],
             ]],
            ['title' => '¿Cómo dibujar una figura humana con proporciones correctas?',
             'content' => 'Intento dibujar personas pero siempre me salen las piernas demasiado cortas o la cabeza demasiado grande. ¿Hay alguna regla de proporciones?',
             'tags' => ['Artes Plásticas'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Regla de las 8 cabezas: un adulto mide aproximadamente 8 veces el tamaño de su cabeza.\n\n1: cabeza\n2: pecho\n3: caderas\n4: inicio muslos\n5: rodillas\n6: media pierna\n7-8: pies\n\nPractica dibujando "muñecos de palo" primero, luego ve añadiendo volumen.'],
             ]],

            // === ECONOMÍA / EMPRESA ===
            ['title' => '¿Qué es el IVA y cómo se calcula?',
             'content' => 'Estoy haciendo un trabajo de economía y no entiendo la diferencia entre IVA soportado y repercutido.',
             'tags' => ['Economía', 'FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'IVA REPERCUTIDO: el que tú cobras a tus clientes (lo añades en tus facturas).\nIVA SOPORTADO: el que tú pagas a tus proveedores (lo que te facturan).\n\nCada trimestre haces cuentas: Hacienda = IVA repercutido - IVA soportado.\nSi sale positivo → pagas. Si sale negativo → te devuelven.'],
             ]],
            ['title' => 'Diferencia entre autónomo y sociedad limitada',
             'content' => 'Quiero montar un negocio pequeño. ¿Qué me conviene más, ser autónomo o crear una SL?',
             'tags' => ['Economía', 'FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'AUTÓNOMO: más simple y barato de montar. Respondes con tu patrimonio personal si hay deudas. Cuota fija de unos 80€/mes (tarifa plana primer año).\n\nSL: necesitas 3000€ de capital social. No respondes con tu patrimonio (limitado a la empresa). Más gastos de gestoría. Mejor si facturas más de 30.000€/año o tienes riesgos.'],
             ]],

            // === FILOSOFÍA ===
            ['title' => 'Diferencia entre Platón y Aristóteles',
             'content' => 'En filosofía estamos viendo a los griegos y no me queda claro en qué se diferencian sus teorías del conocimiento.',
             'tags' => ['Filosofía'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Platón: las ideas son realidades perfectas y eternas en otro mundo. Lo que vemos aquí son copias imperfectas (Mito de la Caverna). Conocer es recordar esas ideas (reminiscencia).\n\nAristóteles: las ideas NO existen separadas de las cosas. La esencia está en los objetos mismos. Conocer es abstraer la forma de la materia mediante la experiencia.'],
             ]],
            ['title' => '¿Qué es el imperativo categórico de Kant?',
             'content' => 'No entiendo el concepto. ¿Alguien lo explica en lenguaje sencillo?',
             'tags' => ['Filosofía'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'El imperativo categórico dice: "Actúa solo según aquella máxima que puedas querer que se convierta en ley universal".\n\nEn cristiano: antes de hacer algo, pregúntate "¿y si todo el mundo hiciera esto?". Si la respuesta es que el mundo funcionaría mejor, entonces es ético. Si no, no lo hagas.\n\nEjemplo: ¿Miento? Si todo el mundo mintiera, nadie creería a nadie y la comunicación sería imposible. Luego mentir está mal.'],
             ]],

            // === TECNOLOGÍA ===
            ['title' => '¿Cómo funciona un motor de corriente continua?',
             'content' => 'En tecnología estamos viendo motores eléctricos. No entiendo cómo el rotor empieza a girar.',
             'tags' => ['Tecnología'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Principio básico: cuando una corriente eléctrica pasa por una espira dentro de un campo magnético, se genera una fuerza que hace girar la espira (Ley de Lorentz).\n\nEl motor DC tiene:\n- Estátor: imanes fijos (campo magnético).\n- Rotor: bobinas por donde pasa corriente.\n- Delgas: invierten la corriente cada media vuelta para que siga girando.\n\nLa corriente crea un electroimán que los imanes fijos empujan, haciendo girar el rotor.'],
             ]],
            ['title' => '¿Qué es la impresión 3D y qué tipos hay?',
             'content' => 'Me interesa el tema pero no sé por dónde empezar. ¿Qué tipo de impresora 3D me recomiendan para empezar?',
             'tags' => ['Tecnología'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Para empezar, una impresora FDM (filamento fundido) tipo Creality Ender 3 o similar. Cuesta unos 200€ y usa filamento PLA (fácil, no tóxico, biodegradable).\n\nOtros tipos:\n- Resina (SLA): más precisión pero más cara y tóxica (necesitas ventilación).\n- SLS: sinterizado de polvo (industrial, muy cara).\n\nSoftware: Cura o PrusaSlicer para preparar modelos. Thingiverse para descargar diseños gratuitos.'],
             ]],

            // === DIBUJO TÉCNICO ===
            ['title' => 'Duda con perspectivas: isométrica vs caballera',
             'content' => 'No sé cuándo usar perspectiva isométrica y cuándo caballera. ¿Cuál es la diferencia principal?',
             'tags' => ['Dibujo Técnico'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'ISOMÉTRICA: los tres ejes forman 120° entre sí. Todas las medidas se mantienen en la misma escala (no se deforman). Ideal para piezas técnicas.\n\nCABALLERA: una cara está en verdadera magnitud (frontal) y las líneas de profundidad van a 45° con reducción (generalmente 1/2). Más fácil de dibujar pero deforma la profundidad.'],
             ]],

            // === FORMACIÓN Y ORIENTACIÓN LABORAL ===
            ['title' => '¿Cómo se hace una nómina?',
             'content' => 'En FOL nos explicaron las nóminas pero no sé calcular las deducciones. ¿Alguien me explica las partes de una nómina?',
             'tags' => ['FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Partes de una nómina:\n1. DEVENGOS: Salario base + complementos (plus peligrosidad, nocturnidad, antigüedad) + horas extra.\n2. DEDUCCIONES:\n   - Contingencias comunes (4.70%)\n   - Desempleo (1.55%)\n   - Formación profesional (0.10%)\n   - IRPF (según tu salario y situación)\n3. LÍQUIDO A PERCIBIR = Devengos - Deducciones.\n\nEjemplo: SB 1500€, IRPF 10% → 1500 - (1500*6.35%) - (1500*10%) = 1500 - 95.25 - 150 = 1254.75€ netos.'],
             ]],
            ['title' => 'Derechos y deberes del trabajador',
             'content' => '¿Cuáles son los derechos básicos que tengo como trabajador? Mi jefe no me da los descansos que creo que me corresponden.',
             'tags' => ['FOL'],
             'answers' => [
                 ['role' => 'teacher', 'content' => 'Derechos básicos:\n- Jornada máxima 40h/semanales (cómputo anual).\n- Descanso mínimo 12h entre jornada y jornada.\n- Descanso semanal 1.5 días (normalmente sábado tarde + domingo).\n- 15 minutos de descanso si jornada > 6h (o lo que diga el convenio).\n- 30 días de vacaciones al año.\n- 2 pagas extra al año (o prorrateadas).\n\nSi no te respetan estos derechos, habla con tu delegado sindical o ve a Inspección de Trabajo.'],
             ]],
        ];

        $createdCount = 0;
        foreach ($questions as $qData) {
            $author = $students->random();

            $question = Question::create([
                'user_id' => $author->id,
                'title' => $qData['title'],
                'content' => $qData['content'],
            ]);

            foreach ($qData['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $question->tags()->attach($tags[$tagName]);
                }
            }

            $numAnswers = count($qData['answers']);
            foreach ($qData['answers'] as $i => $ansData) {
                $respondent = null;
                if ($ansData['role'] === 'teacher' && $teachers->isNotEmpty()) {
                    $respondent = $teachers->random();
                } else {
                    $respondent = $allUsers->where('id', '!=', $author->id)->random();
                }
                if (!$respondent) continue;

                $isUseful = ($i === 0);

                Answer::create([
                    'question_id' => $question->id,
                    'user_id' => $respondent->id,
                    'content' => $ansData['content'],
                    'is_useful' => $isUseful,
                    'reputation' => $isUseful ? 10 : rand(0, 5),
                ]);

                if ($isUseful) {
                    $respondent->increment('reputation', 10);
                }
            }

            $question->update(['answer_count' => $numAnswers]);
            $createdCount++;
        }

        // Mateo's CSS question
        $mateo = User::where('email', 'like', 'mateo.garcia%@cifpzonzamas.es')->first();
        $valentina = User::where('email', 'like', 'valentina.lopez%@cifpzonzamas.es')->first();
        if ($mateo && !Question::where('title', '¿Cómo se alinea verticalmente un div sin Flexbox?')->exists()) {
            $q = Question::create([
                'user_id' => $mateo->id,
                'title' => '¿Cómo se alinea verticalmente un div sin Flexbox?',
                'content' => 'Estoy haciendo un ejercicio y no podemos usar Flexbox. ¿Hay alguna forma de centrar verticalmente un div con CSS puro?',
            ]);
            if (isset($tags['Diseño de Interfaces Web'])) {
                $q->tags()->attach($tags['Diseño de Interfaces Web']);
            }
            if ($valentina) {
                Answer::create([
                    'question_id' => $q->id,
                    'user_id' => $valentina->id,
                    'content' => '¡Hola Mateo! Puedes usar:\n\n1. position: absolute; top: 50%; transform: translateY(-50%);\n2. display: table-cell; vertical-align: middle;\n3. display: grid; place-items: center; (esto sí es Grid, no Flex, y funciona)\n\nPersonalmente recomiendo la opción 1, funciona en navegadores antiguos.',
                    'is_useful' => true,
                    'reputation' => 10,
                ]);
                $valentina->increment('reputation', 10);
            }
            $q->update(['answer_count' => 1]);
            $createdCount++;
        }

        echo "✅ {$createdCount} preguntas creadas con respuestas.\n";
    }
}
