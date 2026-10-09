<?php
final class SctOtpService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $userId,string $ip,int $length,int $ttlMinutes): string
    {
        $code=str_pad((string)random_int(0,(10**$length)-1),$length,'0',STR_PAD_LEFT);
        $hash=password_hash($code,PASSWORD_DEFAULT);
        $this->pdo->beginTransaction();
        try {
            $stmt=$this->pdo->prepare('UPDATE login_codes SET used_at=NOW() WHERE id_users=:id_users AND used_at IS NULL');
            $stmt->execute(['id_users'=>$userId]);
            $stmt=$this->pdo->prepare(
                'INSERT INTO login_codes(id_users,code_hash,expires_at,attempts,ip_address,created_at)
                 VALUES(:id_users,:code_hash,DATE_ADD(NOW(),INTERVAL :ttl MINUTE),0,:ip,NOW())'
            );
            $stmt->execute(['id_users'=>$userId,'code_hash'=>$hash,'ttl'=>$ttlMinutes,'ip'=>$ip]);
            $this->pdo->commit();
            return $code;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function requestCountByIp(string $ip,int $minutes): int
    {
        $stmt=$this->pdo->prepare('SELECT COUNT(*) FROM login_codes WHERE ip_address=:ip AND created_at >= (NOW() - INTERVAL :minutes MINUTE)');
        $stmt->execute(['ip'=>$ip,'minutes'=>$minutes]);
        return (int)$stmt->fetchColumn();
    }

    public function requestCountByUser(string $userId,int $minutes): int
    {
        $stmt=$this->pdo->prepare('SELECT COUNT(*) FROM login_codes WHERE id_users=:id_users AND created_at >= (NOW() - INTERVAL :minutes MINUTE)');
        $stmt->execute(['id_users'=>$userId,'minutes'=>$minutes]);
        return (int)$stmt->fetchColumn();
    }

    public function verifyAndConsume(string $userId,string $code,int $maxAttempts): array
    {
        // Atomic consumption: row is locked until verification + used_at update finish.
        $this->pdo->beginTransaction();
        try {
            $stmt=$this->pdo->prepare(
                'SELECT id_login_code,code_hash,attempts FROM login_codes
                 WHERE id_users=:id_users AND used_at IS NULL AND expires_at>=NOW() AND attempts<:max_attempts
                 ORDER BY created_at DESC LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['id_users'=>$userId,'max_attempts'=>$maxAttempts]);
            $row=$stmt->fetch();
            if (!$row || !password_verify($code,(string)$row['code_hash'])) {
                if ($row) {
                    $up=$this->pdo->prepare('UPDATE login_codes SET attempts=attempts+1 WHERE id_login_code=:id');
                    $up->execute(['id'=>$row['id_login_code']]);
                    $attempts=(int)$row['attempts']+1;
                } else {
                    $attempts=$maxAttempts;
                }
                $this->pdo->commit();
                return ['ok'=>false,'attempts'=>$attempts];
            }
            $up=$this->pdo->prepare('UPDATE login_codes SET used_at=NOW() WHERE id_login_code=:id AND used_at IS NULL');
            $up->execute(['id'=>$row['id_login_code']]);
            if ($up->rowCount()!==1) {
                $this->pdo->rollBack();
                return ['ok'=>false,'attempts'=>(int)$row['attempts']];
            }
            $this->pdo->commit();
            return ['ok'=>true,'attempts'=>(int)$row['attempts']];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
