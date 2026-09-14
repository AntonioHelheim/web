<!DOCTYPE html>
<html lang="<?php echo idiomaActual(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author"    content="helheim.cl">
    <meta name="copyright" content="Tecaivot">
    <meta name="theme-color" content="#002259">

    <!-- El sitio ya está en producción (dominio + SSL confirmados) —
         se indexa normalmente. Si en algún momento se necesita volver
         a bloquear la indexación (ej. mientras se prueba un cambio
         grande), cambiar a "noindex, nofollow" acá. -->
    <meta name="robots" content="index, follow">

    <?php
    $__urlBase = 'https://www.safetycontroltower.cl/';
    $__langActual = idiomaActual();
    $__urlCanonica = $__langActual === 'es' ? $__urlBase : $__urlBase . '?lang=' . $__langActual;
    ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($__urlCanonica, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="es" href="<?php echo htmlspecialchars($__urlBase, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="en" href="<?php echo htmlspecialchars($__urlBase . '?lang=en', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="fr" href="<?php echo htmlspecialchars($__urlBase . '?lang=fr', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="pt" href="<?php echo htmlspecialchars($__urlBase . '?lang=pt', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="zh" href="<?php echo htmlspecialchars($__urlBase . '?lang=zh', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($__urlBase, ENT_QUOTES, 'UTF-8'); ?>">
<!--==================== Favicon ====================-->
<link rel="icon" href="./images/logos/favicon.ico" sizes="any">
<link rel="apple-touch-icon" sizes="180x180" href="./images/logos/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="./images/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="./images/logos/favicon-16x16.png">
<link rel="manifest" href="./images/logos/site.webmanifest">
<!--==================== Favicon ====================-->

<!--==================== Metatags Generated with https://metatags.io/ ====================-->
<!-- Primary Meta Tags -->
<title><?php echo htmlspecialchars(t('site_title'), ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="title" content="<?php echo htmlspecialchars(t('site_title'), ENT_QUOTES, 'UTF-8'); ?>" />
<meta name="description" content="<?php echo htmlspecialchars(t('meta_description'), ENT_QUOTES, 'UTF-8'); ?>" />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="https://www.safetycontroltower.cl" />
<meta property="og:title" content="<?php echo htmlspecialchars(t('site_title'), ENT_QUOTES, 'UTF-8'); ?>" />
<meta property="og:description" content="<?php echo htmlspecialchars(t('meta_description'), ENT_QUOTES, 'UTF-8'); ?>" />
<meta property="og:image" content="https://www.safetycontroltower.cl/images/logos/logo512-512.png" />

<!-- X (Twitter) -->
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:url" content="https://www.safetycontroltower.cl" />
<meta property="twitter:title" content="<?php echo htmlspecialchars(t('site_title'), ENT_QUOTES, 'UTF-8'); ?>" />
<meta property="twitter:description" content="<?php echo htmlspecialchars(t('meta_description'), ENT_QUOTES, 'UTF-8'); ?>" />
<meta property="twitter:image" content="https://www.safetycontroltower.cl/images/logos/logo512-512.png" />

<!--==================== Metatags Generated with https://metatags.io/ ====================-->

<!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
       <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">

    <!-- Estilos propios -->
    <!-- build: <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?> -->
       <link rel="stylesheet" href="css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Datos estructurados (schema.org) — ayuda a los buscadores a
         entender qué es SCT sin tener que inferirlo del texto. -->
    <script type="application/ld+json">
    <?php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'Safety Control Tower',
        'alternateName' => 'SCT',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'url' => 'https://www.safetycontroltower.cl',
        'description' => t('meta_description'),
        'inLanguage' => ['es', 'en', 'fr', 'pt', 'zh'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Tecaivot',
            'url' => 'https://www.tecaivot.cl',
        ],
        'creator' => [
            '@type' => 'Organization',
            'name' => 'Helheim',
            'url' => 'https://helheim.cl',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>

</head>