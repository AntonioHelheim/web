<?php
/** Safety Control Tower E3-VS1 — authentication endpoint. */
const MAX_SOLICITUDES_CODIGO_IP   = 10;
const MAX_SOLICITUDES_CODIGO_USR  = 5;
const VENTANA_SOLICITUD_MINUTOS   = 30;
const MAX_INTENTOS_AUTENTICACION_USR = 10;
const MAX_INTENTOS_AUTENTICACION_IP  = 500; // resguardo alto para redes compartidas
const MAX_INTENTOS_CODIGO             = 10;
const VENTANA_INTENTOS_MINUTOS    = 10;

require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/response.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/passwords.php';
require __DIR__ . '/app/Auth/AuthConfig.php';
require __DIR__ . '/app/Auth/RolePolicy.php';
require __DIR__ . '/app/Auth/SessionService.php';
require __DIR__ . '/app/Auth/UserContext.php';
require __DIR__ . '/app/Auth/AuthorizationService.php';
require __DIR__ . '/app/Auth/CredentialRepository.php';
require __DIR__ . '/app/Auth/LoginAttemptRepository.php';
require __DIR__ . '/app/Auth/OtpService.php';
require __DIR__ . '/app/Auth/OtpDeliveryService.php';
require __DIR__ . '/app/Auth/AuthenticationService.php';
require __DIR__ . '/app/Auth/LoginController.php';
require __DIR__ . '/app/Onboarding/OnboardingRepository.php';
require __DIR__ . '/app/Onboarding/OnboardingService.php';
require __DIR__ . '/app/Security/LoginThrottle.php';

$credentialRepo = new SctCredentialRepository($pdo);
$attemptRepo = new SctLoginAttemptRepository($pdo);
$authorization = new SctAuthorizationService($pdo);
$otp = new SctOtpService($pdo);
$authentication = new SctAuthenticationService($credentialRepo, $attemptRepo, $authorization);
$controller = new SctLoginController(
    $credentialRepo,
    $attemptRepo,
    $otp,
    new SctOtpDeliveryService(),
    $authentication,
    new SctLoginThrottle($attemptRepo),
    isset($isLocal) ? (bool) $isLocal : ((getenv('APP_ENV') ?: 'local') === 'local'),
    new SctOnboardingService($pdo,$authorization)
);
$controller->handle();
