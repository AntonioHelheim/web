<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/i18n.php';

$sesionExpirada = !empty($_SESSION['session_expired']);
unset($_SESSION['session_expired']);

$langActual = idiomaActual();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langActual, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tt('denied_title') ?> - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?> -->
    <link rel="stylesheet" href="css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">

    <style>
        .denied-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            position: relative;
        }
        .denied-icon {
            font-size: 3.5rem;
            color: #dc2626;
            margin-bottom: 1rem;
        }
        .denied-language {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .denied-language select {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }
    </style>
</head>
<body>

    <div class="denied-wrapper">

        <div class="denied-language">
            <label class="sr-only" for="deniedLanguage"><?= tt('login_language_label') ?></label>
            <select id="deniedLanguage" aria-label="<?= tt('login_language_label') ?>" onchange="if (/^(es|en|pt|fr|zh)$/.test(this.value)) window.location.href='?lang='+encodeURIComponent(this.value)">
                <?php foreach (IDIOMAS_DISPONIBLES as $codigoIdioma): ?>
                    <option value="<?= htmlspecialchars($codigoIdioma, ENT_QUOTES, 'UTF-8') ?>" <?= $codigoIdioma === $langActual ? 'selected' : '' ?>>
                        <?= htmlspecialchars(strtoupper($codigoIdioma), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>

            <div class="denied-icon">
                <i class="bi bi-x-circle-fill"></i>
            </div>

            <h1><?= tt('denied_title') ?></h1>

            <p class="lead">
                <?= $sesionExpirada ? tt('denied_session_expired') : tt('denied_no_access') ?>
            </p>

            <p class="text-muted">
                <?= tt('denied_login_prompt') ?>
            </p>

            <a href="index.php" class="btn btn-primary-custom mt-3">
                <?= tt('denied_retry_button') ?>
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

    </div>

</body>
</html>
