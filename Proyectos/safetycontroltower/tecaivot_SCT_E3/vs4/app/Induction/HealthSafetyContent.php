<?php
/**
 * Safety Control Tower — Health & Safety induction content manifest.
 * E3-VS4: módulos, multimedia, animaciones y evaluaciones por módulo.
 */
final class SctHealthSafetyContent
{
    public static function manifest(): array
    {
        // Slides 12 y 13 de la PPT original se retiran del flujo: su función
        // se reemplaza por la evaluación nativa SCT del Módulo 1.
        $titles = [
            1  => 'Bienvenida a la inducción de Seguridad y Salud Ocupacional',
            2  => 'Módulo 1 — Introducción, legislación y control de riesgos',
            3  => '¿Quiénes somos?',
            4  => 'Portafolio energético',
            5  => 'Activos renovables por tecnología',
            6  => 'Ley 16.744 — Accidentes y enfermedades profesionales',
            7  => 'DS N°594 — Condiciones sanitarias y ambientales',
            8  => 'Ley Karin — Prevención del acoso y violencia en el trabajo',
            9  => 'Concepto de seguridad — Tarea, peligro y riesgo',
            10 => 'Jerarquía de los controles',
            11 => 'Elementos de Protección Personal',
            14 => 'Módulo 2 — Pilares y esenciales de seguridad ENGIE',
            15 => 'Pilares de ENGIE — Nuestro compromiso',
            16 => 'Pilares de ENGIE',
            17 => '5 esenciales de seguridad',
            18 => 'Ninguna vida en riesgo',
            19 => 'Reglas que salvan vidas — Resumen',
            20 => 'Reglas que salvan vidas — Comprueba',
            21 => 'Reglas que salvan vidas — Mantente anclado',
            22 => 'Reglas que salvan vidas — Controla',
            23 => 'Reglas que salvan vidas — Asegúrate',
            24 => 'Reglas que salvan vidas — Evita cargas suspendidas',
            25 => 'Reglas que salvan vidas — Descarta alcohol y estupefacientes',
            26 => 'Reglas que salvan vidas — Asegúrate / Para',
            27 => '¿Qué riesgo corresponde a qué regla que salva vidas? — Parte 1',
            28 => '¿Qué riesgo corresponde a qué regla que salva vidas? — Parte 2',
            29 => 'Stop Work',
            30 => 'Stop Work — Video',
            31 => 'Vigilancia compartida',
            32 => 'Vigilancia compartida — Video',
            33 => 'El minuto que salva vidas — LMRA',
            34 => 'LMRA — Aplicación visual',
            35 => 'Eventos e incidentes',
            36 => 'Ejercicio — Riesgo de derrumbe en zanjas',
            37 => 'Ejercicio — Uso de dispositivos al conducir',
            38 => 'Ejercicio — Ausencia de energía',
            39 => 'Ejercicio — Cargas suspendidas e izaje',
            40 => 'Ejercicio — Alcohol y drogas',
            41 => 'Ejercicio — Trabajo en altura',
            42 => 'Ejercicio — Vehículos y equipos en movimiento',
            43 => 'Ejercicio — Espacios confinados',
            44 => 'Pilares de ENGIE — Procesos industriales y riesgos psicosociales',
            45 => 'Nuestros 9 compromisos para el bienestar en los lugares de trabajo',
            46 => 'Módulo 3 — Tipos de eventos e incidentes HIPO',
            47 => 'Tipos de eventos — Objetivo y definición de HIPO',
            48 => '¿Qué significa HIPO?',
            49 => 'Tipos de eventos — Video',
            50 => 'Identificación de situaciones peligrosas y eventos HIPO',
            51 => 'Eventos e incidentes — Reportar hoy evita que mañana ocurra lo peor',
            52 => 'Caso HIPO — Derrumbe y panel de entibación',
            53 => 'Análisis del caso HIPO — Puntos esenciales no aplicados',
            54 => 'Caso HIPO — Caída de objeto durante montaje de torre eólica',
            55 => 'Análisis del caso HIPO — Evaluación de riesgos y EPP',
            56 => 'Caso HIPO — Caída desde altura',
            57 => 'Análisis del caso HIPO — Vigilancia, Stop Work y LMRA',
        ];

        $mediaBase = '../../assets/inductions/health-safety/part-01/media/';
        $media = [
            2  => ['type'=>'audio','durationMs'=>41311,'src'=>$mediaBase.'media1.m4a','x'=>93.670,'y'=>90.006,'w'=>3.993,'h'=>7.099],
            3  => ['type'=>'video','durationMs'=>74025,'src'=>$mediaBase.'media2.mp4','poster'=>$mediaBase.'poster-slide-03.png','x'=>12.309,'y'=>18.747,'w'=>75.383,'h'=>75.383],
            6  => ['type'=>'audio','durationMs'=>32416,'src'=>$mediaBase.'media3.m4a','x'=>94.837,'y'=>89.117,'w'=>3.993,'h'=>7.099],
            7  => ['type'=>'audio','durationMs'=>49491,'src'=>$mediaBase.'media4.m4a','x'=>95.003,'y'=>91.488,'w'=>3.993,'h'=>7.099],
            8  => ['type'=>'audio','durationMs'=>40833,'src'=>$mediaBase.'media5.m4a','x'=>94.253,'y'=>91.340,'w'=>3.993,'h'=>7.099],
            9  => ['type'=>'audio','durationMs'=>58530,'src'=>$mediaBase.'media6.m4a','x'=>94.337,'y'=>89.562,'w'=>3.993,'h'=>7.099],
            10 => ['type'=>'audio','durationMs'=>60153,'src'=>$mediaBase.'media7.m4a','x'=>95.253,'y'=>88.377,'w'=>3.993,'h'=>7.099],
            14 => ['type'=>'audio','durationMs'=>28671,'src'=>$mediaBase.'media9.m4a','x'=>94.420,'y'=>88.821,'w'=>3.993,'h'=>7.099],
            15 => ['type'=>'audio','durationMs'=>28654,'src'=>$mediaBase.'media10.m4a','x'=>95.003,'y'=>91.191,'w'=>3.993,'h'=>7.099],
            16 => ['type'=>'video','durationMs'=>176384,'src'=>$mediaBase.'media11.mp4','poster'=>$mediaBase.'poster-slide-16.png','x'=>12.640,'y'=>18.533,'w'=>72.849,'h'=>73.062],
            17 => ['type'=>'audio','durationMs'=>24897,'src'=>$mediaBase.'media12.m4a','x'=>93.305,'y'=>90.065,'w'=>3.993,'h'=>7.099],
            18 => ['type'=>'video','durationMs'=>80128,'src'=>$mediaBase.'media13.mp4','poster'=>$mediaBase.'poster-slide-18.png','x'=>19.000,'y'=>25.000,'w'=>68.000,'h'=>65.000],
            20 => ['type'=>'animation','durationMs'=>15660,'src'=>$mediaBase.'image80.gif','x'=>19.126,'y'=>23.945,'w'=>76.114,'h'=>74.862],
            21 => ['type'=>'animation','durationMs'=>10260,'src'=>$mediaBase.'image82.gif','x'=>18.911,'y'=>23.688,'w'=>76.330,'h'=>75.489],
            22 => ['type'=>'animation','durationMs'=>21410,'src'=>$mediaBase.'image84.gif','x'=>18.674,'y'=>23.711,'w'=>76.255,'h'=>75.441],
            23 => ['type'=>'animation','durationMs'=>12660,'src'=>$mediaBase.'image86.gif','x'=>18.754,'y'=>23.842,'w'=>76.662,'h'=>75.180],
            24 => ['type'=>'animation','durationMs'=>12830,'src'=>$mediaBase.'image88.gif','x'=>19.587,'y'=>24.030,'w'=>76.551,'h'=>74.805],
            25 => ['type'=>'animation','durationMs'=>18080,'src'=>$mediaBase.'image90.gif','x'=>19.126,'y'=>24.099,'w'=>76.551,'h'=>74.531],
            26 => ['type'=>'animation','durationMs'=>16510,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-26-animation.gif','x'=>20.85745,'y'=>25.97365,'w'=>74.08165,'h'=>72.92333],
            27 => ['type'=>'audio','durationMs'=>8086,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-27-audio.m4a'],
            29 => ['type'=>'audio','durationMs'=>12498,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-29-audio.m4a'],
            30 => ['type'=>'video','durationMs'=>157972,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-30-video.mp4','poster'=>'../../assets/inductions/health-safety/part-02/media/slide-30-poster.jpg','x'=>14.351,'y'=>29.427,'w'=>69.191,'h'=>64.349],
            31 => ['type'=>'audio','durationMs'=>19600,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-31-audio.m4a'],
            32 => ['type'=>'video','durationMs'=>113536,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-32-video.mp4','poster'=>'../../assets/inductions/health-safety/part-02/media/slide-32-poster.jpg','x'=>13.43,'y'=>26.129,'w'=>73.14,'h'=>66.667],
            33 => ['type'=>'audio','durationMs'=>22782,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-33-audio.m4a'],
            35 => ['type'=>'audio','durationMs'=>10261,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-35-audio.m4a'],
            36 => ['type'=>'animation','durationMs'=>12660,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-36-animation.gif','x'=>4.60659,'y'=>24.39118,'w'=>45.39341,'h'=>45.41114],
            37 => ['type'=>'animation','durationMs'=>16510,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-37-animation.gif','x'=>4.35607,'y'=>27.16912,'w'=>45.64393,'h'=>45.66177],
            38 => ['type'=>'animation','durationMs'=>15660,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-38-animation.gif','x'=>4.4628,'y'=>25.01889,'w'=>45.3934,'h'=>45.41114],
            39 => ['type'=>'animation','durationMs'=>12830,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-39-animation.gif','x'=>3.27306,'y'=>28.2057,'w'=>45.64393,'h'=>45.66177],
            40 => ['type'=>'animation','durationMs'=>18080,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-40-animation.gif','x'=>2.85571,'y'=>28.19514,'w'=>45.81952,'h'=>45.83742],
            41 => ['type'=>'animation','durationMs'=>10260,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-41-animation.gif','x'=>3.0687,'y'=>28.49189,'w'=>45.42113,'h'=>45.43889],
            42 => ['type'=>'animation','durationMs'=>10830,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-42-animation.gif','x'=>2.91387,'y'=>25.99601,'w'=>45.42114,'h'=>45.43889],
            43 => ['type'=>'animation','durationMs'=>21410,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-43-animation.gif','x'=>3.32322,'y'=>26.70201,'w'=>45.70723,'h'=>45.7251],
            44 => ['type'=>'video','durationMs'=>120000,'src'=>'../../assets/inductions/health-safety/part-02/media/slide-44-video.mp4','poster'=>'../../assets/inductions/health-safety/part-02/media/slide-44-poster.jpg','x'=>18.01,'y'=>28.034,'w'=>64.935,'h'=>64.926],
            46 => ['type'=>'audio','durationMs'=>6996,'src'=>'../../assets/inductions/health-safety/part-03/media/slide-46-audio.m4a'],
            47 => ['type'=>'audio','durationMs'=>17887,'src'=>'../../assets/inductions/health-safety/part-03/media/slide-47-audio.m4a'],
            48 => ['type'=>'audio','durationMs'=>4153,'src'=>'../../assets/inductions/health-safety/part-03/media/slide-48-audio.m4a'],
            49 => ['type'=>'video','durationMs'=>168204,'src'=>'../../assets/inductions/health-safety/part-03/media/slide-49-video.mp4','poster'=>'../../assets/inductions/health-safety/part-03/media/slide-49-poster.jpg','x'=>19.81818,'y'=>26.41357,'w'=>63.22727,'h'=>63.57027],
            52 => ['type'=>'audio','durationMs'=>8086,'src'=>'../../assets/inductions/health-safety/part-03/media/slide-52-audio.m4a'],
        ];

        $animationData = [];
        $animationFile = __DIR__ . '/data/health-safety-part-01-animations.json';
        if (is_file($animationFile)) {
            $decoded = json_decode((string) file_get_contents($animationFile), true);
            if (is_array($decoded)) $animationData = $decoded;
        }

        $animationFilePart03 = __DIR__ . '/data/health-safety-part-03-animations.json';
        if (is_file($animationFilePart03)) {
            $decodedPart03 = json_decode((string) file_get_contents($animationFilePart03), true);
            if (is_array($decodedPart03)) $animationData = array_replace($animationData, $decodedPart03);
        }

        $slides = [];
        foreach ($titles as $number => $title) {
            $animation = $animationData[(string) $number] ?? [];
            $slide = [
                'number' => $number,
                'title' => $title,
                'asset' => $animation['baseAsset'] ?? sprintf('../../assets/inductions/health-safety/part-01/slide-%02d.jpg', $number),
                'thumb' => $animation['thumbAsset'] ?? sprintf('../../assets/inductions/health-safety/part-01/slide-%02d.jpg', $number),
                'animation' => [
                    'maxStep' => (int) ($animation['maxStep'] ?? 0),
                    // Preserve the legacy default (first visual step) only when a slide
                    // does not explicitly define its initial state.
                    'initialStep' => array_key_exists('initialStep', $animation)
                        ? (int) $animation['initialStep']
                        : ((int) ($animation['maxStep'] ?? 0) > 0 ? 1 : 0),
                    'autoStart' => !empty($animation['autoStart']),
                    // Slides such as 15 and 17 intentionally reopen in their clean
                    // initial state, even after completion, so audio can drive the
                    // complete pedagogical sequence again without exposing layers early.
                    'reviewStartsInitial' => !empty($animation['reviewStartsInitial']),
                    'effects' => is_array($animation['effects'] ?? null) ? $animation['effects'] : [],
                    'layers' => is_array($animation['layers'] ?? null) ? $animation['layers'] : [],
                ],
            ];
            if (isset($media[$number])) $slide['media'] = $media[$number];
            $slides[] = $slide;
        }

        return [
            'id' => 'health-safety-engie',
            'title' => 'Inducción de Seguridad y Salud Ocupacional',
            'company' => 'ENGIE Chile',
            'part' => 1,
            'parts_total' => 5,
            'slides_total_expected' => 125,
            'modules' => [
                [
                    'id' => 'module-01', 'number' => 1,
                    'title' => 'Introducción, legislación y control de riesgos',
                    'slide_numbers' => range(1, 11), 'status' => 'active',
                    'evaluation_id' => 'module-01-evaluation-01',
                ],
                [
                    'id' => 'module-02', 'number' => 2,
                    'title' => 'Pilares y esenciales de seguridad ENGIE',
                    // Local slides removed from the active flow:
                    // 14, 15 and 23–30 => internal/global 27, 28 and 36–43.
                    // Their source content remains preserved in assets/animation data
                    // and is reused by the native Module 2 evaluation.
                    'slide_numbers' => array_merge(range(14, 26), range(29, 35), range(44, 45)),
                    'status' => 'active',
                    'evaluation_id' => 'module-02-evaluation-01',
                ],
                [
                    'id'=>'module-03','number'=>3,
                    'title'=>'Tipos de eventos e incidentes HIPO',
                    'slide_numbers'=>range(46,57),
                    'status'=>'active',
                ],
                ['id'=>'module-04','number'=>4,'title'=>'Módulo 4','slide_numbers'=>[],'status'=>'pending'],
                ['id'=>'module-05','number'=>5,'title'=>'Módulo 5','slide_numbers'=>[],'status'=>'pending'],
                ['id'=>'module-06','number'=>6,'title'=>'Módulo 6','slide_numbers'=>[],'status'=>'pending'],
                ['id'=>'module-07','number'=>7,'title'=>'Módulo 7','slide_numbers'=>[],'status'=>'pending'],
            ],
            'evaluations' => [
                'module-01-evaluation-01' => [
                    'id' => 'module-01-evaluation-01',
                    'number' => 1,
                    'module_id' => 'module-01',
                    'title' => 'Evaluación Módulo 1',
                    'intro' => 'Responde las 4 preguntas. Para aprobar debes obtener 50% o más de respuestas correctas.',
                    'pass_percentage' => 50,
                    'pass_operator' => 'greater_than_or_equal',
                    'questions' => [
                        [
                            'id'=>'M1Q1',
                            'text'=>'¿Qué protege principalmente la Ley N° 16.744?',
                            'options'=>[
                                ['id'=>'a','text'=>'Sólo accidentes ocurridos dentro de la faena.','correct'=>false],
                                ['id'=>'b','text'=>'Accidentes del trabajo, accidentes de trayecto y enfermedades profesionales.','correct'=>true],
                                ['id'=>'c','text'=>'Únicamente condiciones sanitarias y ambientales.','correct'=>false],
                                ['id'=>'d','text'=>'Vacaciones adicionales y bonos de transporte.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M1Q2',
                            'text'=>'Según el DS N°594, ¿qué condición debe asegurarse en los lugares de trabajo?',
                            'options'=>[
                                ['id'=>'a','text'=>'Agua potable, servicios higiénicos y condiciones sanitarias apropiadas.','correct'=>true],
                                ['id'=>'b','text'=>'Bonos especiales por exposición al riesgo.','correct'=>false],
                                ['id'=>'c','text'=>'Uso de teléfono durante la conducción.','correct'=>false],
                                ['id'=>'d','text'=>'Sólo señalización, sin necesidad de vías de evacuación.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M1Q3',
                            'text'=>'¿Qué conductas aborda la Ley Karin dentro del ámbito laboral?',
                            'options'=>[
                                ['id'=>'a','text'=>'Sólo accidentes de trayecto.','correct'=>false],
                                ['id'=>'b','text'=>'Acoso laboral, acoso sexual y violencia ejercida por terceros.','correct'=>true],
                                ['id'=>'c','text'=>'Exclusivamente incumplimientos contractuales.','correct'=>false],
                                ['id'=>'d','text'=>'Sólo riesgos asociados a equipos eléctricos.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M1Q4',
                            'text'=>'¿Cuál es la medida de control más efectiva según la jerarquía de controles?',
                            'options'=>[
                                ['id'=>'a','text'=>'Usar únicamente Elementos de Protección Personal.','correct'=>false],
                                ['id'=>'b','text'=>'Señalizar el peligro sin modificar la tarea.','correct'=>false],
                                ['id'=>'c','text'=>'Eliminar el peligro.','correct'=>true],
                                ['id'=>'d','text'=>'Aceptar el riesgo cuando la tarea sea urgente.','correct'=>false],
                            ],
                        ],
                    ],
                ],
                'module-02-evaluation-01' => [
                    'id' => 'module-02-evaluation-01',
                    'number' => 2,
                    'module_id' => 'module-02',
                    'title' => 'Evaluación Módulo 2',
                    'intro' => 'Responde las 10 preguntas. Para aprobar debes obtener 50% o más de respuestas correctas.',
                    'pass_percentage' => 50,
                    'pass_operator' => 'greater_than_or_equal',
                    'questions' => [
                        [
                            'id'=>'M2Q1',
                            'text'=>'¿Cuál expresa correctamente el compromiso de seguridad presentado en los Pilares de ENGIE?',
                            'options'=>[
                                ['id'=>'a','text'=>'Las tareas urgentes pueden ejecutarse sin todos los controles si existe autorización.','correct'=>false],
                                ['id'=>'b','text'=>'Ninguna actividad es tan urgente o importante que no pueda realizarse de forma segura.','correct'=>true],
                                ['id'=>'c','text'=>'La seguridad depende exclusivamente del área de prevención.','correct'=>false],
                                ['id'=>'d','text'=>'Los controles de seguridad sólo se aplican a proyectos nuevos.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q2',
                            'text'=>'¿Cuál de las siguientes acciones forma parte de los 5 Esenciales de Seguridad de ENGIE?',
                            'options'=>[
                                ['id'=>'a','text'=>'Notificar eventos e incidentes para evitar que un día ocurra lo peor.','correct'=>true],
                                ['id'=>'b','text'=>'Continuar un trabajo peligroso mientras no exista un accidente.','correct'=>false],
                                ['id'=>'c','text'=>'Usar el teléfono durante la conducción cuando el trayecto sea corto.','correct'=>false],
                                ['id'=>'d','text'=>'Dejar la evaluación de riesgos únicamente a la jefatura.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q3',
                            'text'=>'¿Qué principio resume correctamente STOP WORK?',
                            'options'=>[
                                ['id'=>'a','text'=>'Si no es seguro, no se inicia o se detiene el trabajo hasta recuperar condiciones seguras.','correct'=>true],
                                ['id'=>'b','text'=>'Sólo la jefatura puede detener una tarea insegura.','correct'=>false],
                                ['id'=>'c','text'=>'Un trabajo iniciado debe terminar aunque cambien las condiciones.','correct'=>false],
                                ['id'=>'d','text'=>'STOP WORK se aplica únicamente después de un accidente.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q4',
                            'text'=>'¿Qué significa Vigilancia Compartida?',
                            'options'=>[
                                ['id'=>'a','text'=>'Supervisar únicamente a trabajadores de empresas contratistas.','correct'=>false],
                                ['id'=>'b','text'=>'Velar por la seguridad propia y la de cada persona, sin importar cargo, empresa o nivel jerárquico.','correct'=>true],
                                ['id'=>'c','text'=>'Delegar la seguridad del área únicamente al encargado de turno.','correct'=>false],
                                ['id'=>'d','text'=>'Observar sin intervenir ante una condición insegura.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q5',
                            'text'=>'¿Cuál es el propósito del LMRA o “Minuto que Salva Vidas”?',
                            'options'=>[
                                ['id'=>'a','text'=>'Registrar la asistencia antes de comenzar la jornada.','correct'=>false],
                                ['id'=>'b','text'=>'Verificar el entorno y confirmar que los riesgos están controlados antes de iniciar una actividad.','correct'=>true],
                                ['id'=>'c','text'=>'Reemplazar el permiso de trabajo y el análisis de riesgos.','correct'=>false],
                                ['id'=>'d','text'=>'Evaluar únicamente los riesgos ocurridos después de terminar la tarea.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q6',
                            'text'=>'¿Por qué es importante reportar eventos e incidentes?',
                            'options'=>[
                                ['id'=>'a','text'=>'Para evitar su reiteración y prevenir que se conviertan en hechos graves.','correct'=>true],
                                ['id'=>'b','text'=>'Sólo para identificar responsables individuales.','correct'=>false],
                                ['id'=>'c','text'=>'Porque reemplaza las medidas preventivas del trabajo.','correct'=>false],
                                ['id'=>'d','text'=>'Únicamente cuando existe una lesión con tiempo perdido.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q7',
                            'text'=>'Al trabajar o ingresar a una zanja, ¿qué Regla que Salva Vidas corresponde aplicar?',
                            'options'=>[
                                ['id'=>'a','text'=>'Ingresar si la excavación parece estable visualmente.','correct'=>false],
                                ['id'=>'b','text'=>'Solo bajar si las medidas de protección contra derrumbes son aplicadas y adecuadas.','correct'=>true],
                                ['id'=>'c','text'=>'Usar únicamente casco y guantes como control principal.','correct'=>false],
                                ['id'=>'d','text'=>'Trabajar acompañado, aunque no existan medidas contra derrumbes.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q8',
                            'text'=>'Antes de trabajar en un cuadro eléctrico o sobre otra fuente de energía peligrosa, ¿qué debes hacer?',
                            'options'=>[
                                ['id'=>'a','text'=>'Comenzar el trabajo si el equipo se encuentra detenido.','correct'=>false],
                                ['id'=>'b','text'=>'Verificar la ausencia de energía mecánica, química, eléctrica o de fluidos bajo presión antes de comenzar.','correct'=>true],
                                ['id'=>'c','text'=>'Usar teléfono para avisar mientras se manipula el equipo.','correct'=>false],
                                ['id'=>'d','text'=>'Realizar primero una prueba de funcionamiento energizada.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q9',
                            'text'=>'Durante una maniobra de izaje, ¿qué regla debe cumplirse?',
                            'options'=>[
                                ['id'=>'a','text'=>'No caminar ni detenerse debajo de cargas suspendidas.','correct'=>true],
                                ['id'=>'b','text'=>'Permanecer bajo la carga para controlar visualmente su posición.','correct'=>false],
                                ['id'=>'c','text'=>'Acercarse a la carga mientras esté suspendida para reducir el tiempo de maniobra.','correct'=>false],
                                ['id'=>'d','text'=>'La restricción aplica sólo cuando la carga supera una tonelada.','correct'=>false],
                            ],
                        ],
                        [
                            'id'=>'M2Q10',
                            'text'=>'Antes de entrar en un espacio confinado, ¿qué condición debe comprobarse y mantenerse controlada?',
                            'options'=>[
                                ['id'=>'a','text'=>'Que exista cobertura de telefonía móvil.','correct'=>false],
                                ['id'=>'b','text'=>'Que la atmósfera interior sea segura y que sea monitoreada durante toda la operación.','correct'=>true],
                                ['id'=>'c','text'=>'Que la tarea pueda completarse en menos de una hora.','correct'=>false],
                                ['id'=>'d','text'=>'Que sólo exista una persona dentro del espacio.','correct'=>false],
                            ],
                        ],
                    ],
                ],
            ],
            'slides' => $slides,
            'source' => 'PRESENTACION ENGIE_IRL_SCL — Partes 1, 2 y 3 (slides internas 1–57)',
        ];
    }

    public static function module(array $manifest, string $moduleId): ?array
    {
        foreach (($manifest['modules'] ?? []) as $module) {
            if ((string)($module['id'] ?? '') === $moduleId) return $module;
        }
        return null;
    }

    public static function evaluation(array $manifest, string $evaluationId): ?array
    {
        $evaluation = $manifest['evaluations'][$evaluationId] ?? null;
        return is_array($evaluation) ? $evaluation : null;
    }
}
