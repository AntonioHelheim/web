<?php
/**
 * lib/validation.php
 * Validaciones reutilizables para no repetirlas (con variaciones sutiles)
 * en cada endpoint nuevo.
 */

/**
 * Devuelve la lista de campos obligatorios que faltan o vienen vacíos en
 * $input. Lista vacía = todo OK.
 *
 *   $faltantes = requerirCampos($input, ['rut', 'razon_social', 'email']);
 *   if ($faltantes) { responderJSON(false, ['campos_faltantes' => $faltantes], 'Faltan campos obligatorios.', 400); }
 */
function requerirCampos(array $input, array $camposObligatorios): array
{
    $faltantes = [];
    foreach ($camposObligatorios as $campo) {
        if (!isset($input[$campo]) || trim((string) $input[$campo]) === '') {
            $faltantes[] = $campo;
        }
    }
    return $faltantes;
}

function validarEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Valida el dígito verificador de un RUT chileno (con o sin puntos/guion).
 * Ej: validarRutChileno('12.345.678-5') / validarRutChileno('12345678-5')
 */
function validarRutChileno(string $rut): bool
{
    $rut = strtoupper(str_replace(['.', '-', ' '], '', trim($rut)));

    if (!preg_match('/^\d{7,8}[0-9K]$/', $rut)) {
        return false;
    }

    $dv = substr($rut, -1);
    $numero = substr($rut, 0, -1);

    $suma = 0;
    $multiplicador = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += ((int) $numero[$i]) * $multiplicador;
        $multiplicador = ($multiplicador === 7) ? 2 : $multiplicador + 1;
    }

    $resto = 11 - ($suma % 11);
    $dvEsperado = $resto === 11 ? '0' : ($resto === 10 ? 'K' : (string) $resto);

    return $dv === $dvEsperado;
}

/**
 * Limpia texto libre proveniente del usuario antes de guardarlo o mostrarlo:
 * quita espacios sobrantes y neutraliza HTML (protección XSS básica).
 */
function sanitizarTexto(string $texto): string
{
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}