<?php
/**
 * lib/pdf_certificado.php
 * Genera el PDF de un certificado de aprobación usando Dompdf (ya
 * instalado en php/vendor/, hasta ahora solo probado con un "hola
 * mundo" en php/db_dompdf.php — este es el punto donde se conecta al
 * flujo real).
 *
 * No imprime nada ni hace echo: guarda el PDF en disco y devuelve la
 * ruta relativa, para que el endpoint decida qué responder.
 */

require_once __DIR__ . '/../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Genera un código de verificación único y legible (no adivinable a
 * fuerza bruta en un rango razonable: 12 caracteres hex = 48 bits).
 */
function generarCodigoCertificado(): string
{
    return strtoupper(bin2hex(random_bytes(6)));
}

/**
 * Arma el HTML del certificado y lo renderiza a PDF con Dompdf.
 * Devuelve la ruta relativa al archivo generado (para guardar en
 * certificates.file_path), o lanza una excepción si algo falla — el
 * llamador decide cómo responder ese error, acá no se hace responderJSON.
 */
function generarCertificadoPdf(array $datos): string
{
    $nombreCompleto = htmlspecialchars($datos['nombre_trabajador'], ENT_QUOTES, 'UTF-8');
    $nombreCurso = htmlspecialchars($datos['nombre_curso'], ENT_QUOTES, 'UTF-8');
    $nombreEmpresa = htmlspecialchars($datos['nombre_empresa'], ENT_QUOTES, 'UTF-8');
    $codigo = htmlspecialchars($datos['codigo'], ENT_QUOTES, 'UTF-8');
    $fecha = htmlspecialchars($datos['fecha'], ENT_QUOTES, 'UTF-8');
    $porcentaje = htmlspecialchars((string) $datos['porcentaje'], ENT_QUOTES, 'UTF-8');

    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { margin: 0; }
            body {
                margin: 0;
                font-family: DejaVu Sans, sans-serif;
                color: #0F172A;
            }
            .marco {
                border: 3px solid #002259;
                margin: 30px;
                padding: 60px 50px;
                text-align: center;
            }
            .marca {
                font-size: 13px;
                letter-spacing: 3px;
                color: #00A3F4;
                font-weight: bold;
                margin-bottom: 40px;
            }
            h1 {
                font-size: 26px;
                color: #002259;
                margin: 0 0 10px;
            }
            .subtitulo {
                font-size: 13px;
                color: #64748B;
                margin-bottom: 40px;
            }
            .nombre {
                font-size: 30px;
                font-weight: bold;
                color: #002259;
                margin: 20px 0;
                border-bottom: 1px solid #E2E8F0;
                display: inline-block;
                padding-bottom: 8px;
            }
            .curso {
                font-size: 18px;
                margin: 20px 0 8px;
            }
            .empresa {
                font-size: 13px;
                color: #64748B;
                margin-bottom: 30px;
            }
            .detalle {
                font-size: 12px;
                color: #64748B;
                margin-top: 40px;
            }
            .codigo {
                margin-top: 30px;
                font-size: 11px;
                color: #94a3b8;
            }
        </style>
    </head>
    <body>
        <div class="marco">
            <div class="marca">SAFETY CONTROL TOWER</div>
            <h1>Certificado de Aprobación</h1>
            <div class="subtitulo">Se certifica que</div>
            <div class="nombre">{$nombreCompleto}</div>
            <div class="curso">aprobó satisfactoriamente el curso</div>
            <div class="curso"><strong>{$nombreCurso}</strong></div>
            <div class="empresa">{$nombreEmpresa}</div>
            <div class="detalle">
                Puntaje obtenido: {$porcentaje}% &nbsp;·&nbsp; Fecha de emisión: {$fecha}
            </div>
            <div class="codigo">Código de verificación: {$codigo}</div>
        </div>
    </body>
    </html>
    HTML;

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $directorio = __DIR__ . '/../uploads/certificados';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $nombreArchivo = 'certificado_' . $codigo . '.pdf';
    $rutaCompleta = $directorio . '/' . $nombreArchivo;

    file_put_contents($rutaCompleta, $dompdf->output());

    return 'uploads/certificados/' . $nombreArchivo;
}
