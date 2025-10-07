<?php
if (ob_get_length()) ob_end_clean();
require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
require 'db.php';

// Establecer zona horaria
date_default_timezone_set('America/Punta_Arenas');
$fechaHora = date('d-m-Y H:i');

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
$conn->set_charset('utf8');

// Función para obtener un valor escalar de una consulta
function fetch_scalar($conn, $sql) {
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return $row['total'] ?? 0;
    }
    return 0;
}

// Definición de consultas SQL
$sql_realizadas       = "SELECT COUNT(*) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id = 187";
$sql_no_realizadas    = "SELECT COUNT(*) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id != 187";
$sql_entorno_real     = "SELECT SUM(A.field_529) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id = 187";
$sql_entorno_plan     = "SELECT SUM(A.field_526) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id != 187";
$sql_monto_realizadas = "SELECT SUM(A.field_505) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id = 187";
$sql_monto_no_real    = "SELECT SUM(A.field_505) AS total FROM app_entity_41 A INNER JOIN app_fields_choices E ON A.field_498 = E.id WHERE E.id != 187";

// Recuperar datos
$total_realizadas     = (int) fetch_scalar($conn, $sql_realizadas);
$total_no_realizadas  = (int) fetch_scalar($conn, $sql_no_realizadas);
$total_entorno_real   = (float) fetch_scalar($conn, $sql_entorno_real);
$total_entorno_plan   = (float) fetch_scalar($conn, $sql_entorno_plan);
$monto_realizadas     = (float) fetch_scalar($conn, $sql_monto_realizadas);
$monto_no_realizadas  = (float) fetch_scalar($conn, $sql_monto_no_real);

// Cálculo de totales generales
$total_actividades = $total_realizadas + $total_no_realizadas;
$total_monto       = $monto_realizadas + $monto_no_realizadas;

// Base de URL para QuickChart
define('CHART_BASE','https://quickchart.io/chart?format=png&c=');
function quickchart_url($config) {
    return CHART_BASE . urlencode(json_encode($config));
}

// Configuración de gráficos
$chart1 = [
    'type'=>'bar',
    'data'=>['labels'=>['Realizadas','No Realizadas','Total'],'datasets'=>[[
        'label'=>'Actividades',
        'data'=>[$total_realizadas,$total_no_realizadas,$total_actividades],
        'backgroundColor'=>['#4caf50','#f44336','#2196f3']
    ]]],
    'options'=>['plugins'=>['legend'=>['display'=>false],'datalabels'=>['anchor'=>'end','align'=>'top','color'=>'#000','font'=>['weight'=>'bold']]],'scales'=>['y'=>['beginAtZero'=>true,'ticks'=>['stepSize'=>5,'precision'=>0]]]]
];
$chart2 = [
    'type'=>'bar',
    'data'=>['labels'=>['Entorno Planificado','Entorno Real'],'datasets'=>[[
        'label'=>'Entorno',
        'data'=>[$total_entorno_plan,$total_entorno_real],
        'backgroundColor'=>['#ff9800','#00bcd4']
    ]]],
    'options'=>['plugins'=>['legend'=>['display'=>false],'datalabels'=>['anchor'=>'end','align'=>'top','color'=>'#000','font'=>['weight'=>'bold']]],'scales'=>['y'=>['beginAtZero'=>true,'ticks'=>['stepSize'=>5,'precision'=>0]]]]
];
$chart3 = [
    'type'=>'bar',
    'data'=>['labels'=>['Monto Realizado','Monto No Realizado','Total Monto'],'datasets'=>[[
        'label'=>'Monto',
        'data'=>[$monto_realizadas,$monto_no_realizadas,$total_monto],
        'backgroundColor'=>['#4caf50','#f44336','#2196f3']
    ]]],
    'options'=>['plugins'=>['legend'=>['display'=>false],'datalabels'=>['anchor'=>'end','align'=>'top','color'=>'#000','font'=>['weight'=>'bold']]],'scales'=>['y'=>['beginAtZero'=>true,'ticks'=>['stepSize'=>5,'precision'=>0]]]]
];

// Generar URLs de gráfico
$url1 = quickchart_url($chart1);
$url2 = quickchart_url($chart2);
$url3 = quickchart_url($chart3);

$conn->close();

// Crear documento PDF
$options = new Options();
$options->set('isHtml5ParserEnabled',true);
$options->set('isRemoteEnabled',true);
$options->set('defaultFont','Arial');
$dompdf = new Dompdf($options);

// Construir HTML con secciones por página
$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
  @page { margin:10mm; }
  body { font-family:Arial,sans-serif; margin:0; padding:0; font-size:12px; text-align:center; }
  h2 { margin:20px 0 10px; font-size:16px; }
  .chart img { width:100%; max-width:190mm; height:auto; margin:0 auto; }
  .table { width:100%; max-width:190mm; margin:10px auto; border-collapse:collapse; }
  .table th, .table td { border:1px solid #555; padding:8px; }
  .table th { background:#f0f0f0; }
  .page-break { page-break-after:always; display:block; }
</style></head><body>';

// Página 1: Actividades
$html .= '<h2>Actividades Realizadas vs No Realizadas</h2>';
$html .= '<div class="chart"><img src="'.$url1.'" alt="Gráfico Actividades"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Realizadas</td><td>'.$total_realizadas.'</td></tr>';
$html .= '<tr><td>No Realizadas</td><td>'.$total_no_realizadas.'</td></tr>';
$html .= '<tr><td><strong>Total Actividades</strong></td><td><strong>'.$total_actividades.'</strong></td></tr>';
$html .= '</tbody></table>';
$html .= '<div class="page-break"></div>';

// Página 2: Entorno
$html .= '<h2>Entorno Planificado vs Entorno Real</h2>';
$html .= '<div class="chart"><img src="'.$url2.'" alt="Gráfico Entorno"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Entorno Planificado</td><td>'.$total_entorno_plan.'</td></tr>';
$html .= '<tr><td>Entorno Real</td><td>'.$total_entorno_real.'</td></tr>';
$html .= '</tbody></table>';
$html .= '<div class="page-break"></div>';

// Página 3: Monto
$html .= '<h2>Monto Realizado vs Monto por Realizar</h2>';
$html .= '<div class="chart"><img src="'.$url3.'" alt="Gráfico Monto"></div>';
$html .= '<table class="table"><thead><tr><th>Tipo</th><th>Total</th></tr></thead><tbody>';
$html .= '<tr><td>Monto Realizado</td><td>'.number_format($monto_realizadas,0,',','.').'</td></tr>';
$html .= '<tr><td>Monto No Realizado</td><td>'.number_format($monto_no_realizadas,0,',','.').'</td></tr>';
$html .= '<tr><td><strong>Total Monto</strong></td><td><strong>'.number_format($total_monto,0,',','.').'</strong></td></tr>';
$html .= '</tbody></table>';
$html .= '</body></html>';

// Renderizar PDF\$_dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();

// Agregar footer dinámico con numeración de páginas
$canvas = $dompdf->getCanvas();
$canvas->page_script(function($pageNumber, $pageCount, $canvas, $fontMetrics) use ($fechaHora) {
    $font  = $fontMetrics->getFont("Arial","normal");
    $text  = "Página $pageNumber de $pageCount - Generado: $fechaHora (UTC-3)";
    $width = $fontMetrics->getTextWidth($text,$font,8);
    $x     = ($canvas->get_width() - $width) / 2;
    $y     = $canvas->get_height() - 10;
    $canvas->text($x,$y,$text,$font,8);
});

// Enviar PDF al navegador
$dompdf->stream("Resumen_Graficos_Actividades.pdf", ["Attachment"=>false]);
exit;
