<?php
/**
 * i18n.php
 * Selección de idioma sencilla para el sitio público de Safety Control Tower.
 *
 * - Soporta los 5 idiomas contemplados en el contrato (Cl.1d.n): es, en, pt, fr, zh.
 * - El idioma se fija vía ?lang=xx (validado contra la lista blanca) y se
 *   recuerda en sesión para el resto de la navegación.
 * - Debe incluirse DESPUÉS de session_bootstrap.php (requiere sesión iniciada).
 */

const IDIOMAS_DISPONIBLES = ['es', 'en', 'pt', 'fr', 'zh'];
const IDIOMA_POR_DEFECTO = 'es';

if (isset($_GET['lang']) && in_array($_GET['lang'], IDIOMAS_DISPONIBLES, true)) {
    $_SESSION['site_lang'] = $_GET['lang'];
}

$lang = $_SESSION['site_lang'] ?? IDIOMA_POR_DEFECTO;

if (!in_array($lang, IDIOMAS_DISPONIBLES, true)) {
    $lang = IDIOMA_POR_DEFECTO;
}

$GLOBALS['__strings'] = require __DIR__ . '/lang/' . $lang . '.php';
$GLOBALS['__lang_actual'] = $lang;

// Nombre para mostrar de cada idioma (usado por el selector del navbar,
// sin que cada partial tenga que resolver rutas de archivo por su cuenta).
$GLOBALS['__idiomas_nombres'] = [];
foreach (IDIOMAS_DISPONIBLES as $__codigo) {
    $__dict = require __DIR__ . '/lang/' . $__codigo . '.php';
    $GLOBALS['__idiomas_nombres'][$__codigo] = $__dict['lang_name'];
}
unset($__codigo, $__dict);

/**
 * Devuelve el texto traducido para $key en el idioma activo.
 * Si la llave no existe, devuelve la llave misma (visible en QA, nunca en blanco).
 */
function t(string $key): string
{
    return $GLOBALS['__strings'][$key] ?? $key;
}

/**
 * Devuelve el código del idioma actualmente activo (es/en/pt/fr/zh).
 */
function idiomaActual(): string
{
    return $GLOBALS['__lang_actual'];
}

/**
 * Devuelve [codigo => nombre] de todos los idiomas disponibles, para
 * construir el selector de idioma sin que cada partial lea archivos.
 */
function idiomasDisponiblesConNombre(): array
{
    return $GLOBALS['__idiomas_nombres'];
}

/**
 * Normaliza cualquier valor recibido (perfil de usuario, request, dato
 * legado en BD) a uno de los 5 códigos de IDIOMAS_DISPONIBLES.
 *
 * Tolera:
 *  - mayúsculas/minúsculas y espacios ('ES', ' es ')
 *  - el valor legado 'ESP' (cargado antes de existir la whitelist de 5
 *    idiomas), mapeándolo a 'es'
 *  - cualquier valor no reconocido, degradando a IDIOMA_POR_DEFECTO
 *
 * Nunca lanza excepción: siempre devuelve un código válido de la whitelist.
 */
function normalizarIdiomaUsuario(?string $valor): string
{
    $codigo = strtolower(trim((string) $valor));

    if ($codigo === 'esp') {
        $codigo = 'es';
    }

    return in_array($codigo, IDIOMAS_DISPONIBLES, true) ? $codigo : IDIOMA_POR_DEFECTO;
}

/**
 * Variante "escapada" de t(): equivalente a
 * htmlspecialchars(t($key), ENT_QUOTES, 'UTF-8'), para usar directamente en
 * atributos/texto HTML sin repetir el wrapper en cada vista.
 */
function tt(string $key): string
{
    return htmlspecialchars(t($key), ENT_QUOTES, 'UTF-8');
}