<?php
require_once __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/php/usuarios/common.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: accesso-denegado.php');
    exit;
}

$sessionUserId = (string) ($_SESSION['user_id'] ?? $_SESSION['user_email'] ?? '');

if ($sessionUserId === '') {
    header('Location: accesso-denegado.php');
    exit;
}

try {
    $accessContext = usuariosRequireAccessContext($pdo);
} catch (PDOException $e) {
    error_log('gestion-usuarios.php: ' . $e->getMessage());
    header('Location: accesso-denegado.php');
    exit;
}

aplicarCabecerasSeguridad();

$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$sessionUserIdEscaped = htmlspecialchars($sessionUserId, ENT_QUOTES, 'UTF-8');
$actorLevelEscaped = htmlspecialchars((string) ($accessContext['actor_level'] ?? ''), ENT_QUOTES, 'UTF-8');
$actorCompanyEscaped = htmlspecialchars((string) ($accessContext['company_id'] ?? ''), ENT_QUOTES, 'UTF-8');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .welcome-hero {
            padding: 4rem 0 2rem;
        }
        .welcome-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img {
            height: 32px;
        }
        .topbar-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .welcome-greeting-icon {
            font-size: 2.5rem;
            color: #16a34a;
            margin-bottom: 0.75rem;
        }
        .quick-links {
            margin-top: 2rem;
            margin-bottom: 3rem;
        }
        .users-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .users-toolbar-title {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .users-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .users-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem 1rem;
        }
        .users-detail-grid dt {
            font-size: 0.82rem;
            color: var(--text-secondary);
            margin-bottom: 0.15rem;
        }
        .users-detail-grid dd {
            margin: 0;
            font-weight: 600;
        }
        @media (max-width: 767.98px) {
            .users-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="container" data-current-user="<?php echo $sessionUserIdEscaped; ?>" data-csrf-token="<?php echo $csrfTokenEscaped; ?>" data-actor-level="<?php echo $actorLevelEscaped; ?>" data-actor-company="<?php echo $actorCompanyEscaped; ?>">

        <div class="welcome-topbar">

            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="./images/logos/Logo-SCT-white.svg" alt="Safety Control Tower">
                </div>
            </div>

            <div class="topbar-actions">
                <a href="gestiones.php" class="btn btn-outline-custom btn-sm">
                    Volver a Gestiones
                    <i class="bi bi-arrow-left"></i>
                </a>

                <a href="logout.php" class="btn btn-outline-custom btn-sm">
                    Cerrar sesión
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>

        </div>

        <section class="welcome-hero text-center">

            <div class="welcome-greeting-icon">
                <i class="bi bi-people"></i>
            </div>

            <span class="section-label">SAFETY CONTROL TOWER</span>

            <h1 class="section-title">Gestión de Usuarios</h1>

            <p class="section-description intro-description-centered">
                Gestiona usuarios según tu nivel de acceso, su estado, el rol asignado y su último acceso.
                Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>

        </section>

        <section class="quick-links">
            <div class="feature-card">
                <div class="users-toolbar mb-3">
                    <p class="users-toolbar-title">Administración de cuentas de acceso por empresa.</p>
                    <button type="button" id="usersCreateBtn" class="btn btn-primary-custom btn-sm">
                        Nuevo usuario
                        <i class="bi bi-person-plus"></i>
                    </button>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label for="usersSearch" class="form-label">Buscar usuario</label>
                        <input type="search" id="usersSearch" class="form-control" placeholder="Buscar usuario..." autocomplete="off">
                    </div>

                    <div class="col-12 col-md-6 col-lg-2" id="usersCompanyFilterWrap">
                        <label for="usersCompanyFilter" class="form-label">Empresa</label>
                        <select id="usersCompanyFilter" class="form-select">
                            <option value="all">Todas</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="usersStateFilter" class="form-label">Estado</label>
                        <select id="usersStateFilter" class="form-select">
                            <option value="all">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="usersRoleFilter" class="form-label">Tipo de acceso</label>
                        <select id="usersRoleFilter" class="form-select">
                            <option value="all">Todos</option>
                        </select>
                    </div>
                </div>

                <div id="usersActionAlert" class="alert d-none mt-3 mb-0" role="alert" aria-live="polite"></div>

                <div id="usersStatus" class="alert alert-info mt-4 mb-0" role="status" aria-live="polite">
                    Cargando usuarios...
                </div>

                <div id="usersTableWrapper" class="table-responsive mt-4 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Usuario</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Tipo de acceso</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Último acceso</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>

    <div class="modal fade" id="userViewModal" tabindex="-1" aria-labelledby="userViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content login-modal">
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 id="userViewModalLabel" class="mb-0">Detalle de Usuario</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <dl class="users-detail-grid">
                        <div><dt>Usuario/email</dt><dd id="viewUserEmail">-</dd></div>
                        <div><dt>Nombre</dt><dd id="viewUserName">-</dd></div>
                        <div><dt>Apellido</dt><dd id="viewUserLastname">-</dd></div>
                        <div><dt>RUT</dt><dd id="viewUserRut">-</dd></div>
                        <div><dt>Empresa</dt><dd id="viewUserCompany">-</dd></div>
                        <div><dt>Rol(es)</dt><dd id="viewUserRoles">-</dd></div>
                        <div><dt>Nivel de acceso</dt><dd id="viewUserAccess">-</dd></div>
                        <div><dt>Estado</dt><dd id="viewUserState">-</dd></div>
                        <div><dt>Idioma</dt><dd id="viewUserLanguage">-</dd></div>
                        <div><dt>Último acceso</dt><dd id="viewUserLastAccess">-</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userFormModal" tabindex="-1" aria-labelledby="userFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content login-modal">
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 id="userFormModalLabel" class="mb-0">Nuevo usuario</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div id="userFormAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                    <form id="userForm" novalidate>
                        <input type="hidden" id="userFormMode" value="create">
                        <input type="hidden" id="userFormTarget" value="">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="userEmail" class="form-label">Usuario/email</label>
                                <input type="email" id="userEmail" class="form-control" maxlength="50" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="userRole" class="form-label">Rol</label>
                                <select id="userRole" class="form-select" required></select>
                            </div>

                            <div class="col-12 col-md-6" id="userCompanyGroup">
                                <label for="userCompany" class="form-label">Empresa</label>
                                <select id="userCompany" class="form-select" required></select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="userName" class="form-label">Nombre</label>
                                <input type="text" id="userName" class="form-control" maxlength="50" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="userLastname" class="form-label">Apellido</label>
                                <input type="text" id="userLastname" class="form-control" maxlength="50" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="userRut" class="form-label">RUT</label>
                                <input type="text" id="userRut" class="form-control" maxlength="10" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="userLanguage" class="form-label">Idioma</label>
                                <input type="text" id="userLanguage" class="form-control" maxlength="11" value="ESP" required>
                            </div>

                            <div class="col-12" id="userPasswordGroup">
                                <label for="userPassword" class="form-label">Contraseña inicial</label>
                                <input type="password" id="userPassword" class="form-control" minlength="8" maxlength="100" autocomplete="new-password">
                                <div class="form-text">Debe tener al menos 8 caracteres.</div>
                            </div>
                        </div>

                        <div class="users-actions mt-4">
                            <button type="submit" id="userFormSubmit" class="btn btn-primary-custom btn-sm">
                                Guardar
                            </button>
                            <button type="button" class="btn btn-outline-custom btn-sm" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userStateModal" tabindex="-1" aria-labelledby="userStateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content login-modal">
                <div class="modal-body">
                    <h2 id="userStateModalLabel" class="mb-3">Confirmar cambio de estado</h2>
                    <p id="userStateModalText" class="mb-4">¿Deseas cambiar el estado de este usuario?</p>

                    <div class="users-actions">
                        <button type="button" id="userStateConfirmBtn" class="btn btn-primary-custom btn-sm">
                            Confirmar
                        </button>
                        <button type="button" class="btn btn-outline-custom btn-sm" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/usuarios.js"></script>
</body>
</html>
