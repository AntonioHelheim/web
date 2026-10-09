<?php

require __DIR__ . '/common.php';

questionBankRequirePage($pdo);
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$jsStrings = [
    'loading'=>t('question_bank_loading'),
    'error'=>t('question_bank_error'),
    'saved'=>t('question_bank_saved'),
    'new_question'=>t('question_bank_new_question'),
    'edit_question'=>t('question_bank_edit_question'),
    'active'=>t('question_bank_active'),
    'inactive'=>t('question_bank_inactive'),
    'all_languages_required'=>t('question_bank_all_languages_required'),
    'correct_answer'=>t('question_bank_correct_answer'),
    'confirm_new'=>t('question_bank_confirm_new'),
];
?>
<!doctype html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>" class="sct-app-frontend">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(t('question_bank_page_title'),ENT_QUOTES,'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<script>document.documentElement.classList.add('sct-app-frontend');try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
</head>
<body>
<?php $sctNavbarBasePath='../../';require __DIR__.'/../../partials/app-navbar.php'; ?>

<main
    class="sct-page-shell question-bank-shell"
    data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'],ENT_QUOTES,'UTF-8') ?>"
>
    <section class="worker-home__greeting">
        <div>
            <span class="worker-home__eyebrow">Safety Control Tower · E3-VS1</span>
            <h1><?= htmlspecialchars(t('question_bank_title'),ENT_QUOTES,'UTF-8') ?></h1>
            <p><?= htmlspecialchars(t('question_bank_intro'),ENT_QUOTES,'UTF-8') ?></p>
        </div>
        <button id="questionBankNew" class="btn btn-primary-custom" type="button">
            <i class="bi bi-plus-lg"></i>
            <?= htmlspecialchars(t('question_bank_new_question'),ENT_QUOTES,'UTF-8') ?>
        </button>
    </section>

    <div id="questionBankAlert" class="alert d-none" role="alert"></div>

    <section class="feature-card question-bank-layout">
        <aside class="question-bank-list-panel">
            <div class="question-bank-filter">
                <i class="bi bi-search"></i>
                <input
                    id="questionBankSearch"
                    class="form-control"
                    type="search"
                    placeholder="<?= htmlspecialchars(t('question_bank_search'),ENT_QUOTES,'UTF-8') ?>"
                >
            </div>
            <div id="questionBankList" class="question-bank-list"></div>
        </aside>

        <section class="question-bank-editor-panel">
            <div id="questionBankEmpty" class="question-bank-empty">
                <i class="bi bi-translate"></i>
                <p><?= htmlspecialchars(t('question_bank_select_question'),ENT_QUOTES,'UTF-8') ?></p>
            </div>

            <form id="questionBankForm" class="d-none">
                <input type="hidden" id="questionBankId">

                <div class="question-bank-editor-head">
                    <div>
                        <span id="questionBankCode" class="section-label"></span>
                        <h2 id="questionBankEditorTitle" class="h5 mb-1"></h2>
                    </div>

                    <label class="form-check form-switch">
                        <input id="questionBankState" class="form-check-input" type="checkbox" checked>
                        <span class="form-check-label"><?= htmlspecialchars(t('question_bank_state'),ENT_QUOTES,'UTF-8') ?></span>
                    </label>
                </div>

                <ul class="nav nav-tabs question-bank-language-tabs" role="tablist">
                    <?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link <?= $code==='es'?'active':'' ?>"
                                type="button"
                                data-bs-toggle="tab"
                                data-bs-target="#questionLanguage_<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>"
                                role="tab"
                            >
                                <?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content pt-3">
                    <?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?>
                        <div
                            class="tab-pane fade <?= $code==='es'?'show active':'' ?>"
                            id="questionLanguage_<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>"
                            role="tabpanel"
                            data-language-panel="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>"
                        >
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= htmlspecialchars(t('question_bank_question_text'),ENT_QUOTES,'UTF-8') ?>
                                    · <?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?>
                                </label>
                                <textarea class="form-control" data-question-text rows="3" maxlength="2000" required></textarea>
                            </div>

                            <div class="question-bank-options">
                                <?php for($i=0;$i<4;$i++): ?>
                                    <div class="question-bank-option-row">
                                        <span><?= $i+1 ?></span>
                                        <input
                                            class="form-control"
                                            data-option-text="<?= $i ?>"
                                            maxlength="500"
                                            required
                                        >
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <fieldset class="question-bank-correct mt-3">
                    <legend><?= htmlspecialchars(t('question_bank_correct_answer'),ENT_QUOTES,'UTF-8') ?></legend>
                    <div class="question-bank-correct-options">
                        <?php for($i=0;$i<4;$i++): ?>
                            <label>
                                <input type="radio" name="correct_index" value="<?= $i ?>" <?= $i===0?'checked':'' ?>>
                                <span><?= htmlspecialchars(t('question_bank_option'),ENT_QUOTES,'UTF-8') ?> <?= $i+1 ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </fieldset>

                <div class="question-bank-actions">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="bi bi-check2-circle"></i>
                        <?= htmlspecialchars(t('question_bank_save'),ENT_QUOTES,'UTF-8') ?>
                    </button>
                </div>
            </form>
        </section>
    </section>
</main>

<script id="questionBankI18n" type="application/json"><?= json_encode($jsStrings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/preguntas-onboarding-admin.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
</body>
</html>
