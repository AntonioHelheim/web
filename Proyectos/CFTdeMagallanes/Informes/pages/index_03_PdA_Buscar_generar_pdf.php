<?php
if (ob_get_length()) { ob_end_clean(); } // limpia cualquier buffer previo
ob_start(); // empezamos el buffer que luego capturaremos en $html
require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
require 'db.php';

// Establecer zona horaria de Punta Arenas
date_default_timezone_set('America/Punta_Arenas');
$fechaHora = date('d-m-Y H:i');

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");

//Actividades Realizadas (id de estado 187)
$sql = "SELECT 
A.id, 
A.field_496 AS actividad,
DATE_FORMAT(FROM_UNIXTIME(A.field_501), '%d/%m/%Y') AS FecEjec,
A.field_505 AS Monto, 
A.field_506 AS FuenteFinanciamiento, 
A.field_508 AS Meta, 
A.field_529 AS EntornoReal, 
B.name AS Unidad, 
C.name AS Eje,
D.name AS Mecanismo
FROM app_entity_41 A
INNER JOIN app_fields_choices B ON A.field_493 = B.id
INNER JOIN app_fields_choices C ON A.field_494 = C.id
INNER JOIN app_fields_choices D ON A.field_495 = D.id
INNER JOIN app_fields_choices E ON A.field_498 = E.id
WHERE E.id = 187
ORDER BY A.field_499";

$result = $conn->query($sql);

$html = '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  @page { size: A4 landscape; margin: 10mm; }
  body { font-family: Arial, sans-serif; font-size: 8px; margin: 0; padding: 0; }
  h2 { text-align: center; font-size: 12px; margin: 0 0 8px 0; }
  table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  th, td { border: 1px solid #444; padding: 4px; font-size: 8px; vertical-align: top; word-wrap: break-word; }
  th { background-color: #eee; text-align: center; }
  tr { page-break-inside: avoid !important; }
  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }
  .center { text-align: center; }
  .footer { font-size: 8px; margin-top: 25px; text-align: right; font-style: italic; }
  .bold { font-weight: bold; background-color: #ddd; }
</style>
</head>
<body>
<h2>Listado actualizado de actividades realizadas ordenadas por fecha de planificación</h2>';

if ($result && $result->num_rows > 0) {
    // Inicializar sumatorias
    $totalEntornoReal = 0;
    $totalMonto = 0;

    $html .= '<table><thead><tr>
        <th style="width:2%;">ID</th>
        <th style="width:20%;">Actividad</th>
        <th class="center" style="width:9%;">Unidad</th>
        <th class="center" style="width:5%;">Fec. Ejec.</th>
        <th class="center" style="width:6%;">Entorno Real</th>
        <th class="center" style="width:6%;">Monto</th>
        <th class="center" style="width:7%;">Financiamiento</th>
        <th style="width:19%;">Meta</th>
        <th class="center" style="width:10%;">Eje</th>
        <th style="width:10%;">Mecanismo</th>
    </tr></thead><tbody>';

    while ($row = $result->fetch_assoc()) {
        // Sumar valores numéricos (considerando posibles nulos o vacíos)
        $totalEntornoReal += is_numeric($row['EntornoReal']) ? (float)$row['EntornoReal'] : 0;
        $totalMonto += is_numeric($row['Monto']) ? (float)$row['Monto'] : 0;

        $html .= '<tr>
            <td>' . $row['id'] . '</td>
            <td>' . htmlspecialchars($row['actividad']) . '</td>
            <td class="center">' . htmlspecialchars($row['Unidad']) . '</td>
            <td class="center">' . $row['FecEjec'] . '</td>
            <td class="center">' . htmlspecialchars($row['EntornoReal']) . '</td>
            <td class="center">' . number_format($row['Monto'], 0, ',', '.') . '</td>
            <td class="center">' . htmlspecialchars($row['FuenteFinanciamiento']) . '</td>
            <td>' . htmlspecialchars($row['Meta']) . '</td>
            <td class="center">' . htmlspecialchars($row['Eje']) . '</td>
            <td>' . htmlspecialchars($row['Mecanismo']) . '</td>
        </tr>';
    }

    // Agregar fila resumen al final
    $html .= '<tr class="bold">
        <td colspan="4" class="center">TOTAL</td>
        <td class="center">' . number_format($totalEntornoReal, 0, ',', '.') . '</td>
        <td class="center">' . number_format($totalMonto, 0, ',', '.') . '</td>
        <td colspan="4"></td>
    </tr>';

    $html .= '</tbody></table>';

} else {
    $html .= '<p style="text-align:center;">No se encontraron actividades.</p>';
}

$html .= '<div class="footer">Documento generado automáticamente por el sistema institucional <a href="https://proyectos.cftdemagallanes.cl/" target="_blank">https://proyectos.cftdemagallanes.cl/</a></div>';
$html .= '<div class="footer">Para mayor información dirigirse a <a href="https://proyectos.cftdemagallanes.cl/Informes/index.html" target="_blank">https://proyectos.cftdemagallanes.cl/Informes/index.html</a></div>';
$html .= '</body></html>';

$conn->close();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$canvas->page_script(function($pageNumber, $pageCount, $canvas, $fontMetrics) use ($fechaHora) {
    $font = $fontMetrics->getFont("Arial", "normal");
    $text = "Página $pageNumber de $pageCount - Generado: $fechaHora (UTC-3)";
    $width = $fontMetrics->getTextWidth($text, $font, 8);
    $x = $canvas->get_width() - $width - 20;
    $y = $canvas->get_height() - 20;
    $canvas->text($x, $y, $text, $font, 8);
});

$dompdf->stream("Listado_Actividades.pdf", ["Attachment" => false]);
exit;
