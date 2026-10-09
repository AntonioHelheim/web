<?php
/** HTTP controller for the password + OTP authentication flow. */
final class SctLoginController
{
    private SctCredentialRepository $credentials;
    private SctLoginAttemptRepository $attempts;
    private SctOtpService $otp;
    private SctOtpDeliveryService $delivery;
    private SctAuthenticationService $authentication;
    private SctLoginThrottle $throttle;
    private bool $localMode;
    private ?SctOnboardingService $onboarding;

    public function __construct(
        SctCredentialRepository $credentials,
        SctLoginAttemptRepository $attempts,
        SctOtpService $otp,
        SctOtpDeliveryService $delivery,
        SctAuthenticationService $authentication,
        SctLoginThrottle $throttle,
        bool $localMode,
        ?SctOnboardingService $onboarding = null
    ) {
        $this->credentials = $credentials;
        $this->attempts = $attempts;
        $this->otp = $otp;
        $this->delivery = $delivery;
        $this->authentication = $authentication;
        $this->throttle = $throttle;
        $this->localMode = $localMode;
        $this->onboarding = $onboarding;
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            responderJSON(false, null, 'Método no permitido.', 405);
        }

        $input = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $action = trim((string) ($input['action'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');
        $code = trim((string) ($input['code'] ?? ''));
        $csrf = (string) ($input['csrf_token'] ?? '');
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $this->assertCsrf($csrf);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responderJSON(false, null, 'Correo electrónico no válido.', 400);
        }

        try {
            switch ($action) {
                case 'request_code':
                    $this->requestCode($email, $password, $ip);
                    return;
                case 'resend_code':
                    $this->resendCode($email, $ip);
                    return;
                case 'verify_code':
                    $this->verifyCode($email, $code, $ip);
                    return;
                default:
                    responderJSON(false, null, 'Acción no reconocida.', 400);
                    return;
            }
        } catch (PDOException $e) {
            error_log('SctLoginController E3-VS1: ' . $e->getMessage());
            responderJSON(false, null, 'Error al procesar la solicitud.', 500);
        } catch (Throwable $e) {
            error_log('SctLoginController E3-VS1: ' . $e->getMessage());
            responderJSON(false, null, 'Error al procesar la solicitud.', 500);
        }
    }

    private function requestCode(string $email, string $password, string $ip): void
    {
        $this->assertNotBlocked($email, $ip);

        if ($password === '' || strlen($password) > 255) {
            $this->attempts->record($email, $ip, false);
            SctSessionService::clearSecondFactor();
            $this->assertNotBlocked($email, $ip);
            responderJSON(false, null, 'Correo o contraseña incorrectos. Si tu cuenta aún no tiene una contraseña nueva, usa “¿Olvidaste tu contraseña?”.', 401);
        }

        $result = $this->authentication->verifyPassword($email, $password, $ip, PASSWORD_CREDENTIAL_ACTIVE);
        if (empty($result['ok'])) {
            SctSessionService::clearSecondFactor();
            $this->assertNotBlocked($email, $ip);
            responderJSON(false, null, 'Correo o contraseña incorrectos. Si tu cuenta aún no tiene una contraseña nueva, usa “¿Olvidaste tu contraseña?”.', 401);
        }

        $userId = (string) $result['user_id'];
        $this->assertOtpRate($userId, $ip);
        $otp = $this->otp->create($userId, $ip, SctAuthConfig::OTP_LENGTH, SctAuthConfig::OTP_TTL_MINUTES);

        if (!SctOtpDeliveryService::mayDisplay($this->localMode, $email)
            && !$this->delivery->send($email, $otp, SctAuthConfig::OTP_TTL_MINUTES)) {
            SctSessionService::clearSecondFactor();
            responderJSON(false, null, 'No fue posible enviar el código. Intenta nuevamente.', 500);
        }

        SctSessionService::markSecondFactorPending($userId);
        $this->respondOtpDelivery($email, $otp, false);
    }

    private function resendCode(string $email, string $ip): void
    {
        if (!SctSessionService::secondFactorPendingFor($email, SctAuthConfig::SECOND_FACTOR_PENDING_MINUTES)) {
            SctSessionService::clearSecondFactor();
            responderJSON(false, null, 'La validación de contraseña expiró. Ingresa nuevamente tus credenciales.', 403);
        }

        $row = $this->credentials->findActiveCredential($email);
        if (!$row) {
            SctSessionService::clearSecondFactor();
            responderJSON(false, null, 'No fue posible continuar la autenticación.', 403);
        }

        $userId = (string) $row['id_users'];
        $this->assertOtpRate($userId, $ip);
        $otp = $this->otp->create($userId, $ip, SctAuthConfig::OTP_LENGTH, SctAuthConfig::OTP_TTL_MINUTES);

        if (!SctOtpDeliveryService::mayDisplay($this->localMode, $email)
            && !$this->delivery->send($email, $otp, SctAuthConfig::OTP_TTL_MINUTES)) {
            responderJSON(false, null, 'No fue posible reenviar el código. Intenta nuevamente.', 500);
        }

        SctSessionService::markSecondFactorPending($userId);
        $this->respondOtpDelivery($email, $otp, true);
    }

    private function verifyCode(string $email, string $code, string $ip): void
    {
        if (!SctSessionService::secondFactorPendingFor($email, SctAuthConfig::SECOND_FACTOR_PENDING_MINUTES)) {
            SctSessionService::clearSecondFactor();
            responderJSON(false, null, 'La validación previa expiró. Ingresa nuevamente tu correo y contraseña.', 403);
        }

        if (!preg_match('/^\\d{' . SctAuthConfig::OTP_LENGTH . '}$/', $code)) {
            responderJSON(false, null, 'Código inválido.', 400);
        }

        $row = $this->credentials->findActiveCredential($email);
        if (!$row) {
            SctSessionService::clearSecondFactor();
            responderJSON(false, null, 'Código inválido o expirado.', 401);
        }

        $verified = $this->otp->verifyAndConsume((string) $row['id_users'], $code, SctAuthConfig::MAX_OTP_ATTEMPTS);
        if (empty($verified['ok'])) {
            if ((int) ($verified['attempts'] ?? 0) >= SctAuthConfig::MAX_OTP_ATTEMPTS) {
                SctSessionService::clearSecondFactor();
                responderJSON(false, null, 'Demasiados intentos fallidos. Vuelve a iniciar el acceso en unos minutos.', 429);
            }
            responderJSON(false, null, 'Código inválido o expirado.', 401);
        }

        if (!$this->authentication->completeLogin((string) $row['id_users'], $ip)) {
            SctSessionService::clearSecondFactor();
            responderJSON(false, null, 'La cuenta no tiene un perfil de acceso SCT activo. Contacta a un administrador.', 403);
        }

        $redirect=$this->onboarding?$this->onboarding->nextRedirect((string)$row['id_users']):'bienvenida.php';
        responderJSON(true,['redirect'=>$redirect],'Inicio de sesión exitoso.');
    }

    private function assertCsrf(string $token): void
    {
        if (empty($_SESSION['csrf_token']) || $token === '' || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
            responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
        }
    }

    private function assertNotBlocked(string $email, string $ip): void
    {
        $userSeconds = $this->throttle->userBlockSeconds(
            $email,
            SctAuthConfig::MAX_FAILED_PASSWORDS_BY_USER,
            SctAuthConfig::LOGIN_ATTEMPT_WINDOW_MINUTES,
            SctAuthConfig::LOGIN_BLOCK_MINUTES
        );
        if ($userSeconds > 0) {
            responderJSON(false, ['retry_after_seconds' => $userSeconds], 'Se alcanzó el límite de intentos fallidos. Intenta nuevamente más tarde.', 429);
        }

        $ipSeconds = $this->throttle->ipBlockSeconds(
            $ip,
            SctAuthConfig::MAX_FAILED_PASSWORDS_BY_IP,
            SctAuthConfig::LOGIN_ATTEMPT_WINDOW_MINUTES,
            SctAuthConfig::LOGIN_BLOCK_MINUTES
        );
        if ($ipSeconds > 0) {
            responderJSON(false, ['retry_after_seconds' => $ipSeconds], 'Se detectaron demasiados accesos fallidos desde esta conexión. Intenta nuevamente más tarde.', 429);
        }
    }

    private function assertOtpRate(string $userId, string $ip): void
    {
        if ($this->otp->requestCountByIp($ip, SctAuthConfig::OTP_REQUEST_WINDOW_MINUTES) >= SctAuthConfig::MAX_OTP_REQUESTS_BY_IP) {
            responderJSON(false, null, 'Demasiadas solicitudes desde esta conexión. Intenta nuevamente en unos minutos.', 429);
        }
        if ($this->otp->requestCountByUser($userId, SctAuthConfig::OTP_REQUEST_WINDOW_MINUTES) >= SctAuthConfig::MAX_OTP_REQUESTS_BY_USER) {
            responderJSON(false, null, 'Se alcanzó el límite de códigos solicitados. Intenta nuevamente en unos minutos.', 429);
        }
    }

    private function respondOtpDelivery(string $email, string $otp, bool $resent): void
    {
        $demo = SctOtpDeliveryService::isDemo($email);
        if (SctOtpDeliveryService::mayDisplay($this->localMode, $email)) {
            responderJSON(
                true,
                ['display_code' => $otp, 'display_mode' => $demo ? 'demo' : 'local'],
                $demo
                    ? 'Cuenta DEMO validada. Usa el código mostrado para completar el segundo factor.'
                    : 'Modo desarrollo local: usa el código mostrado para completar el segundo factor.'
            );
        }

        responderJSON(true, null, $resent
            ? 'Código reenviado. Revisa tu correo.'
            : 'Contraseña validada. Enviamos un código de verificación a tu correo.');
    }
}
