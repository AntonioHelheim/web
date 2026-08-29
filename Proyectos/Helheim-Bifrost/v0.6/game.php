<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>PokeWeb</title>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
  body { flex-direction: column; }
</style>
</head>
<body>

<div class="gb-topbar">
  <span>Jugador: <?= $username ?></span>
  <button id="logout-btn">Salir</button>
</div>

<div id="game-container"></div>

<script>
  // Datos que vienen del servidor PHP, disponibles para todo el JS del juego.
  window.POKEWEB_USER = { username: "<?= $username ?>" };
</script>

<script src="https://cdn.jsdelivr.net/npm/phaser@3.80.1/dist/phaser.min.js"></script>
<script src="js/data.js"></script>
<script src="js/maps.js"></script>
<script src="js/entities/CharacterVisual.js"></script>
<script src="js/entities/TileVisuals.js"></script>
<script src="js/entities/Player.js"></script>
<script src="js/scenes/PreloadScene.js"></script>
<script src="js/scenes/CharacterCreationScene.js"></script>
<script src="js/scenes/StarterSelectionScene.js"></script>
<script src="js/scenes/MenuScene.js"></script>
<script src="js/scenes/OverworldScene.js"></script>
<script src="js/scenes/BattleScene.js"></script>
<script src="js/scenes/PvpBattleScene.js"></script>
<script src="js/main.js"></script>
<script>
document.getElementById('logout-btn').addEventListener('click', async () => {
  await fetch('api/logout.php', { method: 'POST', credentials: 'include' });
  window.location.href = 'index.php';
});
</script>

</body>
</html>
