<?php
if (ob_get_length()) ob_end_clean();
require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
require 'db.php';

// Zona horaria
date_default_timezone_set('America/Punta_Arenas');
$fechaHora = date('d-m-Y H:i');

// Conexión
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
$conn->set_charset('utf8');

// Obtener datos de agrupación por estado
$sql_estados = "SELECT E.name, COUNT(*) AS total FROM app_entity_41 AS A INNER JOIN app_fields_choices AS E ON A.field_498 = E.id WHERE E.id != 187 GROUP BY E.name ORDER BY E.name ASC";
$res_estados = $conn->query($sql_estados);

// Preparar arrays dinámicos
$labels = [];
$data = [];
$total_no_realizadas = 0;

if ($res_estados) {
    while ($row = $res_estados->fetch_assoc()) {
        $labels[] = $row['name'];
        $data[] = (int) $row['total'];
        $total_no_realizadas += (int) $row['total'];
    }
}

// Datos adicionales
function fetch_scalar($conn, $sql) {
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return $row['total'] ?? 0;
    }
    return 0;
}

$sql_realizadas       = "SELECT COUNT(*) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id = 187";
$sql_entorno_real     = "SELECT SUM(A.field_529) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id = 187";
$sql_entorno_plan     = "SELECT SUM(A.field_526) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id != 187";
$sql_monto_realizadas = "SELECT SUM(A.field_505) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id = 187";
$sql_monto_no_real    = "SELECT SUM(A.field_505) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id != 187";

$total_realizadas    = (int) fetch_scalar($conn, $sql_realizadas);
$total_entorno_real  = (float) fetch_scalar($conn, $sql_entorno_real);
$total_entorno_plan  = (float) fetch_scalar($conn, $sql_entorno_plan);
$monto_realizadas    = (float) fetch_scalar($conn, $sql_monto_realizadas);
$monto_no_realizadas = (float) fetch_scalar($conn, $sql_monto_no_real);
$total_actividades   = $total_realizadas + $total_no_realizadas;
$total_monto         = $monto_realizadas + $monto_no_realizadas;

// QuickChart.io helper
define('CHART_BASE', 'https://quickchart.io/chart?format=png&c=');
function quickchart_url($config) {
    return CHART_BASE . urlencode(json_encode($config));
}



// Gráfico dinámico de Actividades
// Colores fijos por estado
$colores_fijos = [
    'EN PROCESO'   => '#4caf50',
    'NO REALIZADO' => '#c0ca33',
    'PENDIENTE'    => '#b71c1c'
];

// Preparar colores y datasets individuales por estado
$datasets = [];
foreach ($labels as $index => $label) {
    $datasets[] = [
        'label' => $label,
        'data' => array_map(fn($i) => $i === $index ? $data[$i] : null, array_keys($data)),
        'backgroundColor' => $colores_fijos[$label] ?? '#9e9e9e',
        'barPercentage' => 0.6,
        'categoryPercentage' => 1.0
    ];
}

$chart1 = [
    'type' => 'bar',
    'data' => [
        'labels' => $labels,
        'datasets' => $datasets
    ],
    'options' => [
        'layout' => [
            'padding' => [
                'top' => 60,
                'bottom' => 30,
                'left' => 10,
                'right' => 10
            ]
        ],
        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'top',
                'labels' => [
                    'padding' => 25,
                    'font' => ['size' => 10]
                ]
            ],
            'datalabels' => [
                'anchor' => 'center',
                'align' => 'center',
                'color' => '#fff',
                'font' => ['weight' => 'bold', 'size' => 10],
                // Mostrar siempre el valor mientras no sea null
                'formatter' => 'function(value) { return value !== null ? value : ""; }'
            ]
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'min' => 0, // fuerza el inicio real en 0
                'ticks' => [
                    'stepSize' => 1,
                    'precision' => 0,
                    'maxTicksLimit' => 15,
                    'font' => ['size' => 10]
                ],
                'grid' => [
                    'drawBorder' => false,
                    'color' => 'rgba(0,0,0,0.05)'
                ]
            ],
            'x' => [
                'ticks' => [
                    'font' => ['size' => 10]
                ],
                'grid' => [
                    'display' => false
                ]
            ]
        ]
    ]
];




// Gráfico Entorno
$chart2 = [
    'type'=>'bar',
    'data'=>['labels'=>['Entorno Planificado','Entorno Real'],'datasets'=>[[
        'label'=>'Entorno',
        'data'=>[$total_entorno_plan,$total_entorno_real],
        'backgroundColor'=>['#ff9800','#4caf50']
    ]]],
    'options'=>['plugins'=>['legend'=>['display'=>false],'datalabels'=>['anchor'=>'end','align'=>'top','color'=>'#000','font'=>['weight'=>'bold']]],'scales'=>['y'=>['beginAtZero'=>true,'ticks'=>['stepSize'=>5,'precision'=>0]]]]
];

// Gráfico Monto
$chart3 = [
    'type'=>'bar',
    'data'=>['labels'=>['Monto Realizado','Monto No Realizado','Total Monto'],'datasets'=>[[
        'label'=>'Monto',
        'data'=>[$monto_realizadas,$monto_no_realizadas,$total_monto],
        'backgroundColor'=>['#4caf50','#f44336','#2196f3']
    ]]],
    'options'=>['plugins'=>['legend'=>['display'=>false],'datalabels'=>['anchor'=>'end','align'=>'top','color'=>'#000','font'=>['weight'=>'bold']]],'scales'=>['y'=>['beginAtZero'=>true,'ticks'=>['stepSize'=>5,'precision'=>0]]]]
];

// URLs de los gráficos
$url1 = quickchart_url($chart1);
$url2 = quickchart_url($chart2);
$url3 = quickchart_url($chart3);

$conn->close();

// Construcción de PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// HTML para PDF
$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
  @page { margin:10mm; }
  body { font-family:Arial,sans-serif; font-size:12px; text-align:center; }
  h2 { margin:20px 0 10px; font-size:16px; }
  .chart img { width:100%; max-width:190mm; height:auto; margin:0 auto; }
  .table { width:100%; max-width:190mm; margin:10px auto; border-collapse:collapse; }
  .table th, .table td { border:1px solid #555; padding:8px; }
  .table th { background:#f0f0f0; }
  .page-break { page-break-after:always; display:block; }
</style></head><body>';

// Página 1: Actividades por realiozar
$html .= '<h2>Actividades Pendientes de realizar </h2>';
$html .= '<div class="chart"><img src="'.$url1.'" alt="Gráfico Actividades por Estado"></div>';
$html .= '<table class="table"><thead><tr><th>Estado</th><th>Total</th></tr></thead><tbody>';
for ($i = 0; $i < count($labels); $i++) {
    $html .= '<tr><td>'.$labels[$i].'</td><td>'.$data[$i].'</td></tr>';
}
$html .= '<tr><td><strong>Realizadas</strong></td><td><strong>'.$total_realizadas.'</strong></td></tr>';
$html .= '<tr><td><strong>Total Actividades</strong></td><td><strong>'.$total_actividades.'</strong></td></tr>';
$html .= '</tbody></table>';
$html .= '<div class="page-break"></div>';

// Página 2: Entorno panificado vs real
$html .= '<h2>Entorno Planificado vs Real</h2>';
$html .= '<div class="chart"><img src="'.$url2.'" alt="Gráfico Entorno"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Entorno Planificado</td><td>'.$total_entorno_plan.'</td></tr>';
$html .= '<tr><td>Entorno Real</td><td>'.$total_entorno_real.'</td></tr>';
$html .= '</tbody></table>';
$html .= '<div class="page-break"></div>';

// Página 3: Monto realizado vs no realizado
$html .= '<h2>Monto Realizado vs No Realizado</h2>';
$html .= '<div class="chart"><img src="'.$url3.'" alt="Gráfico Monto"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Monto Realizado</td><td>'.number_format($monto_realizadas,0,',','.').'</td></tr>';
$html .= '<tr><td>Monto No Realizado</td><td>'.number_format($monto_no_realizadas,0,',','.').'</td></tr>';
$html .= '<tr><td><strong>Total Monto</strong></td><td><strong>'.number_format($total_monto,0,',','.').'</strong></td></tr>';
$html .= '</tbody></table>';
$html .= '</body></html>';

// Renderizado PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Footer
$canvas = $dompdf->getCanvas();
$canvas->page_script(function($pageNumber, $pageCount, $canvas, $fontMetrics) use ($fechaHora) {
    $font  = $fontMetrics->getFont("Arial", "normal");
    $text  = "Página $pageNumber de $pageCount - Generado: $fechaHora (UTC-3)";
    $width = $fontMetrics->getTextWidth($text, $font, 8);
    $x     = ($canvas->get_width() - $width) / 2;
    $y     = $canvas->get_height() - 10;
    $canvas->text($x, $y, $text, $font, 8);
});

$dompdf->stream("Resumen_Graficos_Actividades.pdf", ["Attachment" => false]);
exit;
