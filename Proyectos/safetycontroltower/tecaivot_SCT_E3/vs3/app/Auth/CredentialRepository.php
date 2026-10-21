<?php
final class SctCredentialRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findActiveCredential(string $email): ?array
    {
        $stmt=$this->pdo->prepare(
            'SELECT u.id_users,u.state,uc.password_hash,uc.credential_status
             FROM users u LEFT JOIN user_credentials uc ON uc.id_users=u.id_users
             WHERE u.id_users=:email AND u.state=1 LIMIT 1'
        );
        $stmt->execute(['email'=>$email]);
        $row=$stmt->fetch();
        return $row ?: null;
    }

    public function rehash(string $userId,string $hash): void
    {
        $stmt=$this->pdo->prepare(
            'UPDATE user_credentials SET password_hash=:hash,password_changed_at=NOW(),updated_at=NOW() WHERE id_users=:id_users'
        );
        $stmt->execute(['hash'=>$hash,'id_users'=>$userId]);
    }

    public function touchLastAccess(string $userId): void
    {
        $stmt=$this->pdo->prepare('UPDATE users SET last_access=NOW() WHERE id_users=:id_users');
        $stmt->execute(['id_users'=>$userId]);
    }
}
