<?php
/** Password validation and authenticated-session finalization. */
final class SctAuthenticationService
{
    /** Valid bcrypt hash used to reduce account-enumeration timing differences. */
    public const DUMMY_PASSWORD_HASH = '$2y$12$eXl0YUpTJtHejei9lEo61uqvTt5ToI/ZgYlIdbTXu7IuhiRLfMuLy';

    private SctCredentialRepository $credentials;
    private SctLoginAttemptRepository $attempts;
    private SctAuthorizationService $authorization;

    public function __construct(
        SctCredentialRepository $credentials,
        SctLoginAttemptRepository $attempts,
        SctAuthorizationService $authorization
    ) {
        $this->credentials = $credentials;
        $this->attempts = $attempts;
        $this->authorization = $authorization;
    }

    public function verifyPassword(string $email, string $password, string $ip, string $activeStatus): array
    {
        $row = $this->credentials->findActiveCredential($email);
        $stored = ($row && !empty($row['password_hash']))
            ? (string) $row['password_hash']
            : self::DUMMY_PASSWORD_HASH;

        $valid = password_verify($password, $stored);
        if (!$row || ($row['credential_status'] ?? '') !== $activeStatus || empty($row['password_hash']) || !$valid) {
            $this->attempts->record($email, $ip, false);
            return ['ok' => false];
        }

        if (password_needs_rehash((string) $row['password_hash'], PASSWORD_DEFAULT)) {
            $this->credentials->rehash((string) $row['id_users'], password_hash($password, PASSWORD_DEFAULT));
        }

        return ['ok' => true, 'user_id' => (string) $row['id_users']];
    }

    public function completeLogin(string $email, string $ip): bool
    {
        // E3-VS1: authentication is not authorization. An identity without a
        // recognized active SCT profile cannot receive an operational session.
        if (!$this->authorization->userHasActiveRecognizedRole($email)) {
            return false;
        }

        $this->credentials->touchLastAccess($email);
        $this->attempts->record($email, $ip, true);
        SctSessionService::establishAuthenticatedSession($email);
        return true;
    }
}
