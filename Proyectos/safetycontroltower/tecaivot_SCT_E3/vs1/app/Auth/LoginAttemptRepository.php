<?php
final class SctLoginAttemptRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function record(string $identifier,string $ip,bool $success): void
    {
        $stmt=$this->pdo->prepare('INSERT INTO login_attempts(identifier,ip_address,success) VALUES(:identifier,:ip,:success)');
        $stmt->execute(['identifier'=>$identifier,'ip'=>$ip,'success'=>$success?1:0]);
    }

    public function recentFailuresForUser(string $identifier,int $limit): array
    {
        $limit=max(1,$limit);
        $stmt=$this->pdo->prepare(
            'SELECT UNIX_TIMESTAMP(la.created_at) failed_at,UNIX_TIMESTAMP(NOW()) now_at
             FROM login_attempts la
             WHERE la.success=0 AND la.identifier=:identifier
               AND la.created_at>=COALESCE((SELECT uc.password_changed_at FROM user_credentials uc WHERE uc.id_users=:credential_user LIMIT 1),\'1970-01-01 00:00:00\')
             ORDER BY la.created_at DESC LIMIT '.$limit
        );
        $stmt->execute(['identifier'=>$identifier,'credential_user'=>$identifier]);
        return $stmt->fetchAll();
    }

    public function recentFailuresForIp(string $ip,int $limit): array
    {
        $limit=max(1,$limit);
        $stmt=$this->pdo->prepare(
            'SELECT UNIX_TIMESTAMP(created_at) failed_at,UNIX_TIMESTAMP(NOW()) now_at
             FROM login_attempts WHERE success=0 AND ip_address=:ip ORDER BY created_at DESC LIMIT '.$limit
        );
        $stmt->execute(['ip'=>$ip]);
        return $stmt->fetchAll();
    }
}
