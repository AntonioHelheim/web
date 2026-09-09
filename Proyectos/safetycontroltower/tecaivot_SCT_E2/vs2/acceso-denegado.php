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
    <title><?= htmlspecialchars(t('denied_page_title'), ENT_QUOTES, 'UTF-8') ?></title>

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
            top: 1.5rem;
            right: 1.5rem;
        }
        .denied-language select {
            min-width: 90px;
        }
    </style>
</head>
<body>

    <div class="denied-wrapper">

        <div class="denied-language">
            <label class="sr-only" for="deniedLanguage"><?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?></label>
            <select id="deniedLanguage" class="form-select form-select-sm" aria-label="<?= htmlspecialchars(t('login_language_label'), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach (IDIOMAS_DISPONIBLES as $codigoIdioma): ?>
                    <option value="<?= htmlspecialchars($codigoIdioma, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $codigoIdioma === $langActual ? 'selected' : '' ?>>
                        <?= htmlspecialchars(strtoupper($codigoIdioma), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>

            <div class="denied-icon">
                <i class="bi bi-x-circle-fill"></i>
            </div>

            <h1><?= htmlspecialchars(t('denied_title'), ENT_QUOTES, 'UTF-8') ?></h1>

            <p class="lead">
                <?php echo $sesionExpirada
                    ? htmlspecialchars(t('denied_session_expired'), ENT_QUOTES, 'UTF-8')
                    : htmlspecialchars(t('denied_no_session'), ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <p class="text-muted">
                <?= htmlspecialchars(t('denied_login_prompt'), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <a href="index.php" class="btn btn-primary-custom mt-3">
                <?= htmlspecialchars(t('denied_retry_btn'), ENT_QUOTES, 'UTF-8') ?>
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>

    </div>

    <script>
        (function () {
            var select = document.getElementById("deniedLanguage");
            if (!select) return;
            select.addEventListener("change", function () {
                var lang = select.value;
                if (/^(es|en|pt|fr|zh)$/.test(lang)) {
                    window.location.href = "?lang=" + encodeURIComponent(lang);
                }
            });
        })();
    </script>
</body>
</html>
