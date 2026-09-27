<?php
/**
 * api/usuarios/idioma.php
 *
 * Endpoint de autogestión: guarda en el perfil del usuario (users.language)
 * el idioma que el usuario elige desde el selector de idioma disponible en
 * las pantallas de navegación (js/lang-switcher.js).
 *
 * A propósito NO usa requireCapabilityPage/requireCapability: cualquier
 * cuenta con sesión activa, sin importar su rol, puede cambiar su propio
 * idioma de visualización. La única regla de negocio es "solo tu propio
 * perfil", reforzada usando currentUserId() (nunca un id_users recibido
 * desde el cliente).
 */

require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);
requireLogin();

$idUsers = currentUserId();
if (!$idUsers) {
    responderJSON(false, null, 'Debes iniciar sesión para continuar.', 401);
}

$rawLang = (string) ($input['language'] ?? '');
$language = normalizarIdiomaUsuario($rawLang);

// normalizarIdiomaUsuario() nunca lanza error (degrada a IDIOMA_POR_DEFECTO),
// así que validamos explícitamente que lo recibido realmente esté en la
// whitelist antes de normalizar, para poder avisarle al cliente si mandó
// un código no soportado en vez de guardar silenciosamente "es".
$codigoLimpio = strtolower(trim($rawLang));
if ($codigoLimpio === 'esp') {
    $codigoLimpio = 'es';
}
if (!in_array($codigoLimpio, IDIOMAS_DISPONIBLES, true)) {
    responderJSON(false, null, 'Idioma no soportado.', 400);
}

try {
    $perfilAnterior = currentUserProfile($pdo);

    $stmt = $pdo->prepare(
        'UPDATE users
         SET language = :language, last_update = NOW()
         WHERE id_users = :id_users
         LIMIT 1'
    );
    $stmt->execute([
        'language' => $language,
        'id_users' => $idUsers,
    ]);

    // Refleja el cambio de inmediato en la sesión actual: no hace falta
    // esperar al próximo ?lang= ni a un nuevo login para ver el idioma nuevo.
    $_SESSION['site_lang'] = $language;

    if ($perfilAnterior) {
        auditTrailLogChanges(
            $pdo,
            (int) ($perfilAnterior['id_company'] ?? 0),
            'usuarios',
            'users',
            $idUsers,
            $perfilAnterior,
            array_merge($perfilAnterior, ['language' => $language]),
            'update',
            trim(($perfilAnterior['name'] ?? '') . ' ' . ($perfilAnterior['lastname'] ?? '')),
            ['language']
        );
    }

    responderJSON(true, ['language' => $language], 'Idioma actualizado correctamente.');
} catch (PDOException $e) {
    error_log('api/usuarios/idioma.php: ' . $e->getMessage());
    responderJSON(false, null, 'No fue posible actualizar el idioma.', 500);
}
