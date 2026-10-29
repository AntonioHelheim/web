<?php

require __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/app/Config/MutualityOptions.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/auth.php';

requireLoginPage();
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = currentUserId();
$service = sctOnboarding($pdo);
$status = $service->status($userId);

if (!empty($status['complete'])) {
    header('Location: bienvenida.php');
    exit;
}

$profile = $service->profile($userId) ?: [];

$storedProfileLanguage = normalizarIdiomaUsuario(
    (string)($profile['language'] ?? IDIOMA_POR_DEFECTO)
);

if (
    !isset($_GET['lang'])
    && idiomaActual() !== $storedProfileLanguage
) {
    $_SESSION['site_lang'] = $storedProfileLanguage;
    $GLOBALS['__strings'] = require __DIR__ . '/lang/' . $storedProfileLanguage . '.php';
    $GLOBALS['__lang_actual'] = $storedProfileLanguage;
}

$presentation = $service->profilePresentation($userId);
$roleLabels = $presentation['role_labels'] ?? [];
$projects = $presentation['projects'] ?? [];
$step = (string)$status['next_step'];
$requestedStep = strtolower(trim((string)($_GET['step'] ?? '')));

if (
    !$status['complete']
    && $status['profile_complete']
    && !$status['assessment_complete']
    && $requestedStep === 'profile'
) {
    $step = 'profile';
}

$hasWorker = !empty($profile['id_worker']);
$profileLanguage = normalizarIdiomaUsuario((string)($profile['language'] ?? IDIOMA_POR_DEFECTO));
$selectedContractorId = (int)($profile['id_contractor_company'] ?? 0);
$selectedContractorName = trim((string)($profile['contractor_business_name'] ?? ''));
$selectedContractorRut = trim((string)($profile['contractor_rut'] ?? ''));
$selectedContractorLabel = trim(
    $selectedContractorName
    . ($selectedContractorRut !== '' ? ' · ' . $selectedContractorRut : '')
);

$requiresHealthProfile = !empty($presentation['requires_health_profile']);
$healthProfile = [];

if ($requiresHealthProfile) {
    try {
        $healthProfile = healthCurrentProfile($pdo, $userId) ?: [];
    } catch (Throwable $e) {
        $healthProfile = [];
    }
}

$healthConditions = is_array($healthProfile['conditions_json'] ?? null)
    ? $healthProfile['conditions_json']
    : [];
$healthAllergies = is_array($healthProfile['allergies_json'] ?? null)
    ? $healthProfile['allergies_json']
    : [];
$healthOccupationalDiseases = is_array($healthProfile['occupational_diseases_json'] ?? null)
    ? $healthProfile['occupational_diseases_json']
    : [];

$healthRelation2 = (string)($healthProfile['emergency_relation_2'] ?? '');
if (in_array($healthRelation2, ['parent', 'child', 'sibling', 'family'], true)) {
    $healthRelation2 = 'direct_family';
}

$healthOptions = [
    'conditions' => [
        'asthma' => 'health_condition_asthma',
        'diabetes' => 'health_condition_diabetes',
        'epilepsy' => 'health_condition_epilepsy',
        'hypertension' => 'health_condition_hypertension',
        'cardiac' => 'health_condition_cardiac',
        'severe_allergy' => 'health_condition_severe_allergy',
        'musculoskeletal' => 'health_condition_musculoskeletal',
        'other' => 'health_condition_other',
        'none' => 'health_none',
        'prefer_not' => 'health_prefer_not',
    ],
    'allergies' => [
        'medicines' => 'health_allergy_medicines',
        'food' => 'health_allergy_food',
        'insects' => 'health_allergy_insects',
        'latex' => 'health_allergy_latex',
        'other' => 'health_allergy_other',
        'none' => 'health_none',
        'prefer_not' => 'health_prefer_not',
    ],
    'diseases' => [
        'hearing' => 'health_occ_hearing',
        'silicosis' => 'health_occ_silicosis',
        'musculoskeletal' => 'health_occ_musculoskeletal',
        'dermatitis' => 'health_occ_dermatitis',
        'chemical' => 'health_occ_chemical',
        'uv' => 'health_occ_uv',
        'altitude' => 'health_occ_altitude',
        'mental' => 'health_occ_mental',
        'other' => 'health_occ_other',
    ],
];

$healthRelationOptions = [
    'partner' => 'onboarding_relation_partner',
    'direct_family' => 'onboarding_relation_direct_family',
    'friend' => 'onboarding_relation_friend',
    'other' => 'onboarding_relation_other',
];
?>
<!doctype html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>" class="sct-app-frontend">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= tt('onboarding_page_title') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >
    <link
        rel="stylesheet"
        href="css/style.css?v=<?= htmlspecialchars(SCT_ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"
    >
</head>

<body class="worker-home-page onboarding-page">
<main
    class="onboarding-shell"
    data-step="<?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?>"
    data-csrf="<?= htmlspecialchars((string)$_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
    data-msg-answer-all="<?= tt('onboarding_answer_all') ?>"
    data-msg-result="<?= tt('onboarding_result_prefix') ?>"
    data-msg-correct="<?= tt('onboarding_correct_answers') ?>"
    data-msg-contractor-empty="<?= tt('onboarding_contractor_search_empty') ?>"
    data-msg-health-required="<?= tt('onboarding_error_health_required') ?>"
    data-msg-draft-saving="<?= tt('onboarding_assessment_draft_saving') ?>"
    data-msg-draft-saved="<?= tt('onboarding_assessment_draft_saved') ?>"
    data-msg-draft-error="<?= tt('onboarding_error_assessment_draft') ?>"
    data-language-endpoint="api/onboarding/idioma-guardar.php"
    data-current-language="<?= htmlspecialchars($profileLanguage, ENT_QUOTES, 'UTF-8') ?>"
    data-msg-language-saving="<?= tt('onboarding_language_saving') ?>"
    data-msg-language-error="<?= tt('onboarding_error_language_change') ?>"
    data-msg-profile-required="<?= tt('onboarding_profile_missing_fields') ?>"
>
    <header class="onboarding-header">
        <img src="images/logos/Logo-SCT-white.png" alt="Safety Control Tower">

        <div class="onboarding-header__actions">
            <div class="dropdown">
                <button
                    class="btn btn-outline-custom btn-sm dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="bi bi-translate"></i>
                    <?= htmlspecialchars(idiomasDisponiblesConNombre()[idiomaActual()] ?? strtoupper(idiomaActual()), ENT_QUOTES, 'UTF-8') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php foreach (idiomasDisponiblesConNombre() as $languageCode => $languageName): ?>
                        <li>
                            <a
                                class="dropdown-item <?= idiomaActual() === $languageCode ? 'active' : '' ?>"
                                href="?step=<?= urlencode($step) ?>&lang=<?= urlencode($languageCode) ?>"
                                data-onboarding-language="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <?= htmlspecialchars($languageName, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <a href="logout.php" class="btn btn-outline-custom btn-sm">
                <i class="bi bi-box-arrow-right"></i>
                <?= tt('onboarding_logout') ?>
            </a>
        </div>
    </header>

    <section class="onboarding-progress" aria-label="<?= tt('onboarding_page_title') ?>">
        <div class="onboarding-progress__item <?= $step === 'profile' ? 'is-active' : 'is-complete' ?>">
            <span>1</span>
            <div>
                <strong><?= tt('onboarding_step1_title') ?></strong>
                <small><?= tt('onboarding_step1_subtitle') ?></small>
            </div>
        </div>

        <div class="onboarding-progress__line"></div>

        <div class="onboarding-progress__item <?= $step === 'assessment' ? 'is-active' : '' ?>">
            <span>2</span>
            <div>
                <strong><?= tt('onboarding_step2_title') ?></strong>
                <small><?= tt('onboarding_step2_subtitle') ?></small>
            </div>
        </div>

        <div class="onboarding-progress__line"></div>

        <div class="onboarding-progress__item">
            <span>3</span>
            <div>
                <strong><?= tt('onboarding_step3_title') ?></strong>
                <small><?= tt('onboarding_step3_subtitle') ?></small>
            </div>
        </div>
    </section>

    <div id="onboardingAlert" class="alert d-none" role="alert"></div>

    <?php if ($step === 'profile'): ?>
        <section class="feature-card onboarding-card">
            <div class="onboarding-card__heading">
                <span class="section-label"><?= tt('onboarding_profile_badge') ?></span>
                <h1><?= tt('onboarding_profile_title') ?></h1>
                <p><?= tt('onboarding_profile_intro') ?></p>
            </div>

            <form id="onboardingProfileForm" class="onboarding-form onboarding-form--profile-cards">
                <section class="onboarding-form-section onboarding-profile-section" data-accent="1">
                    <div class="onboarding-form-section__head">
                        <span class="onboarding-form-section__step">01</span>
                        <div>
                            <h2><?= tt('onboarding_identity_section') ?></h2>
                            <p><?= tt('onboarding_identity_section_help') ?></p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label"><?= tt('onboarding_email') ?></label>
                            <div class="onboarding-readonly-field">
                                <i class="bi bi-envelope"></i>
                                <input
                                    class="form-control"
                                    value="<?= htmlspecialchars((string)($profile['id_users'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    disabled
                                >
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label"><?= tt('onboarding_company') ?></label>
                            <div class="onboarding-readonly-field">
                                <i class="bi bi-building"></i>
                                <input
                                    class="form-control"
                                    value="<?= htmlspecialchars((string)($profile['razon_social'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    disabled
                                >
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label"><?= tt('onboarding_user_group') ?></label>
                            <div class="onboarding-readonly-field">
                                <i class="bi bi-person-badge"></i>
                                <input
                                    class="form-control"
                                    value="<?= htmlspecialchars(implode(' / ', $roleLabels), ENT_QUOTES, 'UTF-8') ?>"
                                    disabled
                                >
                            </div>
                            <small class="form-text text-muted">
                                <?= tt('onboarding_user_group_help') ?>
                            </small>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label">
                                <?= tt('onboarding_project') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>

                            <?php
                            $indefiniteSelected =
                                !empty($profile['confirmed_at'])
                                && empty($profile['confirmed_project_id']);
                            ?>
                            <select
                                class="form-select"
                                name="confirmed_project_id"
                                required
                                data-profile-required
                            >
                                <option value=""><?= tt('onboarding_select_option') ?></option>

                                <?php foreach ($projects as $project): ?>
                                    <option
                                        value="<?= (int)$project['id_project'] ?>"
                                        <?= (int)($profile['confirmed_project_id'] ?? 0) === (int)$project['id_project'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars((string)$project['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>

                                <option
                                    value="indefinite"
                                    <?= $indefiniteSelected ? 'selected' : '' ?>
                                >
                                    <?= tt('onboarding_project_indefinite') ?>
                                </option>
                            </select>

                            <small class="form-text text-muted">
                                <?= tt('onboarding_project_help') ?>
                            </small>
                        </div>
                    </div>
                </section>

                <section class="onboarding-form-section onboarding-profile-section" data-accent="2">
                    <div class="onboarding-form-section__head">
                        <span class="onboarding-form-section__step">02</span>
                        <div>
                            <h2><?= tt('onboarding_personal_work_section') ?></h2>
                            <p><?= tt('onboarding_personal_work_help') ?></p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <?= tt('onboarding_name') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                class="form-control"
                                name="name"
                                required
                                maxlength="50"
                                autocomplete="given-name"
                                value="<?= htmlspecialchars((string)($profile['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <?= tt('onboarding_lastname') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                class="form-control"
                                name="lastname"
                                required
                                maxlength="50"
                                autocomplete="family-name"
                                value="<?= htmlspecialchars((string)($profile['lastname'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <?= tt('onboarding_rut') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                class="form-control"
                                name="rut"
                                required
                                maxlength="10"
                                inputmode="text"
                                placeholder="12345678-5"
                                value="<?= htmlspecialchars((string)($profile['rut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <?= tt('onboarding_language') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                class="form-select"
                                name="language"
                                data-onboarding-language-select
                                required
                            >
                                <?php foreach (idiomasDisponiblesConNombre() as $languageCode => $languageName): ?>
                                    <option
                                        value="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= $languageCode === $profileLanguage ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($languageName, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                <?= tt('onboarding_language_immediate_help') ?>
                            </small>
                        </div>

                        <?php if ($hasWorker): ?>
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    <?= tt('onboarding_phone') ?>
                                    <span class="onboarding-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    class="form-control"
                                    name="phone"
                                    type="tel"
                                    required
                                    maxlength="20"
                                    autocomplete="tel"
                                    value="<?= htmlspecialchars((string)($profile['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    <?= tt('onboarding_position') ?>
                                    <span class="onboarding-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    class="form-control"
                                    name="position"
                                    required
                                    maxlength="100"
                                    value="<?= htmlspecialchars((string)($profile['position'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <?= tt('onboarding_experience_years') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>
                            <div class="onboarding-number-field">
                                <input
                                    class="form-control"
                                    type="number"
                                    name="years_experience_current_role"
                                    min="0"
                                    max="80"
                                    step="1"
                                    required
                                    value="<?= htmlspecialchars(
                                        isset($profile['years_experience_current_role'])
                                            ? (string)$profile['years_experience_current_role']
                                            : '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                <span><?= tt('onboarding_years_suffix') ?></span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="onboarding-form-section onboarding-profile-section" data-accent="3">
                    <div class="onboarding-form-section__head">
                        <span class="onboarding-form-section__step">03</span>
                        <div>
                            <h2><?= tt('onboarding_employment_section') ?></h2>
                            <p><?= tt('onboarding_employment_section_help') ?></p>
                        </div>
                    </div>

                    <div class="row g-3 align-items-start">
                        <div class="col-12 col-lg-5">
                            <label class="form-label">
                                <?= tt('onboarding_contractor_question') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>
                            <select
                                class="form-select"
                                name="hired_by_contractor"
                                id="hiredByContractor"
                                required
                            >
                                <option value=""><?= tt('onboarding_select_option') ?></option>
                                <option
                                    value="no"
                                    <?= (int)($profile['hired_by_contractor'] ?? -1) === 0 && $profile['confirmed_at'] ? 'selected' : '' ?>
                                >
                                    <?= tt('common_no') ?>
                                </option>
                                <option
                                    value="yes"
                                    <?= (int)($profile['hired_by_contractor'] ?? 0) === 1 ? 'selected' : '' ?>
                                >
                                    <?= tt('common_yes') ?>
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-7" id="contractorNameWrap">
                            <label class="form-label">
                                <?= tt('onboarding_contractor_name') ?>
                                <span class="onboarding-required" aria-hidden="true">*</span>
                            </label>

                            <div
                                class="onboarding-search-select"
                                id="contractorSearchSelect"
                                data-endpoint="api/onboarding/contratistas-buscar.php"
                            >
                                <input
                                    type="hidden"
                                    name="id_contractor_company"
                                    id="contractorId"
                                    value="<?= $selectedContractorId > 0 ? $selectedContractorId : '' ?>"
                                >

                                <div class="onboarding-search-select__control">
                                    <i class="bi bi-search"></i>
                                    <input
                                        class="form-control"
                                        type="search"
                                        id="contractorSearchInput"
                                        autocomplete="off"
                                        placeholder="<?= tt('onboarding_contractor_search_placeholder') ?>"
                                        value="<?= htmlspecialchars($selectedContractorLabel, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                    <button
                                        class="onboarding-search-select__clear"
                                        id="contractorClearButton"
                                        type="button"
                                        aria-label="<?= tt('onboarding_contractor_clear') ?>"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div
                                    class="onboarding-search-select__menu"
                                    id="contractorSearchMenu"
                                    hidden
                                ></div>
                            </div>

                            <small class="form-text text-muted">
                                <?= tt('onboarding_contractor_search_help') ?>
                            </small>
                        </div>

                        <div class="col-12">
                            <div class="onboarding-field-title">
                                <div>
                                    <label class="form-label mb-1">
                                        <?= tt('onboarding_mutuality') ?>
                                        <span class="onboarding-required" aria-hidden="true">*</span>
                                    </label>
                                    <p class="onboarding-field-help"><?= tt('onboarding_mutuality_help') ?></p>
                                </div>
                            </div>

                            <div
                                class="onboarding-mutuality-grid"
                                role="radiogroup"
                                aria-label="<?= tt('onboarding_mutuality') ?>"
                            >
                                <?php foreach (SctMutualityOptions::OPTIONS as $code => $option): ?>
                                    <?php
                                    $inputId = 'onboardingMutuality_' . $code;
                                    $checked = strtolower((string)($profile['mutual_code'] ?? '')) === $code;
                                    ?>
                                    <label
                                        class="onboarding-mutuality-option"
                                        for="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <input
                                            type="radio"
                                            name="mutual_code"
                                            id="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8') ?>"
                                            value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $checked ? 'checked' : '' ?>
                                            required
                                        >
                                        <span class="onboarding-mutuality-option__logo">
                                            <img
                                                src="<?= htmlspecialchars($option['logo'], ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($option['sigla'], ENT_QUOTES, 'UTF-8') ?>"
                                                loading="lazy"
                                            >
                                        </span>
                                        <span class="onboarding-mutuality-option__copy">
                                            <strong><?= htmlspecialchars($option['sigla'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small><?= htmlspecialchars($option['name'], ENT_QUOTES, 'UTF-8') ?></small>
                                        </span>
                                        <span class="onboarding-mutuality-option__check" aria-hidden="true">
                                            <i class="bi bi-check2"></i>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="onboarding-form-section onboarding-profile-section" data-accent="4">
                    <div class="onboarding-form-section__head">
                        <span class="onboarding-form-section__step">04</span>
                        <div>
                            <h2><?= tt('onboarding_emergency_section') ?></h2>
                            <p><?= tt('onboarding_emergency_section_help') ?></p>
                        </div>
                    </div>

                    <div class="onboarding-contact-card">
                        <div class="onboarding-contact-card__head">
                            <div>
                                <strong><?= tt('onboarding_emergency_primary') ?></strong>
                                <small><?= tt('onboarding_required_contact') ?></small>
                            </div>
                            <i class="bi bi-person-fill-exclamation"></i>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-5">
                                <label class="form-label">
                                    <?= tt('onboarding_emergency_name') ?>
                                    <span class="onboarding-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    class="form-control"
                                    name="emergency_contact_name"
                                    maxlength="150"
                                    autocomplete="name"
                                    required
                                    value="<?= htmlspecialchars((string)($profile['emergency_contact_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label">
                                    <?= tt('onboarding_emergency_phone') ?>
                                    <span class="onboarding-required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    class="form-control"
                                    type="tel"
                                    name="emergency_contact_phone"
                                    maxlength="30"
                                    autocomplete="tel"
                                    required
                                    value="<?= htmlspecialchars((string)($profile['emergency_contact_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">
                                    <?= tt('onboarding_emergency_relation_primary') ?>
                                    <span class="onboarding-required" aria-hidden="true">*</span>
                                </label>
                                <select class="form-select" name="emergency_contact_relation" required>
                                    <option value=""><?= tt('onboarding_select_option') ?></option>
                                    <?php foreach ($healthRelationOptions as $value => $labelKey): ?>
                                        <option
                                            value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= ($profile['emergency_contact_relation'] ?? '') === $value ? 'selected' : '' ?>
                                        >
                                            <?= tt($labelKey) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if ($requiresHealthProfile): ?>
                        <div class="onboarding-contact-card onboarding-contact-card--secondary">
                            <div class="onboarding-contact-card__head">
                                <div>
                                    <strong><?= tt('onboarding_emergency_secondary') ?></strong>
                                    <small><?= tt('onboarding_optional_contact') ?></small>
                                </div>
                                <i class="bi bi-person-plus"></i>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label"><?= tt('health_full_name') ?></label>
                                    <input
                                        class="form-control"
                                        name="emergency_name_2"
                                        maxlength="150"
                                        autocomplete="name"
                                        value="<?= htmlspecialchars((string)($healthProfile['emergency_name_2'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                </div>

                                <div class="col-12 col-sm-6 col-lg-4">
                                    <label class="form-label"><?= tt('onboarding_emergency_phone') ?></label>
                                    <input
                                        class="form-control"
                                        name="emergency_phone_2"
                                        type="tel"
                                        maxlength="30"
                                        autocomplete="tel"
                                        value="<?= htmlspecialchars((string)($healthProfile['emergency_phone_2'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <label class="form-label"><?= tt('onboarding_emergency_relation_secondary') ?></label>
                                    <select class="form-select" name="emergency_relation_2">
                                        <option value=""><?= tt('onboarding_select_option') ?></option>
                                        <?php foreach ($healthRelationOptions as $value => $labelKey): ?>
                                            <option
                                                value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $healthRelation2 === $value ? 'selected' : '' ?>
                                            >
                                                <?= tt($labelKey) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <p class="onboarding-contact-card__help">
                                <i class="bi bi-info-circle"></i>
                                <?= tt('onboarding_emergency_secondary_help') ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if ($requiresHealthProfile): ?>
                    <section class="onboarding-form-section onboarding-form-section--health onboarding-profile-section" data-accent="5">
                        <div class="onboarding-form-section__head">
                            <span class="onboarding-form-section__step">05</span>
                            <div>
                                <h2><?= tt('onboarding_health_occupational_section') ?></h2>
                                <p><?= tt('onboarding_health_occupational_intro') ?></p>
                            </div>
                        </div>

                        <div class="onboarding-health-panel" data-onboarding-health>
                        <fieldset class="health-fieldset" data-onboarding-exclusive="conditions">
                            <legend><?= tt('health_conditions') ?></legend>
                            <div class="health-check-grid">
                                <?php foreach ($healthOptions['conditions'] as $value => $labelKey): ?>
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="conditions[]"
                                            value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= in_array($value, $healthConditions, true) ? 'checked' : '' ?>
                                        >
                                        <span><?= tt($labelKey) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>

                        <div
                            class="mt-3 <?= in_array('other', $healthConditions, true) ? '' : 'd-none' ?>"
                            data-onboarding-show-if="conditions:other"
                        >
                            <label class="form-label"><?= tt('health_indicate_which') ?></label>
                            <input
                                class="form-control"
                                name="condition_other"
                                maxlength="255"
                                value="<?= htmlspecialchars((string)($healthProfile['condition_other_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="health-subcard">
                            <label class="form-label d-block"><?= tt('health_medications') ?></label>
                            <div class="health-radio-row">
                                <?php foreach ([
                                    'yes' => 'health_yes',
                                    'no' => 'health_no',
                                    'prefer_not' => 'health_prefer_not',
                                ] as $value => $labelKey): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="medication_choice"
                                            value="<?= $value ?>"
                                            <?= ($healthProfile['medication_choice'] ?? '') === $value ? 'checked' : '' ?>
                                        >
                                        <span><?= tt($labelKey) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="row g-3 mt-1 <?= ($healthProfile['medication_choice'] ?? '') === 'yes' ? '' : 'd-none' ?>"
                                data-onboarding-show-if="medication_choice:yes"
                            >
                                <div class="col-12 col-md-6">
                                    <label class="form-label"><?= tt('health_medications_names') ?></label>
                                    <textarea
                                        class="form-control"
                                        name="medications_text"
                                        rows="2"
                                        maxlength="1000"
                                    ><?= htmlspecialchars((string)($healthProfile['medications_text_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label"><?= tt('health_medications_emergency') ?></label>
                                    <textarea
                                        class="form-control"
                                        name="medications_emergency"
                                        rows="2"
                                        maxlength="1500"
                                    ><?= htmlspecialchars((string)($healthProfile['medications_emergency_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <fieldset class="health-fieldset" data-onboarding-exclusive="allergies">
                            <legend><?= tt('health_allergies') ?></legend>
                            <div class="health-check-grid">
                                <?php foreach ($healthOptions['allergies'] as $value => $labelKey): ?>
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="allergies[]"
                                            value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= in_array($value, $healthAllergies, true) ? 'checked' : '' ?>
                                        >
                                        <span><?= tt($labelKey) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>

                        <?php
                        $hasHealthAllergy = (bool)array_diff(
                            $healthAllergies,
                            ['none', 'prefer_not']
                        );
                        ?>
                        <div
                            class="mt-3 <?= $hasHealthAllergy ? '' : 'd-none' ?>"
                            data-onboarding-show-if="has_allergy"
                        >
                            <label class="form-label"><?= tt('health_allergy_details') ?></label>
                            <textarea
                                class="form-control"
                                name="allergy_details"
                                rows="2"
                                maxlength="1000"
                            ><?= htmlspecialchars((string)($healthProfile['allergy_details_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

                            <label class="form-label mt-3 d-block"><?= tt('health_severe_reaction') ?></label>
                            <div class="health-radio-row">
                                <?php foreach ([
                                    'yes' => 'health_yes',
                                    'no' => 'health_no',
                                    'dont_know' => 'health_dont_know',
                                ] as $value => $labelKey): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="severe_reaction"
                                            value="<?= $value ?>"
                                            <?= ($healthProfile['severe_reaction'] ?? '') === $value ? 'checked' : '' ?>
                                        >
                                        <span><?= tt($labelKey) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="mt-3 <?= ($healthProfile['severe_reaction'] ?? '') === 'yes' ? '' : 'd-none' ?>"
                                data-onboarding-show-if="severe_reaction:yes"
                            >
                                <label class="form-label"><?= tt('health_severe_info') ?></label>
                                <textarea
                                    class="form-control"
                                    name="severe_reaction_info"
                                    rows="2"
                                    maxlength="1500"
                                ><?= htmlspecialchars((string)($healthProfile['severe_reaction_info_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>

                        <div class="health-subcard">
                            <label class="form-label d-block"><?= tt('health_occupational_question') ?></label>
                            <div class="health-radio-row">
                                <?php foreach ([
                                    'yes' => 'health_yes',
                                    'no' => 'health_no',
                                    'under_evaluation' => 'health_under_evaluation',
                                    'prefer_not' => 'health_prefer_not',
                                ] as $value => $labelKey): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="occupational_choice"
                                            value="<?= $value ?>"
                                            <?= ($healthProfile['occupational_choice'] ?? '') === $value ? 'checked' : '' ?>
                                        >
                                        <span><?= tt($labelKey) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="mt-3 <?= ($healthProfile['occupational_choice'] ?? '') === 'yes' ? '' : 'd-none' ?>"
                                data-onboarding-show-if="occupational_choice:yes"
                            >
                                <fieldset class="health-fieldset">
                                    <legend><?= tt('health_occupational_which') ?></legend>
                                    <div class="health-check-grid">
                                        <?php foreach ($healthOptions['diseases'] as $value => $labelKey): ?>
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    name="occupational_diseases[]"
                                                    value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= in_array($value, $healthOccupationalDiseases, true) ? 'checked' : '' ?>
                                                >
                                                <span><?= tt($labelKey) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>

                                <div
                                    class="mt-3 <?= in_array('other', $healthOccupationalDiseases, true) ? '' : 'd-none' ?>"
                                    data-onboarding-show-if="occupational_diseases:other"
                                >
                                    <label class="form-label"><?= tt('health_indicate_which') ?></label>
                                    <textarea
                                        class="form-control"
                                        name="occupational_other"
                                        rows="2"
                                        maxlength="1000"
                                    ><?= htmlspecialchars((string)($healthProfile['occupational_other_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="health-subcard">
                            <label class="form-label d-block"><?= tt('health_restriction_question') ?></label>
                            <div class="health-radio-row">
                                <?php foreach ([
                                    'yes' => 'health_yes',
                                    'no' => 'health_no',
                                    'prefer_not' => 'health_prefer_not',
                                ] as $value => $labelKey): ?>
                                    <label>
                                        <input
                                            type="radio"
                                            name="restriction_choice"
                                            value="<?= $value ?>"
                                            <?= ($healthProfile['restriction_choice'] ?? '') === $value ? 'checked' : '' ?>
                                        >
                                        <span><?= tt($labelKey) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="mt-3 <?= ($healthProfile['restriction_choice'] ?? '') === 'yes' ? '' : 'd-none' ?>"
                                data-onboarding-show-if="restriction_choice:yes"
                            >
                                <label class="form-label"><?= tt('health_restriction_details') ?></label>
                                <textarea
                                    class="form-control"
                                    name="restriction_details"
                                    rows="3"
                                    maxlength="1500"
                                ><?= htmlspecialchars((string)($healthProfile['restriction_details_enc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>

                        <div class="health-declaration onboarding-health-declaration onboarding-health-declaration--notice">
                            <i class="bi bi-shield-lock"></i>
                            <div>
                                <strong><?= tt('onboarding_sensitive_data_title') ?></strong>
                                <p><?= tt('onboarding_sensitive_data_notice') ?></p>
                            </div>
                        </div>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="onboarding-form-section onboarding-form-section--consent onboarding-profile-section" data-accent="1">
                    <div class="onboarding-form-section__head">
                        <span class="onboarding-form-section__step"><?= $requiresHealthProfile ? '06' : '05' ?></span>
                        <div>
                            <h2><?= tt('onboarding_consent_section') ?></h2>
                            <p><?= tt('onboarding_consent_section_help') ?></p>
                        </div>
                    </div>

                    <div class="onboarding-consent onboarding-consent--unified">
                        <div class="onboarding-consent__summary">
                            <i class="bi bi-file-earmark-check"></i>
                            <div>
                                <strong><?= tt('onboarding_unified_consent_title') ?></strong>
                            </div>
                        </div>

                        <div class="form-check onboarding-consent__acceptance">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="dataConsent"
                                name="accepted"
                                value="1"
                                required
                            >
                            <label class="form-check-label" for="dataConsent">
                                <?= tt('onboarding_unified_consent_text') ?>
                            </label>
                        </div>

                        <small class="onboarding-consent__version">
                            <?= tt('onboarding_consent_version') ?>:
                            <?= htmlspecialchars(SctOnboardingService::CONSENT_VERSION, ENT_QUOTES, 'UTF-8') ?>.
                        </small>
                    </div>
                </section>

                <div class="onboarding-form-footer">
                    <span class="onboarding-form-footer__hint">
                        <i class="bi bi-shield-check"></i>
                        <?= tt('onboarding_required_hint') ?>
                    </span>

                    <button class="btn btn-primary-custom onboarding-submit-button" type="submit">
                        <span><?= tt('onboarding_save_continue') ?></span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>
        </section>
    <?php else: ?>
        <section class="feature-card onboarding-card onboarding-card--assessment">
            <div class="onboarding-card__heading onboarding-card__heading--assessment">
                <span class="section-label"><?= tt('onboarding_assessment_badge') ?></span>
                <h1><?= tt('onboarding_assessment_title') ?></h1>
                <p><?= tt('onboarding_assessment_intro') ?></p>
            </div>

            <div id="onboardingAssessmentLoading" class="alert alert-info">
                <?= tt('onboarding_assessment_loading') ?>
            </div>

            <form id="onboardingAssessmentForm" class="d-none">
                <input type="hidden" id="onboardingAttemptId">
                <div id="onboardingQuestions" class="onboarding-questions-grid"></div>

                <div class="mt-4 onboarding-assessment-actions">
                    <div class="onboarding-assessment-actions__left">
                        <a
                            class="btn btn-outline-custom"
                            id="onboardingBackToProfile"
                            href="onboarding.php?step=profile"
                        >
                            <i class="bi bi-arrow-left"></i>
                            <?= tt('onboarding_back_to_profile') ?>
                        </a>

                        <span
                            class="onboarding-draft-status"
                            id="onboardingDraftStatus"
                            role="status"
                            aria-live="polite"
                        ></span>
                    </div>

                    <button class="btn btn-primary-custom" type="submit">
                        <?= tt('onboarding_assessment_save_submit') ?>
                        <i class="bi bi-check2-circle"></i>
                    </button>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($step === 'assessment'): ?>
        <div
            class="modal fade onboarding-result-modal"
            id="onboardingAssessmentResultModal"
            tabindex="-1"
            aria-labelledby="onboardingAssessmentResultTitle"
            aria-describedby="onboardingAssessmentResultDescription"
            aria-hidden="true"
            data-bs-backdrop="static"
            data-bs-keyboard="false"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header onboarding-result-modal__header">
                        <div class="onboarding-result-modal__icon" aria-hidden="true">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <span class="section-label"><?= tt('onboarding_result_modal_badge') ?></span>
                            <h2 class="modal-title h5 mb-0" id="onboardingAssessmentResultTitle">
                                <?= tt('onboarding_result_modal_title') ?>
                            </h2>
                        </div>
                    </div>

                    <div class="modal-body">
                        <p
                            class="onboarding-result-modal__intro"
                            id="onboardingAssessmentResultDescription"
                        >
                            <?= tt('onboarding_result_modal_intro') ?>
                        </p>

                        <div class="onboarding-result-summary">
                            <div class="onboarding-result-summary__item is-correct">
                                <span class="onboarding-result-summary__icon">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <div>
                                    <small><?= tt('onboarding_result_correct') ?></small>
                                    <strong id="onboardingResultCorrectPercentage">0%</strong>
                                    <span id="onboardingResultCorrectCount">0 / 15</span>
                                </div>
                            </div>

                            <div class="onboarding-result-summary__item is-incorrect">
                                <span class="onboarding-result-summary__icon">
                                    <i class="bi bi-x-lg"></i>
                                </span>
                                <div>
                                    <small><?= tt('onboarding_result_incorrect') ?></small>
                                    <strong id="onboardingResultIncorrectPercentage">0%</strong>
                                    <span id="onboardingResultIncorrectCount">0 / 15</span>
                                </div>
                            </div>
                        </div>

                        <div class="onboarding-result-modal__score" id="onboardingResultScoreBox">
                            <span><?= tt('onboarding_result_final') ?></span>
                            <strong id="onboardingResultScore">0%</strong>
                        </div>

                        <p class="onboarding-result-modal__note">
                            <?= tt('onboarding_result_modal_note') ?>
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button
                            class="btn btn-primary-custom onboarding-result-modal__continue"
                            id="onboardingAssessmentResultContinue"
                            type="button"
                        >
                            <?= tt('onboarding_result_modal_continue') ?>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/onboarding.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
