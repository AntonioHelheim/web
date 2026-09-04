<?php
/**
 * =========================================================
 * LOGIN.PHP
 * =========================================================
 *
 * Login sin contraseña en 2 pasos:
 *
 *   1. request_code
 *      { email, csrf_token }
 *
 *   2. verify_code
 *      { email, code, csrf_token }
 *
 *
 * LOCALHOST
 * ---------------------------------------------------------
 * - Genera código normalmente.
 * - Guarda el hash en login_codes.
 * - NO envía correo.
 * - Devuelve el código en la respuesta JSON.
 *
 *
 * PRODUCCIÓN
 * ---------------------------------------------------------
 * - Genera código normalmente.
 * - Guarda el hash en login_codes.
 * - Envía código por correo.
 * - NO devuelve el código.
 *
 *
 * SEGURIDAD
 * ---------------------------------------------------------
 * - CSRF
 * - Rate limiting
 * - Código aleatorio
 * - password_hash()
 * - password_verify()
 * - Expiración de 10 minutos
 * - Código de un solo uso
 * - Bloqueo por intentos fallidos
 * - No revela si un correo existe
 * - Regeneración de sesión
 * =========================================================
 */


/* =========================================================
   1. CONSTANTES
   ========================================================= */

const CODIGO_LARGO               = 6;

const CODIGO_VIGENCIA_MINUTOS    = 10;

const MAX_SOLICITUDES_CODIGO_IP  = 8;

const MAX_SOLICITUDES_CODIGO_USR = 3;

const VENTANA_SOLICITUD_MINUTOS  = 15;

const MAX_INTENTOS_VERIFICACION  = 5;

const VENTANA_VERIFICACION_MIN   = 15;

const BLOQUEO_MINUTOS            = 15;


/* =========================================================
   2. CARGAR DEPENDENCIAS
   ========================================================= */

require __DIR__ . '/session_bootstrap.php';

require __DIR__ . '/lib/response.php';

require __DIR__ . '/lib/db.php';


/* =========================================================
   3. MÉTODO HTTP
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responderJSON(
        false,
        null,
        'Método no permitido.',
        405
    );
}


/* =========================================================
   4. LEER INPUT
   ========================================================= */

$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!$input) {

    $input = $_POST;
}


/* =========================================================
   5. VARIABLES DE LA SOLICITUD
   ========================================================= */

$action = (string) (
    $input['action'] ?? ''
);

$email = trim(
    (string) (
        $input['email'] ?? ''
    )
);

$code = trim(
    (string) (
        $input['code'] ?? ''
    )
);

$csrfToken = (string) (
    $input['csrf_token'] ?? ''
);

$ip = $_SERVER['REMOTE_ADDR']
    ?? 'unknown';


/* =========================================================
   6. CARGAR CONFIGURACIÓN
   =========================================================
 *
 * db.php normalmente es quien carga config.php.
 *
 * Sin embargo, para asegurar que APP_ENV esté disponible
 * independientemente de cómo esté construido db.php,
 * comprobamos si la variable ya existe.
 *
 * Si db.php ya cargó config.php, $isLocal estará disponible.
 */

if (!isset($isLocal)) {

    $environment = getenv('APP_ENV') ?: 'local';

    $isLocal = ($environment === 'local');
}


/* =========================================================
   7. VALIDAR CSRF
   ========================================================= */

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $csrfToken
    )
) {

    responderJSON(
        false,
        null,
        'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.',
        403
    );
}


/* =========================================================
   8. VALIDAR EMAIL
   ========================================================= */

if (
    $email === '' ||
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    responderJSON(
        false,
        null,
        'Correo electrónico no válido.',
        400
    );
}


/* =========================================================
   9. GENERAR CÓDIGO
   ========================================================= */

function generarCodigo(): string
{
    return str_pad(
        (string) random_int(
            0,
            10 ** CODIGO_LARGO - 1
        ),
        CODIGO_LARGO,
        '0',
        STR_PAD_LEFT
    );
}


/* =========================================================
   10. ENVIAR CÓDIGO POR CORREO
   ========================================================= */

function enviarCodigoPorCorreo(
    string $email,
    string $codigo
): bool {

    $asunto =
        'Tu código de acceso — Safety Control Tower';

    $cuerpo =
        "Tu código de acceso es: {$codigo}\n\n" .
        'Este código vence en ' .
        CODIGO_VIGENCIA_MINUTOS .
        " minutos.\n" .
        'Si no solicitaste este código, puedes ignorar este mensaje.';

    $cabeceras =
        "From: Safety Control Tower <no-responder@safetycontroltower.cl>\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail(
        $email,
        $asunto,
        $cuerpo,
        $cabeceras
    );
}


/* =========================================================
   11. REGISTRAR INTENTO DE VERIFICACIÓN
   ========================================================= */

function registrarIntentoVerificacion(
    PDO $pdo,
    string $identifier,
    string $ip,
    bool $success
): void {

    $stmt = $pdo->prepare(
        'INSERT INTO login_attempts
        (
            identifier,
            ip_address,
            success
        )
        VALUES
        (
            :identifier,
            :ip,
            :success
        )'
    );

    $stmt->execute(
        [
            'identifier' =>
                $identifier,

            'ip' =>
                $ip,

            'success' =>
                $success ? 1 : 0
        ]
    );
}


/* =========================================================
   12. CONTAR INTENTOS FALLIDOS
   ========================================================= */

function intentosVerificacionFallidosRecientes(
    PDO $pdo,
    string $identifier,
    string $ip,
    int $minutos
): int {

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM login_attempts
         WHERE success = 0
           AND created_at >=
               (NOW() - INTERVAL :minutos MINUTE)
           AND
               (
                   identifier = :identifier
                   OR
                   ip_address = :ip
               )'
    );

    $stmt->execute(
        [
            'minutos' =>
                $minutos,

            'identifier' =>
                $identifier,

            'ip' =>
                $ip
        ]
    );

    return (int) $stmt->fetchColumn();
}


/* =========================================================
   13. PROCESAMIENTO PRINCIPAL
   ========================================================= */

try {


    /* =====================================================
       PASO 1 — SOLICITAR CÓDIGO
       ===================================================== */

    if ($action === 'request_code') {


        /* -------------------------------------------------
           RATE LIMIT POR IP
           ------------------------------------------------- */

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM login_codes
             WHERE ip_address = :ip
               AND created_at >=
                   (
                       NOW()
                       -
                       INTERVAL :minutos MINUTE
                   )'
        );

        $stmt->execute(
            [
                'ip' =>
                    $ip,

                'minutos' =>
                    VENTANA_SOLICITUD_MINUTOS
            ]
        );

        $solicitudesPorIp =
            (int) $stmt->fetchColumn();


        if (
            $solicitudesPorIp >=
            MAX_SOLICITUDES_CODIGO_IP
        ) {

            responderJSON(
                false,
                null,
                'Demasiadas solicitudes desde esta conexión. Intenta nuevamente en unos minutos.',
                429
            );
        }


        /* -------------------------------------------------
           BUSCAR USUARIO
           ------------------------------------------------- */

        $stmt = $pdo->prepare(
            'SELECT id_users
             FROM users
             WHERE id_users = :email
               AND state = 1
             LIMIT 1'
        );

        $stmt->execute(
            [
                'email' =>
                    $email
            ]
        );

        $user = $stmt->fetch();


        /* -------------------------------------------------
           GENERAR CÓDIGO SI EXISTE USUARIO
           ------------------------------------------------- */

        if ($user) {


            /* ---------------------------------------------
               RATE LIMIT POR USUARIO
               --------------------------------------------- */

            $stmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM login_codes
                 WHERE id_users = :identifier
                   AND created_at >=
                       (
                           NOW()
                           -
                           INTERVAL :minutos MINUTE
                       )'
            );

            $stmt->execute(
                [
                    'identifier' =>
                        $user['id_users'],

                    'minutos' =>
                        VENTANA_SOLICITUD_MINUTOS
                ]
            );

            $solicitudesPorUsuario =
                (int) $stmt->fetchColumn();


            /* ---------------------------------------------
               GENERAR CÓDIGO
               --------------------------------------------- */

            if (
                $solicitudesPorUsuario <
                MAX_SOLICITUDES_CODIGO_USR
            ) {

                $codigo =
                    generarCodigo();


                /* -----------------------------------------
                   HASH
                   ----------------------------------------- */

                $codigoHash =
                    password_hash(
                        $codigo,
                        PASSWORD_DEFAULT
                    );


                /* -----------------------------------------
                   EXPIRACIÓN
                   ----------------------------------------- */

                $expiraEn =
                    date(
                        'Y-m-d H:i:s',
                        time()
                        +
                        CODIGO_VIGENCIA_MINUTOS * 60
                    );


                /* -----------------------------------------
                   GUARDAR EN BASE DE DATOS
                   ----------------------------------------- */

                $stmt = $pdo->prepare(
                    'INSERT INTO login_codes
                    (
                        id_users,
                        code_hash,
                        expires_at,
                        ip_address
                    )
                    VALUES
                    (
                        :identifier,
                        :hash,
                        :expira,
                        :ip
                    )'
                );

                $stmt->execute(
                    [
                        'identifier' =>
                            $user['id_users'],

                        'hash' =>
                            $codigoHash,

                        'expira' =>
                            $expiraEn,

                        'ip' =>
                            $ip
                    ]
                );


                /* =========================================
                   LOCALHOST
                   ========================================= */

if ($isLocal) {

    /**
     * En desarrollo NO enviamos correo.
     *
     * El código se devuelve:
     * - dentro de data.dev_code
     * - dentro del mensaje
     *
     * Esto permite que el código sea visible incluso
     * si el frontend actualmente solo muestra response.message.
     */

    responderJSON(
        true,
        [
            'dev_code' => $codigo
        ],
        'Modo desarrollo: tu código de acceso es ' . $codigo . '.'
    );
}


                /* =========================================
                   PRODUCCIÓN
                   ========================================= */

                enviarCodigoPorCorreo(
                    $email,
                    $codigo
                );
            }
        }


        /* -------------------------------------------------
           RESPUESTA PRODUCCIÓN / GENÉRICA
           ------------------------------------------------- */

        responderJSON(
            true,
            null,
            'Si el correo está registrado, recibirás un código de acceso.'
        );
    }


    /* =====================================================
       PASO 2 — VERIFICAR CÓDIGO
       ===================================================== */

    if ($action === 'verify_code') {


        /* -------------------------------------------------
           VALIDAR FORMATO DEL CÓDIGO
           ------------------------------------------------- */

        if (
            !preg_match(
                '/^\d{' .
                CODIGO_LARGO .
                '}$/',
                $code
            )
        ) {

            responderJSON(
                false,
                null,
                'Código inválido.',
                400
            );
        }


        /* -------------------------------------------------
           CONTAR INTENTOS FALLIDOS
           ------------------------------------------------- */

        $intentosFallidos =
            intentosVerificacionFallidosRecientes(
                $pdo,
                $email,
                $ip,
                VENTANA_VERIFICACION_MIN
            );


        if (
            $intentosFallidos >=
            MAX_INTENTOS_VERIFICACION
        ) {

            responderJSON(
                false,
                null,
                'Demasiados intentos fallidos. Solicita un nuevo código en ' .
                BLOQUEO_MINUTOS .
                ' minutos.',
                429
            );
        }


        /* -------------------------------------------------
           BUSCAR USUARIO
           ------------------------------------------------- */

        $stmt = $pdo->prepare(
            'SELECT *
             FROM users
             WHERE id_users = :email
               AND state = 1
             LIMIT 1'
        );

        $stmt->execute(
            [
                'email' =>
                    $email
            ]
        );

        $user =
            $stmt->fetch();


        /* -------------------------------------------------
           HASH SEÑUELO
           ------------------------------------------------- */

        static $dummyHash =
            '$2y$10$abcdefghijklmnopqrstuuVQjV1n0z0e0e0e0e0e0e0e0e0e0e0e';


        $codigoValido =
            false;

        $idLoginCode =
            null;


        /* -------------------------------------------------
           BUSCAR CÓDIGO
           ------------------------------------------------- */

        if ($user) {

            $stmt = $pdo->prepare(
                'SELECT
                    id_login_code,
                    code_hash
                 FROM login_codes
                 WHERE id_users = :identifier
                   AND used_at IS NULL
                   AND expires_at >= NOW()
                   AND attempts < :maxIntentos
                 ORDER BY created_at DESC
                 LIMIT 1'
            );

            $stmt->execute(
                [
                    'identifier' =>
                        $user['id_users'],

                    'maxIntentos' =>
                        MAX_INTENTOS_VERIFICACION
                ]
            );

            $registroCodigo =
                $stmt->fetch();


            if ($registroCodigo) {

                $codigoValido =
                    password_verify(
                        $code,
                        $registroCodigo['code_hash']
                    );

                $idLoginCode =
                    $registroCodigo['id_login_code'];

            } else {

                /**
                 * Mantener tiempo de respuesta similar.
                 */
                password_verify(
                    $code,
                    $dummyHash
                );
            }

        } else {

            /**
             * Mantener tiempo de respuesta similar
             * aunque el usuario no exista.
             */
            password_verify(
                $code,
                $dummyHash
            );
        }


        /* =================================================
           CÓDIGO INVÁLIDO
           ================================================= */

        if (
            !$user ||
            !$codigoValido
        ) {

            registrarIntentoVerificacion(
                $pdo,
                $email,
                $ip,
                false
            );


            if ($idLoginCode) {

                $pdo->prepare(
                    'UPDATE login_codes
                     SET attempts = attempts + 1
                     WHERE id_login_code = :id'
                )->execute(
                    [
                        'id' =>
                            $idLoginCode
                    ]
                );
            }


            responderJSON(
                false,
                null,
                'Código inválido o expirado.',
                401
            );
        }


        /* =================================================
           CÓDIGO CORRECTO
           ================================================= */

        /**
         * Marcar código como utilizado.
         */
        $pdo->prepare(
            'UPDATE login_codes
             SET used_at = NOW()
             WHERE id_login_code = :id'
        )->execute(
            [
                'id' =>
                    $idLoginCode
            ]
        );


        /**
         * Registrar login exitoso.
         */
        registrarIntentoVerificacion(
            $pdo,
            $email,
            $ip,
            true
        );


        /**
         * Regenerar ID de sesión.
         */
        session_regenerate_id(true);


        /**
         * Crear sesión del usuario.
         */
        $_SESSION['user_id'] =
            $user['id_users'];

        $_SESSION['user_email'] =
            $email;

        $_SESSION['logged_in'] =
            true;

        $_SESSION['last_activity'] =
            time();

        $_SESSION['csrf_token'] =
            bin2hex(
                random_bytes(32)
            );


        /* -------------------------------------------------
           LOGIN EXITOSO
           ------------------------------------------------- */

        responderJSON(
            true,
            [
                'redirect' =>
                    'bienvenida.php'
            ],
            'Inicio de sesión exitoso.'
        );
    }


    /* =====================================================
       ACCIÓN NO RECONOCIDA
       ===================================================== */

    responderJSON(
        false,
        null,
        'Acción no reconocida.',
        400
    );


} catch (PDOException $e) {


    /* =====================================================
       ERROR DE BASE DE DATOS
       ===================================================== */

    error_log(
        'login.php: ' .
        $e->getMessage()
    );


    responderJSON(
        false,
        null,
        'Error al procesar la solicitud.',
        500
    );
}