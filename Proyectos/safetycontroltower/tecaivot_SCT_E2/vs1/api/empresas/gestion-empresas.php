<?php
require_once __DIR__ . '/common.php';

empresasRequireGlobalAdminPage($pdo, '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empresas - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">

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
        .form-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>">

        <div class="welcome-topbar">

            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.svg" alt="Safety Control Tower">
                </div>
            </div>

            <div class="topbar-actions">
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm">
                    Volver a Gestiones
                    <i class="bi bi-arrow-left"></i>
                </a>

                <a href="../../logout.php" class="btn btn-outline-custom btn-sm">
                    Cerrar sesión
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>

        </div>

        <section class="welcome-hero text-center">

            <div class="welcome-greeting-icon">
                <i class="bi bi-building"></i>
            </div>

            <span class="section-label">SAFETY CONTROL TOWER</span>

            <h1 class="section-title">Gestión de Empresas</h1>

            <p class="section-description intro-description-centered">
                Administra empresas de la plataforma. Sesión iniciada como <strong><?php echo $userEmail; ?></strong>.
            </p>

        </section>

        <section class="quick-links">
            <div class="feature-card">
                <h2 class="h5 mb-3">Nueva empresa</h2>

                <div id="companiesActionAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <form id="companyForm" novalidate>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="companyRut" class="form-label">RUT</label>
                            <input type="text" id="companyRut" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="companyName" class="form-label">Razón social</label>
                            <input type="text" id="companyName" class="form-control" maxlength="150" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="companyAddress" class="form-label">Dirección</label>
                            <input type="text" id="companyAddress" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="companyEmail" class="form-label">Email</label>
                            <input type="email" id="companyEmail" class="form-control" maxlength="50" required>
                        </div>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" id="companySubmitBtn" class="btn btn-primary-custom btn-sm">Guardar empresa</button>
                    </div>
                </form>
            </div>

            <div class="feature-card mt-4">
                <h2 class="h5 mb-3">Empresas</h2>

                <div id="companiesStatus" class="alert alert-info mb-0" role="status" aria-live="polite">
                    Cargando empresas...
                </div>

                <div id="companiesTableWrapper" class="table-responsive mt-3 d-none">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">RUT</th>
                                <th scope="col">Razón social</th>
                                <th scope="col">Dirección</th>
                                <th scope="col">Email</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="companiesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/empresas.js"></script>
</body>
</html>
