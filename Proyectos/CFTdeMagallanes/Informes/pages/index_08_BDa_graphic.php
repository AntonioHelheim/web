<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Establecer zona horaria
date_default_timezone_set('America/Punta_Arenas');
$fechaHora = date('d-m-Y H:i');

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4') or die('Error al establecer charset utf8mb4: ' . $conn->error);

// Consulta MySQL, incluyendo b.id para mapeo de colores
$sql = <<<'SQL'
SELECT
    b.id    AS id_b,
    b.name  AS nombre_b,
    c.name  AS nombre_c,
    COUNT(*) AS total_registros
FROM app_entity_44 AS a
INNER JOIN app_fields_choices AS b ON a.field_599 = b.id
INNER JOIN app_fields_choices AS c ON a.field_593 = c.id
GROUP BY b.id, b.name, c.name
ORDER BY c.name ASC, b.name ASC, total_registros DESC;
SQL;

$result = $conn->query($sql);
if (! $result) {
    die('Error en la consulta: ' . $conn->error);
}

// Preparar arrays de datos y colores según b.id
// Definición de colores
define('COLOR_330', '#32CD32');   // verde limón
define('COLOR_331', '#006400');   // verde oscuro
define('COLOR_332', '#FFFF00');   // amarillo
define('COLOR_351', '#FFFF00');   // amarillo
define('COLOR_333', '#0000FF');   // azul

$labels = [];
$data   = [];
$colors = [];
$rows   = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['nombre_c'] . ' - ' . $row['nombre_b'];
    $data[]   = (int) $row['total_registros'];
    $rows[]   = $row;
    // Asignar color según b.id
    switch ((int)$row['id_b']) {
        case 330:
            $colors[] = COLOR_330;
            break;
        case 331:
            $colors[] = COLOR_331;
            break;
        case 332:
        case 351:
            $colors[] = COLOR_332;
            break;
        case 333:
            $colors[] = COLOR_333;
            break;
        default:
            $colors[] = '#CCCCCC'; // gris por defecto
    }
}
$result->free();
$conn->close();

// Helper QuickChart con mayor resolución
define('CHART_BASE', 'https://quickchart.io/chart?format=png&width=1200&height=600&c=');
function quickchart_url($config) {
    return CHART_BASE . urlencode(json_encode($config));
}

// Configuración del gráfico con colores personalizados
$chartConfig = [
    'type' => 'bar',
    'data' => [
        'labels'    => $labels,
        'datasets'  => [[
            'label'           => 'Nuevas matriculas',
            'data'            => $data,
            'backgroundColor' => $colors
        ]]
    ],
    'options' => [
        'plugins' => [
            'datalabels' => [ 'display' => true, 'align' => 'top', 'anchor' => 'end', 'font' => ['size' => 10] ]
        ],
        'scales' => [
            'x' => [ 'ticks' => [ 'font' => ['size' => 8], 'autoSkip' => false, 'maxRotation' => 90, 'minRotation' => 45 ] ],
            'y' => [ 'beginAtZero' => true, 'ticks' => [ 'precision' => 0, 'font' => ['size' => 8] ] ]
        ]
    ]
];
$chartUrl = quickchart_url($chartConfig);

// Inicializar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

// Construir HTML con salto de página
$html = '<!DOCTYPE html>'
      . '<html lang="es"><head><meta charset="UTF-8"><style>'
      . '@page { margin: 10mm; }'
      . 'body { font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 12px; }'
      . 'h2 { text-align: center; margin: 10px 0; }'
      . '.chart img { display: block; width: 100%; height: auto; }'
      . 'table { width: 100%; border-collapse: collapse; margin: 10px 0; }'
      . 'th, td { border: 1px solid #555; padding: 6px; font-size: 10px; }'
      . 'th { background: #f0f0f0; }'
      . '</style></head><body>';

// Página 1: Gráfico completo
$html .= '<h2>Gráfico matriculas por estado y carrera - 2025</h2>';
$html .= '<div class="chart"><img src="' . $chartUrl . '" alt="Gráfico de Registros"></div>';
$html .= '<div style="page-break-after: always;"></div>';

// Página 2: Tabla de datos
$html .= '<h2>Detalle de Registros</h2>';
$html .= '<table><thead><tr><th>Carrera</th><th>Tipo de matricula</th><th>Total Registros</th></tr></thead><tbody>';
foreach ($rows as $r) {
    $html .= '<tr>'
           . '<td>' . htmlspecialchars($r['nombre_c'], ENT_QUOTES, 'UTF-8') . '</td>'
           . '<td>' . htmlspecialchars($r['nombre_b'], ENT_QUOTES, 'UTF-8') . '</td>'
           . '<td>' . $r['total_registros'] . '</td>'
           . '</tr>';
}
$html .= '</tbody></table>';
$html .= '</body></html>';

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Añadir pie de página con numeración
global $fechaHora;
$canvas = $dompdf->getCanvas();
$fm     = $dompdf->getFontMetrics();
$font   = $fm->getFont('Helvetica', 'normal');
$size   = 8;
$text   = "Página {PAGE_NUM} de {PAGE_COUNT} - Generado: $fechaHora";
$x      = ($canvas->get_width() - $fm->getTextWidth($text, $font, $size)) / 2;
$y      = $canvas->get_height() - 15;
$canvas->page_text($x, $y, $text, $font, $size);

// Salida del PDF al navegador
$dompdf->stream('Registros_Grafico.pdf', ['Attachment' => false]);
exit;