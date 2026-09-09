<?php
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();

$sesionExpirada = !empty($_SESSION['session_expired']);
unset($_SESSION['session_expired']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Acceso denegado - Bifrost</title>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=<?= $ASSET_VERSION ?>">
</head>
<body class="gb-centered-page">

<div class="gb-shell" style="text-align:center;">
  <h1 class="gb-title" style="color:#e0453f;">ACCESO DENEGADO</h1>

  <p style="font-size:9px; line-height:1.6; margin:16px 0;">
    <?= $sesionExpirada
        ? 'Tu sesión expiró por inactividad.'
        : 'No tienes una sesión activa. Inicia sesión para continuar.'; ?>
  </p>

  <a href="index.php" class="gb-switch" style="display:inline-block; margin-top:8px;">
    Volver a intentar
  </a>

  <footer class="gb-footer">
    Desarrollado por <a href="https://helheim.cl" target="_blank" rel="noopener noreferrer">helheim.cl</a>
  </footer>
</div>

</body>
</html>
