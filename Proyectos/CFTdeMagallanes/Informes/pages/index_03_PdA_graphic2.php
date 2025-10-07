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

define('CHART_BASE', 'https://quickchart.io/chart?format=png&c=');
function quickchart_url($config) {
    return CHART_BASE . urlencode(json_encode($config));
}

// Colores fijos por estado
$colores_fijos = [
    'EN PROCESO'   => '#4caf50',
    'NO REALIZADO' => '#c0ca33',
    'PENDIENTE'    => '#b71c1c'
];
$colores = array_map(fn($label) => $colores_fijos[$label] ?? '#9e9e9e', $labels);

// Gráfico 1: Actividades
$chart1 = [
    'type' => 'bar',
    'data' => [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Cantidad de Actividades',
            'data' => $data,
            'backgroundColor' => $colores
        ]]
    ],
    'options' => [
        'plugins' => [
            'legend' => ['display' => true],
            'datalabels' => [
                'anchor' => 'center',
                'align' => 'center',
                'color' => '#fff',
                'font' => ['weight' => 'bold', 'size' => 11],
                'formatter' => 'function(value) { return value; }'
            ]
        ],
            'scales' => [
                'y' => ['beginAtZero' => true],
                'x' => [
        'ticks' => [
            'font' => ['size' => 10],
            'display' => true,
            'autoSkip' => false,
            'maxRotation' => 0,
            'minRotation' => 0
        ],
        'grid' => [
            'display' => false
        ]
    ]

        ]
    ]
];


// Gráfico 2: Entorno
$chart2 = [
    'type' => 'bar',
    'data' => [
        'labels' => ['Entorno Planificado', 'Entorno Real'],
        'datasets' => [[
            'label' => 'Entorno Total',
            'data' => [$total_entorno_plan, $total_entorno_real],
            'backgroundColor' => ['#ff9800', '#4caf50']
        ]]
    ],
    'options' => [
        'plugins' => [
            'legend' => ['display' => true],
            'datalabels' => [
                'anchor' => 'center',
                'align' => 'center',
                'color' => '#fff',
                'font' => ['weight' => 'bold', 'size' => 11],
                'formatter' => 'function(value) { return value; }'
            ]
        ],
        'scales' => [
            'y' => ['beginAtZero' => true],
            'x' => [
    'ticks' => [
        'font' => ['size' => 10],
        'display' => true,
        'autoSkip' => false,
        'maxRotation' => 0,
        'minRotation' => 0
    ],
    'grid' => [
        'display' => false
    ]
]

        ]
    ]
];


// Gráfico 3: Monto
$chart3 = [
    'type' => 'bar',
    'data' => [
        'labels' => ['Monto Realizado', 'Monto No Realizado', 'Total Monto'],
        'datasets' => [[
            'label' => 'Montos en CLP',
            'data' => [$monto_realizadas, $monto_no_realizadas, $total_monto],
            'backgroundColor' => ['#4caf50', '#f44336', '#2196f3']
        ]]
    ],
    'options' => [
        'plugins' => [
            'legend' => ['display' => true],
            'datalabels' => [
                'anchor' => 'center',
                'align' => 'center',
                'color' => '#fff',
                'font' => ['weight' => 'bold', 'size' => 11],
                'formatter' => 'function(value) { return value; }'
            ]
        ],
        'scales' => [
            'y' => ['beginAtZero' => true],
            'x' => [
    'ticks' => [
        'font' => ['size' => 10],
        'display' => true,
        'autoSkip' => false,
        'maxRotation' => 0,
        'minRotation' => 0
    ],
    'grid' => [
        'display' => false
    ]
]

        ]
    ]
];




$url1 = quickchart_url($chart1);
$url2 = quickchart_url($chart2);
$url3 = quickchart_url($chart3);

$conn->close();

// DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// HTML
$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
  @page { margin:10mm; }
  body { font-family:Arial,sans-serif; font-size:12px; text-align:center; }
  h2 { margin:20px 0 10px; font-size:16px; }
  .chart img { width:100%; max-width:190mm; height:auto; margin:0 auto; }
  .table { width:100%; max-width:190mm; margin:10px auto; border-collapse:collapse; }
  .table th, .table td { border:1px solid #555; padding:8px; }
  .table th { background:#f0f0f0; }
  .table td:last-child { text-align:right; }
  .page-break { page-break-after:always; display:block; }
</style></head><body>';

$html .= '<h2>Actividades Pendientes de Realizar</h2>';
$html .= '<div class="chart"><img src="'.$url1.'" alt="Gráfico Actividades por Estado"></div>';
$html .= '<table class="table"><thead><tr><th>Estado</th><th>Total</th></tr></thead><tbody>';
for ($i = 0; $i < count($labels); $i++) {
    $html .= '<tr><td>'.$labels[$i].'</td><td>'.number_format($data[$i], 0, ',', '.').'</td></tr>';
}
$html .= '<tr><td><strong>Realizadas</strong></td><td><strong>'.number_format($total_realizadas, 0, ',', '.').'</strong></td></tr>';
$html .= '<tr><td><strong>Total Actividades</strong></td><td><strong>'.number_format($total_actividades, 0, ',', '.').'</strong></td></tr>';
$html .= '</tbody></table>';
$html .= '<div class="page-break"></div>';

$html .= '<h2>Entorno Planificado vs Real</h2>';
$html .= '<div class="chart"><img src="'.$url2.'" alt="Gráfico Entorno"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Entorno Planificado</td><td>'.number_format($total_entorno_plan, 0, ',', '.').'</td></tr>';
$html .= '<tr><td>Entorno Real</td><td>'.number_format($total_entorno_real, 0, ',', '.').'</td></tr>';
$html .= '</tbody></table>';
$html .= '<div class="page-break"></div>';

$html .= '<h2>Monto Realizado vs No Realizado</h2>';
$html .= '<div class="chart"><img src="'.$url3.'" alt="Gráfico Monto"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Monto Realizado</td><td>'.number_format($monto_realizadas, 0, ',', '.').'</td></tr>';
$html .= '<tr><td>Monto No Realizado</td><td>'.number_format($monto_no_realizadas, 0, ',', '.').'</td></tr>';
$html .= '<tr><td><strong>Total Monto</strong></td><td><strong>'.number_format($total_monto, 0, ',', '.').'</strong></td></tr>';
$html .= '</tbody></table>';
$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Footer con fecha/hora
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
