<?php
if (ob_get_length()) { ob_end_clean(); } // limpia cualquier buffer previo
ob_start(); // empezamos el buffer que luego capturaremos en $html

// Establecer zona horaria de Punta Arenas
date_default_timezone_set('America/Punta_Arenas');
$fechaHora = date('d-m-Y H:i');

// ===== Autoload Dompdf =====
$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) { $autoload = __DIR__ . '/../vendor/autoload.php'; }
if (!is_file($autoload)) { $autoload = __DIR__ . '/dompdf/autoload.inc.php'; }
if (!is_file($autoload)) { die('❌ No se encontró el autoloader de Dompdf.'); }
require_once $autoload;

// ===== Conexión BD =====
// Si ya tienes db.php, puedes usarlo en vez de este bloque.
require __DIR__ . '/db.php';
// $host = 'localhost';
// $username = 'unnffbpk_cft23';
// $password = '4S1)Xp60]4';
// $dbname = 'unnffbpk_proyectos';
// $port = 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_errno) {
  die("Error de conexión MySQL: " . htmlspecialchars($conn->connect_error));
}
$conn->set_charset("utf8");

// ===== Consultas =====
// NO realizadas
$sql_no = "
SELECT 
 A.id, 
 A.field_496 AS actividad,
 DATE_FORMAT(FROM_UNIXTIME(A.field_499), '%d/%m/%Y') as FecPlan,
 DATE_FORMAT(FROM_UNIXTIME(A.field_501), '%d/%m/%Y') AS FecEjec,
 A.field_505 AS Monto, 
 A.field_506 AS FuenteFinanciamiento, 
 A.field_508 AS Meta, 
 A.field_526 AS EntornoEsperado, 
 A.field_529 AS EntornoReal, 
 B.name AS Unidad, 
 C.name AS Eje,
 D.name AS Mecanismo,
 E.name AS Estado
FROM app_entity_41 A
INNER JOIN app_fields_choices B ON A.field_493 = B.id
INNER JOIN app_fields_choices C ON A.field_494 = C.id
INNER JOIN app_fields_choices D ON A.field_495 = D.id
INNER JOIN app_fields_choices E ON A.field_498 = E.id
WHERE E.id != 187
ORDER BY A.field_499";
$res_no = $conn->query($sql_no);

// REALIZADAS
$sql_si = "
SELECT 
 A.id, 
 A.field_496 AS actividad,
 DATE_FORMAT(FROM_UNIXTIME(A.field_499), '%d/%m/%Y') as FecPlan,
 DATE_FORMAT(FROM_UNIXTIME(A.field_501), '%d/%m/%Y') AS FecEjec,
 A.field_505 AS Monto, 
 A.field_506 AS FuenteFinanciamiento, 
 A.field_508 AS Meta,
 A.field_526 AS EntornoEsperado, 
 A.field_529 AS EntornoReal, 
 B.name AS Unidad, 
 C.name AS Eje,
 D.name AS Mecanismo, 
 E.name AS Estado
FROM app_entity_41 A
INNER JOIN app_fields_choices B ON A.field_493 = B.id
INNER JOIN app_fields_choices C ON A.field_494 = C.id
INNER JOIN app_fields_choices D ON A.field_495 = D.id
INNER JOIN app_fields_choices E ON A.field_498 = E.id
WHERE E.id = 187
ORDER BY A.field_501";
$res_si = $conn->query($sql_si);

// ===== Armar HTML =====
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  @page { size: A4 landscape; margin: 12mm; }
  body { font-family: Arial, sans-serif; font-size: 8px; margin: 0; padding: 0; }
  h2 { text-align: center; font-size: 12px; margin: 0 0 8px 0; }
  table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  th, td { border: 1px solid #444; padding: 4px; font-size: 8px; vertical-align: top; word-wrap: break-word; }
  th { background-color: #eee; text-align: center; }
  thead { display: table-header-group; }
  tr { page-break-inside: avoid !important; }
  .center { text-align: center; }
  .bold { font-weight: bold; background-color: #ddd; }
  .section-break { page-break-before: always; }
</style>
</head>
<body>

<?php
// ===================== SECCIÓN: Portada =====================================
echo "
<style>
    .portada-container {
        width: 100%;
        height: 100vh;
        display: table;
        background: linear-gradient(to bottom right, #f9f9f9, #eeeeee);
        font-family: DejaVu Sans, sans-serif;
        padding: 150px;
        margin: 0;
    }
    .portada-content {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }
    .portada-box {
        display: inline-block;
        background-color: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 700px;
        width: 90%;
        margin: 0 auto;
    }
    .portada-title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #b30404;
        border-bottom: 3px solid #b30404;
        padding-bottom: 10px;
    }
    .portada-subtitle {
        font-size: 18px; 
        margin: 14px 0;
        color: #333;
    }
    .portada-link {
        color: #004488;
        text-decoration: underline;
        font-weight: normal;
        word-break: break-word;
    }
    .portada-footer {
        font-size: 10px;
        color: #777;
        margin-top: 40px;
    }
</style>

<div class='portada-container'>
    <div class='portada-content'>
        <div class='portada-box'>
            <div class='portada-title'>Informe de Plan de actividades</div>
            <div class='portada-subtitle'>Centro de Formación Técnica de Magallanes</div>

            <div class='portada-subtitle'>Sitio web del portal de informes:</div>
            <div class='portada-link'>
                <a href='https://proyectos.cftdemagallanes.cl/Informes/index.html' target='_blank'>
                    https://proyectos.cftdemagallanes.cl/Informes/index.html
                </a>
            </div>

            <div class='portada-subtitle'>Sitio web del portal del Sistema de Gestión:</div>
            <div class='portada-link'>
                <a href='https://proyectos.cftdemagallanes.cl/' target='_blank'>
                    https://proyectos.cftdemagallanes.cl/
                </a>
            </div>

        </div>
    </div>
</div>

<div style='page-break-after: always;'></div>
";
?>

<!-- ===== SECCIÓN 1: NO REALIZADAS ===== -->
<h2>Listado actualizado de actividades NO realizadas ordenadas por fecha de planificación</h2>
<?php if ($res_no && $res_no->num_rows > 0): ?>
  <?php $totalEntornoPlan = 0; $totalMontoNo = 0; ?>
  <table>
    <thead>
      <tr>
        <th style="width:4%;">ID</th>
        <th style="width:22%;">Actividad</th>
        <th class="center" style="width:9%;">Estado</th>
        <th class="center" style="width:8%;">FecPlan</th>
        <th class="center" style="width:8%;">Entorno Esperado</th>
        <th class="center" style="width:10%;">Monto</th>
        <th class="center" style="width:9%;">Fuente Financ.</th>
        <th style="width:19%;">Meta</th>
        <th class="center" style="width:10%;">Eje</th>
        <th style="width:10%;">Mecanismo</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $res_no->fetch_assoc()):
        $totalEntornoPlan += is_numeric($row['EntornoEsperado']) ? (float)$row['EntornoEsperado'] : 0;
        $totalMontoNo     += is_numeric($row['Monto']) ? (float)$row['Monto'] : 0;
      ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['actividad']) ?></td>
        <td class="center"><?= htmlspecialchars($row['Estado']) ?></td>
        <td class="center"><?= $row['FecPlan'] ?></td>
        <td class="center"><?= htmlspecialchars($row['EntornoEsperado']) ?></td>
        <td class="center"><?= number_format((float)$row['Monto'], 0, ',', '.') ?></td>
        <td class="center"><?= htmlspecialchars($row['FuenteFinanciamiento']) ?></td>
        <td><?= htmlspecialchars($row['Meta']) ?></td>
        <td class="center"><?= htmlspecialchars($row['Eje']) ?></td>
        <td><?= htmlspecialchars($row['Mecanismo']) ?></td>
      </tr>
      <?php endwhile; ?>
      <tr class="bold">
        <td colspan="4" class="center">TOTAL</td>
        <td class="center"><?= number_format($totalEntornoPlan, 0, ',', '.') ?></td>
        <td class="center"><?= number_format($totalMontoNo, 0, ',', '.') ?></td>
        <td colspan="4"></td>
      </tr>
    </tbody>
  </table>
<?php else: ?>
  <p>No se encontraron actividades NO realizadas.</p>
<?php endif; ?>

<div class="section-break"></div>

<!-- ===== SECCIÓN 2: REALIZADAS ===== -->
<h2>Listado actualizado de actividades realizadas ordenadas por fecha de planificación</h2>
<?php if ($res_si && $res_si->num_rows > 0): ?>
  <?php $totalEntornoReal = 0; $totalMontoSi = 0; ?>
  <table>
    <thead>
      <tr>
        <th style="width:4%;">ID</th>
        <th style="width:22%;">Actividad</th>
        <th class="center" style="width:9%;">Estado</th>
        <th class="center" style="width:8%;">FecPlan</th>
        <th class="center" style="width:8%;">FecEjec</th>
        <th class="center" style="width:8%;">Entorno Esperado</th>
        <th class="center" style="width:8%;">Entorno Real</th>
        <th class="center" style="width:10%;">Monto</th>
        <th class="center" style="width:9%;">Fuente Financ.</th>
        <th style="width:19%;">Meta</th>
        <th class="center" style="width:10%;">Eje</th>
        <th style="width:10%;">Mecanismo</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $res_si->fetch_assoc()):
        $totalEntornoReal += is_numeric($row['EntornoReal']) ? (float)$row['EntornoReal'] : 0;
        $totalMontoSi     += is_numeric($row['Monto']) ? (float)$row['Monto'] : 0;
      ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['actividad']) ?></td>
        <td class="center"><?= htmlspecialchars($row['Estado']) ?></td>
        <td class="center"><?= $row['FecPlan'] ?></td>
        <td class="center"><?= $row['FecEjec'] ?></td>
        <td class="center"><?= htmlspecialchars($row['EntornoEsperado']) ?></td>
        <td class="center"><?= htmlspecialchars($row['EntornoReal']) ?></td>
        <td class="center"><?= number_format((float)$row['Monto'], 0, ',', '.') ?></td>
        <td class="center"><?= htmlspecialchars($row['FuenteFinanciamiento']) ?></td>
        <td><?= htmlspecialchars($row['Meta']) ?></td>
        <td class="center"><?= htmlspecialchars($row['Eje']) ?></td>
        <td><?= htmlspecialchars($row['Mecanismo']) ?></td>
      </tr>
      <?php endwhile; ?>
      <tr class="bold">
        <td colspan="6" class="center">TOTAL</td>
        <td class="center"><?= number_format($totalEntornoReal, 0, ',', '.') ?></td>
        <td class="center"><?= number_format($totalMontoSi, 0, ',', '.') ?></td>
        <td colspan="4"></td>
      </tr>
    </tbody>
  </table>
<?php else: ?>
  <p>No se encontraron actividades realizadas.</p>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();

// ===== Configurar Dompdf y renderizar =====
$options = new \Dompdf\Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->setChroot(__DIR__);

$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// ===== Footer en TODAS las páginas excepto la portada (página 1) =====
$canvas = $dompdf->getCanvas();
$fontMetrics = $dompdf->getFontMetrics();
$font = $fontMetrics->getFont('Arial', 'normal');
$size = 8;
$logoPath = __DIR__ . '/LogoCFT.png'; // tu logo en este directorio
$fechaHoraTexto = $fechaHora;

$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($font, $size, $fechaHoraTexto, $logoPath) {
    if ($pageNumber === 1) { return; } // <-- SIN footer ni logo en la portada

    $pageWidth  = $canvas->get_width();
    $pageHeight = $canvas->get_height();

    // Texto: número de página y timestamp (derecha inferior)
    $text = "Página $pageNumber de $pageCount - Generado: $fechaHoraTexto (UTC-3)";
    $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
    $xText = $pageWidth - $textWidth - 20;
    $yText = $pageHeight - 20;
    $canvas->text($xText, $yText, $text, $font, $size);

    // Logo: izquierda inferior
    if (is_file($logoPath)) {
        $canvas->image($logoPath, 20, $pageHeight - 35, 65, 25);
    }
});

// ===== Mostrar PDF =====
$dompdf->stream('Listado_Actividades_Total.pdf', ['Attachment' => false]);
exit;
