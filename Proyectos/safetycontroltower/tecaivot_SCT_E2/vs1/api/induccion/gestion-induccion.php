<?php
require_once __DIR__ . '/common.php';

requireLoginPage('../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$puedeGestionar = count(array_intersect(currentUserRoles($pdo), INDUCCION_ROLES_GESTION)) > 0;
$puedeGestionarBanco = count(array_intersect(currentUserRoles($pdo), INDUCCION_ROLES_BANCO_PREGUNTAS)) > 0;
$isGlobalAdmin = induccionIsGlobalAdmin($pdo);

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inducción - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= $assetVersionEscaped ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">

    <style>
        .welcome-hero { padding: 4rem 0 2rem; }
        .welcome-topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.25rem 0; border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; }
        .welcome-greeting-icon { font-size: 2.5rem; color: #16a34a; margin-bottom: 0.75rem; }
        .quick-links { margin-top: 2rem; margin-bottom: 3rem; }
        .form-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .curso-detalle { display: none; }
        .curso-detalle.activo { display: block; }
        .subseccion { border-top: 1px solid var(--border); padding-top: 1.25rem; margin-top: 1.25rem; }
        .chip-pregunta {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.5rem 0.75rem; border-radius: 10px; background: rgba(0,0,0,0.03);
            margin-bottom: 0.5rem; font-size: 0.9rem;
        }
        .chip-pregunta button { border: none; background: none; color: #dc2626; }
        .badge-estado-1 { color: #d97706; }
        .badge-estado-2 { color: #16a34a; }
        .badge-estado-3 { color: #dc2626; }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>"
         data-is-global-admin="<?php echo $isGlobalAdmin ? '1' : '0'; ?>"
         data-puede-banco="<?php echo $puedeGestionarBanco ? '1' : '0'; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                </div>
            </div>
            <div class="topbar-actions">
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">Volver a Gestiones <i class="bi bi-arrow-left"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">Cerrar sesión <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-mortarboard"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title">Gestión de Inducción</h1>
            <p class="section-description intro-description-centered">
                Cursos, banco de preguntas, materiales y asignaciones. Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <?php if ($isGlobalAdmin): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3">Empresa</h2>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="companySelect" class="form-label">Empresa</label>
                        <select id="companySelect" class="form-select">
                            <option value="">Selecciona una empresa...</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div id="courseActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <?php if ($puedeGestionar): ?>
            <div class="feature-card mb-4">
                <h2 class="h5 mb-3" id="courseFormTitle">Nuevo curso de inducción</h2>
                <form id="courseForm" novalidate>
                    <input type="hidden" id="courseFormMode" value="create">
                    <input type="hidden" id="courseFormTarget" value="">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="courseName" class="form-label">Nombre</label>
                            <input type="text" id="courseName" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label for="courseDescription" class="form-label">Descripción</label>
                            <input type="text" id="courseDescription" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseAttempts" class="form-label">Intentos permitidos</label>
                            <input type="number" id="courseAttempts" class="form-control" min="1" max="20" value="3" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseApproval" class="form-label">% para aprobar</label>
                            <input type="number" id="courseApproval" class="form-control" min="1" max="100" value="70" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseFrom" class="form-label">Vigente desde</label>
                            <input type="date" id="courseFrom" class="form-control" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="courseUntil" class="form-label">Vigente hasta</label>
                            <input type="date" id="courseUntil" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-actions mt-3">
                        <button type="submit" id="courseSubmitBtn" class="btn btn-primary-custom btn-sm">Guardar curso</button>
                        <button type="button" id="courseCancelEditBtn" class="btn btn-outline-custom btn-sm d-none">Cancelar edición</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3">Cursos</h2>
                <div id="coursesStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    <?php echo $isGlobalAdmin ? 'Selecciona una empresa para ver sus cursos.' : 'Cargando cursos...'; ?>
                </div>
                <div id="coursesTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th><th>% Aprobación</th><th>Intentos</th><th>Preguntas</th><th>Estado</th><th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="coursesTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Detalle de curso: preguntas, materiales, asignaciones -->
            <div id="courseDetail" class="feature-card mt-4 curso-detalle">
                <div class="d-flex justify-content-between align-items-start">
                    <h2 class="h5 mb-0">Detalle: <span id="courseDetailName">-</span></h2>
                    <button type="button" id="courseDetailCloseBtn" class="btn btn-outline-custom btn-sm">Cerrar</button>
                </div>

                <div id="detailAlert" class="alert d-none mt-3" role="alert" aria-live="polite"></div>

                <!-- Preguntas -->
                <div class="subseccion">
                    <h3 class="h6">Preguntas del curso <span id="courseDetailScore" class="text-muted"></span></h3>
                    <div id="courseQuestionsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <div class="input-group mb-2">
                        <input type="text" id="questionSearchInput" class="form-control" placeholder="Buscar en el banco de preguntas...">
                        <button type="button" id="questionSearchBtn" class="btn btn-outline-custom">Buscar</button>
                    </div>
                    <div id="questionSearchResults" class="mb-3"></div>

                    <?php if ($puedeGestionarBanco): ?>
                    <details class="mb-2">
                        <summary class="mb-2" style="cursor:pointer;">+ Crear pregunta nueva en el banco</summary>
                        <form id="newQuestionForm" class="mt-2">
                            <div class="mb-2">
                                <label class="form-label">Enunciado</label>
                                <textarea id="newQuestionText" class="form-control" rows="2" required></textarea>
                            </div>
                            <div id="newQuestionOptions">
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-8"><input type="text" class="form-control option-text" placeholder="Alternativa 1"></div>
                                    <div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="0" checked> <label class="form-check-label">Correcta</label></div>
                                </div>
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-8"><input type="text" class="form-control option-text" placeholder="Alternativa 2"></div>
                                    <div class="col-4 form-check mt-2"><input type="radio" name="correctOption" class="form-check-input option-correct" value="1"> <label class="form-check-label">Correcta</label></div>
                                </div>
                            </div>
                            <button type="button" id="addOptionRowBtn" class="btn btn-outline-custom btn-sm mb-2">+ Alternativa</button>
                            <div class="row g-2">
                                <div class="col-6"><label class="form-label">Dificultad (1-5)</label><input type="number" id="newQuestionDifficulty" class="form-control" min="1" max="5" value="1"></div>
                                <div class="col-6"><label class="form-label">Puntaje</label><input type="number" id="newQuestionPoints" class="form-control" min="1" value="10"></div>
                            </div>
                            <button type="submit" class="btn btn-primary-custom btn-sm mt-2">Crear pregunta</button>
                        </form>
                    </details>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Materiales -->
                <div class="subseccion">
                    <h3 class="h6">Materiales de apoyo</h3>
                    <div id="courseMaterialsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="materialForm" class="row g-2">
                        <div class="col-12 col-md-4"><input type="text" id="materialTitle" class="form-control" placeholder="Título" required></div>
                        <div class="col-6 col-md-3">
                            <select id="materialType" class="form-select">
                                <option value="texto">Texto</option>
                                <option value="documento">Documento (enlace)</option>
                                <option value="video">Video (enlace)</option>
                                <option value="otro">Otro (enlace)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5"><input type="text" id="materialContent" class="form-control" placeholder="Contenido de texto o enlace https://..."></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-custom btn-sm">Agregar material</button></div>
                    </form>
                    <?php endif; ?>
                </div>

                <!-- Asignaciones -->
                <div class="subseccion">
                    <h3 class="h6">Asignaciones</h3>
                    <div id="courseAssignmentsList" class="mb-3"></div>

                    <?php if ($puedeGestionar): ?>
                    <form id="assignForm" class="row g-2">
                        <div class="col-12 col-md-6">
                            <select id="assignUserSelect" class="form-select" required>
                                <option value="">Selecciona un usuario...</option>
                            </select>
                        </div>
                        <div class="col-8 col-md-4"><input type="date" id="assignDeadline" class="form-control" required></div>
                        <div class="col-4 col-md-2"><button type="submit" class="btn btn-outline-custom btn-sm w-100">Asignar</button></div>
                    </form>
                    <?php endif; ?>
                </div>

            </div>

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/induccion-admin.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
