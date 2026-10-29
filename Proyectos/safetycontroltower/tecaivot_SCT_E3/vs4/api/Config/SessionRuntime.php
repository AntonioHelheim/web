<?php

/**
 * Safety Control Tower
 * Configuración centralizada del contexto de sesión.
 *
 * Responsabilidades:
 * - aislar cookies de sesión entre despliegues SCT que compartan host;
 * - resolver la ruta pública de la instalación sin codificar rutas de equipo;
 * - mantener Secure/HttpOnly/SameSite de forma consistente;
 * - permitir que logout.php elimine exactamente la cookie de la instancia.
 *
 * Este archivo NO inicia la sesión.
 */

function sctSessionNormalizeFsPath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));

    if ($path === '') {
        return '';
    }

    $path = preg_replace('#/+#', '/', $path) ?? $path;

    return rtrim($path, '/');
}

function sctSessionNormalizeUrlPath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));

    if ($path === '' || $path === '.') {
        return '/';
    }

    $path = preg_replace('#/+#', '/', $path) ?? $path;

    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    return $path === '/'
        ? '/'
        : rtrim($path, '/');
}

/**
 * Determina la ruta pública base de la instalación SCT.
 *
 * Prioridad:
 * 1. SCT_SESSION_PUBLIC_BASE, cuando exista como variable de entorno.
 * 2. Relación física DOCUMENT_ROOT <-> raíz SCT.
 * 3. Relación SCRIPT_FILENAME <-> SCRIPT_NAME.
 * 4. "/" como fallback.
 */
function sctSessionResolvePublicBasePath(
    string $appRoot,
    array $server,
    ?array $environment = null
): string {
    $environment ??= $_ENV + $_SERVER;

    $override = trim((string)(
        $environment['SCT_SESSION_PUBLIC_BASE']
        ?? getenv('SCT_SESSION_PUBLIC_BASE')
        ?: ''
    ));

    if ($override !== '') {
        return sctSessionNormalizeUrlPath($override);
    }

    $appRoot = sctSessionNormalizeFsPath(
        realpath($appRoot) ?: $appRoot
    );

    $documentRootRaw = (string)($server['DOCUMENT_ROOT'] ?? '');

    if ($documentRootRaw !== '') {
        $documentRoot = sctSessionNormalizeFsPath(
            realpath($documentRootRaw) ?: $documentRootRaw
        );

        if ($documentRoot !== '') {
            if ($appRoot === $documentRoot) {
                return '/';
            }

            $prefix = $documentRoot . '/';

            if (str_starts_with($appRoot . '/', $prefix)) {
                $relative = substr(
                    $appRoot,
                    strlen($documentRoot)
                );

                return sctSessionNormalizeUrlPath($relative);
            }
        }
    }

    $scriptFilenameRaw = (string)(
        $server['SCRIPT_FILENAME'] ?? ''
    );
    $scriptNameRaw = (string)(
        $server['SCRIPT_NAME'] ?? ''
    );

    if ($scriptFilenameRaw !== '' && $scriptNameRaw !== '') {
        $scriptFilename = sctSessionNormalizeFsPath(
            realpath($scriptFilenameRaw) ?: $scriptFilenameRaw
        );

        $scriptName = sctSessionNormalizeUrlPath(
            $scriptNameRaw
        );

        if (
            $scriptFilename !== ''
            && str_starts_with(
                $scriptFilename,
                $appRoot . '/'
            )
        ) {
            $relativeScript = substr(
                $scriptFilename,
                strlen($appRoot)
            );

            $relativeScript = sctSessionNormalizeUrlPath(
                $relativeScript
            );

            if (
                $relativeScript !== '/'
                && str_ends_with(
                    $scriptName,
                    $relativeScript
                )
            ) {
                $base = substr(
                    $scriptName,
                    0,
                    strlen($scriptName)
                        - strlen($relativeScript)
                );

                return sctSessionNormalizeUrlPath($base);
            }
        }
    }

    return '/';
}

/**
 * Host público que participa del namespace de la sesión.
 *
 * HTTP_HOST puede incluir puerto en desarrollo. Eso permite diferenciar
 * localhost de localhost:3000, pero el puerto NO es el único criterio:
 * también participa la ruta pública de la aplicación.
 */
function sctSessionPublicHost(array $server): string
{
    $host = strtolower(trim((string)(
        $server['HTTP_HOST']
        ?? $server['SERVER_NAME']
        ?? 'localhost'
    )));

    $host = preg_replace(
        '/[^a-z0-9.\-:\[\]]/i',
        '',
        $host
    ) ?? '';

    return $host !== ''
        ? $host
        : 'localhost';
}

/**
 * Determina si la cookie debe usar Secure.
 *
 * X-Forwarded-Proto sólo se considera cuando el entorno autoriza
 * explícitamente confiar en cabeceras de proxy.
 */
function sctSessionIsSecure(
    array $server,
    ?array $environment = null
): bool {
    $https = strtolower(trim((string)(
        $server['HTTPS'] ?? ''
    )));

    if (
        $https !== ''
        && $https !== 'off'
        && $https !== '0'
    ) {
        return true;
    }

    if ((int)($server['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }

    $environment ??= $_ENV + $_SERVER;

    $trustProxy = strtolower(trim((string)(
        $environment['SCT_TRUST_PROXY_HEADERS']
        ?? getenv('SCT_TRUST_PROXY_HEADERS')
        ?: ''
    )));

    if (
        in_array(
            $trustProxy,
            ['1', 'true', 'yes', 'on'],
            true
        )
    ) {
        $forwardedProto = strtolower(trim((string)(
            $server['HTTP_X_FORWARDED_PROTO'] ?? ''
        )));

        return $forwardedProto === 'https';
    }

    return false;
}

/**
 * Construye la configuración efectiva de sesión.
 */
function sctBuildSessionRuntimeConfig(
    string $appRoot,
    array $server,
    ?array $environment = null
): array {
    $environment ??= $_ENV + $_SERVER;

    $publicBase = sctSessionResolvePublicBasePath(
        $appRoot,
        $server,
        $environment
    );

    $cookiePath = $publicBase === '/'
        ? '/'
        : rtrim($publicBase, '/') . '/';

    $namespaceOverride = trim((string)(
        $environment['SCT_SESSION_NAMESPACE']
        ?? getenv('SCT_SESSION_NAMESPACE')
        ?: ''
    ));

    $identity = $namespaceOverride !== ''
        ? 'override|' . $namespaceOverride
        : sctSessionPublicHost($server)
            . '|'
            . $publicBase;

    $sessionName = 'SCTSESS'
        . strtoupper(
            substr(
                hash('sha256', $identity),
                0,
                16
            )
        );

    return [
        'name' => $sessionName,
        'public_base' => $publicBase,
        'cookie_path' => $cookiePath,
        'secure' => sctSessionIsSecure(
            $server,
            $environment
        ),
    ];
}

/**
 * Configuración para la solicitud actual.
 */
function sctSessionRuntimeConfig(): array
{
    return sctBuildSessionRuntimeConfig(
        dirname(__DIR__, 2),
        $_SERVER,
        $_ENV + $_SERVER
    );
}

/**
 * Elimina exclusivamente la cookie de sesión de la instalación SCT actual.
 */
function sctExpireCurrentSessionCookie(): void
{
    $params = session_get_cookie_params();

    $options = [
        'expires' => time() - 42000,
        'path' => (string)(
            $params['path'] ?? '/'
        ),
        'secure' => (bool)(
            $params['secure'] ?? false
        ),
        'httponly' => (bool)(
            $params['httponly'] ?? true
        ),
        'samesite' => (string)(
            $params['samesite'] ?? 'Strict'
        ),
    ];

    $domain = trim((string)(
        $params['domain'] ?? ''
    ));

    if ($domain !== '') {
        $options['domain'] = $domain;
    }

    setcookie(
        session_name(),
        '',
        $options
    );
}
