<?php
/**
 * lib/response.php
 * Formato de respuesta JSON único para todos los endpoints de /api/.
 * Evita que cada desarrollador arme su propio json_encode() a mano y se
 * olvide del código HTTP, del campo "success", o del Content-Type.
 *
 * Uso típico:
 *   responderJSON(true, ['id_company' => 7], 'Empresa creada correctamente.', 201);
 *   responderJSON(false, null, 'Correo electrónico no válido.', 400);
 *
 * IMPORTANTE: responderJSON() termina la ejecución (exit) — es siempre la
 * última línea de un endpoint, igual que "return" en un controlador.
 */

function responderJSON(bool $exito, $datos = null, string $mensaje = '', int $codigoHttp = 200): void
{
    if (!headers_sent()) {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
    }

    $payload = ['success' => $exito];

    if ($mensaje !== '') {
        $payload['message'] = $mensaje;
    }

    if ($datos !== null) {
        $payload['data'] = $datos;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}