<?php
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();

if (empty($_SESSION['user_id'])) {
    header('Location: acceso-denegado.php');
    exit;
}
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
// $ASSET_VERSION ya viene definido por session_bootstrap.php — súbelo ahí
// (un solo lugar) cada vez que actualices archivos .js/.css y los subas,
// para forzar que el navegador (y el hosting) descarten la versión en
// caché en vez de seguir usando una copia vieja.
?>
<!DOCTYPE html>
<!-- Bifrost build: <?= $ASSET_VERSION ?> (definida en session_bootstrap.php) -->
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Bifrost</title>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=<?= $ASSET_VERSION ?>">
</head>
<body class="gb-centered-page">

<div class="gb-topbar">
  <span>Jugador: <?= $username ?></span>
  <button id="logout-btn">Salir</button>
</div>

<div id="game-container"></div>

<!-- Menú como overlay HTML normal (no una escena de Phaser). El botón
     "Salir" de arriba nunca ha fallado porque es un botón HTML común; el
     menú de Phaser sí, así que se reconstruyó con la misma idea: HTML
     simple con addEventListener, fuera de toda la complejidad de
     pausar/lanzar escenas dentro del canvas. -->
<div id="game-menu-overlay" class="gb-overlay" style="display:none;">
  <div class="gb-panel">
    <h2>Menú</h2>
    <button id="menu-change-appearance">Cambiar apariencia</button>
    <button id="menu-back-to-game">Volver al juego</button>
  </div>
</div>

<script>
  // Datos que vienen del servidor PHP, disponibles para todo el JS del juego.
  window.BIFROST_USER = { username: "<?= $username ?>" };

  // Captura cualquier error de JavaScript no atrapado en cualquier parte
  // del juego y lo deja bien visible en la consola — sin esto, un error
  // dentro de una escena puede dejar el juego "congelado" sin ninguna
  // pista de qué pasó. Abre la consola (F12) para ver estos mensajes.
  window.addEventListener('error', function (e) {
    console.error('[Bifrost] ERROR NO CAPTURADO:', e.message, 'en', (e.filename || '?') + ':' + (e.lineno || '?'));
  });
  window.addEventListener('unhandledrejection', function (e) {
    console.error('[Bifrost] PROMESA RECHAZADA SIN CAPTURAR:', e.reason);
  });
</script>

<script>
  // Disponible para cualquier script que necesite el mismo cache-busting
  // que ya usan los <script src="...?v=..."> (ej. al cargar data/*.json
  // con el loader de Phaser, que no pasa por PHP y no puede leer
  // $ASSET_VERSION directamente).
  window.BIFROST_ASSET_VERSION = <?= json_encode($ASSET_VERSION) ?>;
  // Token CSRF de la sesión actual: lo manda cada fetch POST del juego a
  // la API (ítem 5 de ROADMAP-ARQUITECTURA.md — antes solo login/registro
  // lo pedían).
  window.BIFROST_CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token']) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/phaser@3.80.1/dist/phaser.min.js"></script>
<script src="js/data.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/maps.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/entities/CharacterVisual.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/entities/TileVisuals.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/entities/Player.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/entities/NPC.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/scenes/PreloadScene.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/scenes/CharacterCreationScene.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/scenes/OverworldScene.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/scenes/BattleScene.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/scenes/PvpBattleScene.js?v=<?= $ASSET_VERSION ?>"></script>
<script src="js/main.js?v=<?= $ASSET_VERSION ?>"></script>
<script>
document.getElementById('logout-btn').addEventListener('click', async () => {
  await fetch('api/logout.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ csrf_token: window.BIFROST_CSRF_TOKEN }),
  });
  window.location.href = 'index.php';
});

// Puente entre el overlay HTML del menú y Phaser. OverworldScene llama a
// window.BIFROST_OPEN_MENU(this) en vez de abrir una escena de Phaser para
// el menú — evita toda la complejidad de pausar/lanzar escenas superpuestas
// que venía dando problemas.
(function () {
  const overlay = document.getElementById('game-menu-overlay');
  let currentOverworld = null;

  window.BIFROST_OPEN_MENU = function (overworldScene) {
    currentOverworld = overworldScene;
    overworldScene.input.keyboard.enabled = false;
    overworldScene.scene.pause();
    overlay.style.display = 'flex';
  };

  document.getElementById('menu-back-to-game').addEventListener('click', () => {
    overlay.style.display = 'none';
    if (currentOverworld) {
      currentOverworld.input.keyboard.enabled = true;
      currentOverworld.scene.resume();
    }
  });

  document.getElementById('menu-change-appearance').addEventListener('click', () => {
    overlay.style.display = 'none';
    if (currentOverworld) {
      // OJO: usar scene.launch() aquí (lanzar CharacterCreationScene
      // ENCIMA de OverworldScene pausada) era exactamente el mismo patrón
      // que rompía el menú — Phaser no responde bien a los clics en una
      // escena nueva lanzada sobre otra pausada. Por eso scene.start():
      // hace una transición completa (equivalente a lo que ya usan los
      // warps entre mapas, que nunca han fallado) en vez de apilar.
      currentOverworld.input.keyboard.enabled = true;
      currentOverworld.scene.start('CharacterCreationScene', {
        editMode: true,
        appearance: currentOverworld.appearance,
        returnData: {
          mapKey: currentOverworld.mapKey,
          x: currentOverworld.player.tileX,
          y: currentOverworld.player.tileY,
          party: currentOverworld.party,
          inventory: currentOverworld.inventory,
        },
      });
      currentOverworld = null;
    }
  });
})();
</script>

</body>
</html>
