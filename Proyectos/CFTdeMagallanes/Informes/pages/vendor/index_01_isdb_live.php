<?php
if (ob_get_length()) ob_end_clean();
require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

require 'db.php'; 

date_default_timezone_set('America/Punta_Arenas');
$fechaHora = date('d-m-Y H:i');

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
$conn->set_charset('utf8');

// ============================================================================
// ===================== SECCIÓN: Portada =====================================
$html .= "
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
            <div class='portada-title'>Informe Bimensual de Proyectos</div>
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

            <div class='portada-footer'>Documento generado el: $fechaHora (hora local Punta Arenas-Porvenir)</div>
        </div>
    </div>
</div>


<div style='page-break-after: always;'></div>
";


// ============================================================================
// ===================== SECCIÓN: Estado de los proyectos =====================
$sql = "
    SELECT  
        a.field_288 AS NombreProyecto,
        b.name AS EstadoProyecto,
        d.name AS Unidad,
        a.field_697 AS PorcentajeAvance,
        a.field_294 AS UltimoSprint, 
        c.name AS InformeMineduc,
        a.field_379 AS RutaCritica, 
        a.field_374 AS Aprobado, 
        a.field_394 AS Gastado, 
        a.field_395 AS Comprometido
    FROM app_entity_30 AS a
    INNER JOIN app_fields_choices AS b ON a.field_293 = b.id
    LEFT JOIN app_fields_choices AS c ON a.field_377 = c.id
    LEFT JOIN app_access_groups AS d ON a.field_290 = d.id
    WHERE a.field_293 NOT IN ('95', '100')
    ORDER BY Unidad ASC, NombreProyecto ASC
";

$result = $conn->query($sql);

// ========= Renderizado tabla HTML ======
$html .= "
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 14px;
        background-color: #f7f7f7;
    }

    table.estado-proyectos {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
        border: 1px solid #ccc;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    table.estado-proyectos th, table.estado-proyectos td {
        padding: 10px 14px;
        text-align: center;
        border-bottom: 1px solid #e0e0e0;
    }

    table.estado-proyectos th {
        background-color: #b30404;
        color: #fff;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    table.estado-proyectos tr:last-child td {
        border-bottom: none;
    }

    table.estado-proyectos tbody tr:hover {
        background-color: #f0f0f0;
    }

    table.estado-proyectos td {
        background-color: #fff;
        color: #333;
    }

    h2 {
        margin-top: 40px;
        color: #333;
        font-size: 20px;
        border-left: 6px solid #b30404;
        padding-left: 12px;
    }
</style>


<h2>Estado de los Proyectos según información cargada en el Sistema de Gestión</h2>
<p></p>
<table class='estado-proyectos'>
    <thead>
        <tr>
            <th>Nombre del Proyecto</th>
            <th>Estado</th>
            <th>Unidad</th>
            <th>Avance</th>
            <th>Informe Mineduc - Enero 2025</th>
        </tr>
    </thead>
    <tbody>
";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // === Determinar color según estado del proyecto ===
        $estado = trim($row['EstadoProyecto']);
        $colorEstado = match ($estado) {
            'Sin estado informado'     => '#FFEB3B',
            'No iniciado - Al día'     => '#00796B',
            'No iniciado - Retraso'    => '#FF0000',
            'En curso - Al día'        => '#00796B',
            'En curso - Retraso'       => '#FF0000',
            'Logrado'                  => '#0288D1',
            'Cancelado'                => '#0288D1',
            'En Pausa'                 => '#B3E5FC',
            'Cerrado'                  => 'Purple',
            default                    => '#FFFFFF',
        };

        $html .= "<tr>
            <td>{$row['NombreProyecto']}</td>
            <td style='background-color: {$colorEstado};'>{$row['EstadoProyecto']}</td>
            <td>{$row['Unidad']}</td>
            <td>{$row['PorcentajeAvance']}%</td>
            <td>{$row['InformeMineduc']}</td>
        </tr>";
    }
} else {
    $html .= "<tr><td colspan='5'><i>No se encontraron proyectos registrados.</i></td></tr>";
}

$html .= "</tbody></table>";



// ============================================================================
// ============= SECCIÓN: Proyectos a detalle hasta sus Objetivos e Hitos =====

$html .= '<div style="page-break-before: always;"></div>';

$html .= '<style>
    body {
        font-family: DejaVu Sans, sans-serif;
    }

    .titulo-proyecto {
        background-color: #b30404;
        color: #fff;
        padding: 12px 16px;
        font-weight: bold;
        font-size: 18px;
        margin-top: 30px;
        border-radius: 12px 12px 0 0;
    }

    .tabla-proyecto {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 0;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }

    .tabla-proyecto td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
        border: none;
    }

    .tabla-proyecto tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .tabla-proyecto tr:first-child td {
        background-color: #f1f1f1;
        font-weight: bold;
    }

    .objetivo-container {
        border: 1px solid #e1e1e1;
        border-radius: 8px;
        margin-top: 16px;
        padding: 16px;
        background-color: #fafafa;
    }

    .hito-table {
        width: 100%;
        margin-top: 12px;
        border-collapse: collapse;
        font-size: 13px;
        border-top: 1px solid #ccc;
    }

    .hito-table td, .hito-table th {
        padding: 8px 10px;
        text-align: left;
        vertical-align: top;
        border: none;
    }

    .hito-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .estado-badge {
        display: inline-block;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 10px;
        color: white;
    }
</style>';

$html .= '<h2>Ficha en detalle de los proyectos hasta sus objetivos</h2>';

// ===================== CONSULTA =====================
$sql_tercera = "
SELECT 
    p.field_288 AS NombreProyecto,
    ep.name AS EstadoProyecto,
    ag.name AS Unidad,
    p.field_697 as PorcentajeAvance,
    p.field_294 AS UltimoSprint, 
    im.name AS InformeMineduc,
    p.field_379 AS RutaCritica, 
    p.field_374 AS Aprobado, 
    p.field_394 AS Gastado, 
    p.field_395 AS Comprometido,
    o.field_303 as Objetivo,
    eo.name as EstadoObjetivo,
    a.field_322 as Hito, 
    b.name as EstadoHito
FROM app_entity_30 AS p
INNER JOIN app_fields_choices AS ep ON p.field_293 = ep.id
LEFT JOIN app_fields_choices AS im ON p.field_377 = im.id
LEFT JOIN app_access_groups AS ag ON p.field_290 = ag.id
LEFT JOIN app_entity_32 AS o ON o.parent_item_id = p.id
LEFT JOIN app_fields_choices AS eo ON o.field_307 = eo.id
LEFT JOIN app_entity_33 as a ON a.parent_item_id = o.id
LEFT JOIN app_fields_choices as b ON a.field_329 = b.id
WHERE p.field_293 NOT IN ('95', '100')
ORDER BY Unidad ASC, NombreProyecto ASC;
";

$res_tercera = $conn->query($sql_tercera);

// Función para determinar color por estado
function colorEstado($estado) {
    return match (trim($estado)) {
        'Sin estado informado'     => '#FFEB3B',
        'No iniciado - Al día'     => '#00796B',
        'No iniciado - Retraso'    => '#FF0000',
        'En curso - Al día'        => '#00796B',
        'En curso - Retraso'       => '#FF0000',
        'Logrado'                  => '#0288D1',
        'Cancelado'                => '#0288D1',
        'En Pausa'                 => '#B3E5FC',
        'Cerrado'                  => 'Purple',
        default                    => '#888'
    };
}

$agrupado = [];
while ($row = $res_tercera->fetch_assoc()) {
    $proyecto = $row['NombreProyecto'] ?? 'Sin Proyecto';
    $objetivo = $row['Objetivo'];

    if (!isset($agrupado[$proyecto])) {
        $agrupado[$proyecto] = [
            'info' => [
                'Estado' => $row['EstadoProyecto'],
                'Unidad' => $row['Unidad'],
                'Avance' => $row['PorcentajeAvance'],
                'Sprint' => $row['UltimoSprint'],
                'InformeMineduc' => $row['InformeMineduc'],
                'RutaCritica' => $row['RutaCritica'],
                'Aprobado' => $row['Aprobado'],
                'Gastado' => $row['Gastado'],
                'Comprometido' => $row['Comprometido'],
            ],
            'objetivos' => []
        ];
    }

    if ($objetivo) {
        if (!isset($agrupado[$proyecto]['objetivos'][$objetivo])) {
            $agrupado[$proyecto]['objetivos'][$objetivo] = [
                'estado' => $row['EstadoObjetivo'],
                'hitos' => []
            ];
        }

        if (!empty($row['Hito'])) {
            $agrupado[$proyecto]['objetivos'][$objetivo]['hitos'][] = [
                'nombre' => $row['Hito'],
                'estado' => $row['EstadoHito']
            ];
        }
    }
}

foreach ($agrupado as $nombre => $data) {
    static $primero = true;
    if ($primero) {
        $primero = false;
    } else {
        $html .= '<div style="page-break-before: always;"></div>';
    }

    $info = $data['info'];
    $colorEstadoProyecto = colorEstado($info['Estado']);

    $html .= "<div class='titulo-proyecto'>Proyecto: $nombre</div>";
    $html .= "<table class='tabla-proyecto'>
        <tr>
            <td><b>Estado</b></td>
            <td style='background-color: {$colorEstadoProyecto};'>{$info['Estado']}</td>
            <td><b>Avance</b></td><td>{$info['Avance']}%</td>
            <td><b>Informe Mineduc</b></td><td>{$info['InformeMineduc']}</td>
        </tr>
        <tr>
            <td><b>Unidad</b></td><td>{$info['Unidad']}</td>
            <td><b>Ruta Crítica</b></td><td colspan='3'>{$info['RutaCritica']}</td>
        </tr>
        <tr>
            <td><b>Notas del Último Sprint</b></td><td colspan='5'>{$info['Sprint']}</td>
        </tr>
        <tr>
            <td><b>Aprobado</b></td><td>{$info['Aprobado']}</td>
            <td><b>Gastado</b></td><td>{$info['Gastado']}</td>
            <td><b>Comprometido</b></td><td>{$info['Comprometido']}</td>
        </tr>
    </table>";

    foreach ($data['objetivos'] as $objetivo => $detalle) {
        $colorEstadoObjetivo = colorEstado($detalle['estado']);
        $html .= "<div class='objetivo-container'>
                    <div style='display: flex; justify-content: space-between; align-items: center;'>
                        <h4 style='margin: 0;'>$objetivo</h4>
                        <span class='estado-badge' style='background-color: {$colorEstadoObjetivo};'>" . ($detalle['estado'] ?? 'Desconocido') . "</span>
                    </div>";

        if (!empty($detalle['hitos'])) {
            $html .= "<table class='hito-table'>
                        <thead><tr><th>Estado del Hito/Indicador</th><th>Hito / Indicador</th></tr></thead><tbody>";
            foreach ($detalle['hitos'] as $hito) {
                $estadoHito = $hito['estado'] ?? 'Sin Estado';
                $colorEstadoHito = colorEstado($estadoHito);
                $html .= "<tr>
                            <td><span class='estado-badge' style='background-color: {$colorEstadoHito};'>$estadoHito</span></td>
                            <td>{$hito['nombre']}</td>
                        </tr>";
            }
            $html .= "</tbody></table>";
        } else {
            $html .= "<i>Este objetivo no tiene hitos registrados.</i>";
        }

        $html .= "</div>";
    }
}

// ==========END SECCIÓN: Proyectos a detalle hasta sus Objetivos e Hitos =====
// ============================================================================


// ============================================================================
// ===================== SECCIÓN: Cierre =====================================
$html .= "
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
            <div class='portada-title'>Informe Bimensual de Proyectos</div>
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

            <div class='portada-subtitle'>Gracias por su tiempo y atencion</div>

            <div class='portada-footer'>Documento generado el: $fechaHora (hora local Punta Arenas-Porvenir)</div>
        </div>
    </div>
</div>


";




// Generar PDF en orientación horizontal A4
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml($html);
$dompdf->render();

// Agregar pie de página con numeración
$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont("DejaVu Sans", "normal");
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($font) {
    $text = "Página $pageNumber de $pageCount";
    $size = 10;
    $width = $fontMetrics->getTextWidth($text, $font, $size);

    // Coordenadas ajustadas para A4 landscape (842 x 595 puntos)
    $x = 770 - $width; // margen derecho
    $y = 575; // margen inferior visible
    $canvas->text($x, $y, $text, $font, $size);
});



// Descargar el archivo
$dompdf->stream("Estado_Proyectos_$fechaHora.pdf", array("Attachment" => false));
exit;
?>

