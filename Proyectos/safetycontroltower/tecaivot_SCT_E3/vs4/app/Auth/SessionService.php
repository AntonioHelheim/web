<?php
final class SctSessionService
{
    public static function currentUserId(): ?string
    {
        $id = $_SESSION['user_id'] ?? $_SESSION['user_email'] ?? null;
        return is_string($id) && trim($id) !== '' ? trim($id) : null;
    }

    public static function markSecondFactorPending(string $userId): void
    {
        $_SESSION['2fa_pending_user'] = strtolower($userId);
        $_SESSION['2fa_password_verified_at'] = time();
    }

    public static function clearSecondFactor(): void
    {
        unset($_SESSION['2fa_pending_user'], $_SESSION['2fa_password_verified_at']);
    }

    public static function secondFactorPendingFor(string $email, int $ttlMinutes): bool
    {
        $pending = (string)($_SESSION['2fa_pending_user'] ?? '');
        $verifiedAt = (int)($_SESSION['2fa_password_verified_at'] ?? 0);
        return $pending !== '' && $verifiedAt > 0
            && hash_equals($pending, strtolower($email))
            && (time() - $verifiedAt) <= ($ttlMinutes * 60);
    }

    public static function establishAuthenticatedSession(string $userId): void
    {
        session_regenerate_id(true);
        self::clearSecondFactor();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = strtolower($userId);
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
