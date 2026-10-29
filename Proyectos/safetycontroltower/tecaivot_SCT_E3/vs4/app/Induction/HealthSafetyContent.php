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
            18 => ['type'=>'video','durationMs'=>80128,'src'=>$mediaBase.'media13.mp4','poster'=>$mediaBase.'poster-slide-18.png','x'=>15.512,'y'=>16.573,'w'=>75.833,'h'=>75.833],
            20 => ['type'=>'animation','durationMs'=>15660,'src'=>$mediaBase.'image80.gif','x'=>19.126,'y'=>23.945,'w'=>76.114,'h'=>74.862],
            21 => ['type'=>'animation','durationMs'=>10260,'src'=>$mediaBase.'image82.gif','x'=>18.911,'y'=>23.688,'w'=>76.330,'h'=>75.489],
            22 => ['type'=>'animation','durationMs'=>21410,'src'=>$mediaBase.'image84.gif','x'=>18.674,'y'=>23.711,'w'=>76.255,'h'=>75.441],
            23 => ['type'=>'animation','durationMs'=>12660,'src'=>$mediaBase.'image86.gif','x'=>18.754,'y'=>23.842,'w'=>76.662,'h'=>75.180],
            24 => ['type'=>'animation','durationMs'=>12830,'src'=>$mediaBase.'image88.gif','x'=>19.587,'y'=>24.030,'w'=>76.551,'h'=>74.805],
            25 => ['type'=>'animation','durationMs'=>18080,'src'=>$mediaBase.'image90.gif','x'=>19.126,'y'=>24.099,'w'=>76.551,'h'=>74.531],
        ];

        $animationData = [];
        $animationFile = __DIR__ . '/data/health-safety-part-01-animations.json';
        if (is_file($animationFile)) {
            $decoded = json_decode((string) file_get_contents($animationFile), true);
            if (is_array($decoded)) $animationData = $decoded;
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
                    'slide_numbers' => range(14, 25), 'status' => 'partial',
                ],
                ['id'=>'module-03','number'=>3,'title'=>'Módulo 3','slide_numbers'=>[],'status'=>'pending'],
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
            ],
            'slides' => $slides,
            'source' => 'PRESENTACION ENGIE_IRL_SCL — Parte 1',
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
