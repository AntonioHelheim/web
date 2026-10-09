<?php
final class SctLoginThrottle
{
    private SctLoginAttemptRepository $attempts;

    public function __construct(SctLoginAttemptRepository $attempts)
    {
        $this->attempts = $attempts;
    }

    private function remainingSeconds(array $rows,int $limit,int $windowMinutes,int $blockMinutes): int
    {
        if(count($rows)<$limit)return 0;$times=[];$now=time();
        foreach($rows as $row){$ts=(int)($row['failed_at']??0);if($ts>0)$times[]=$ts;if(!empty($row['now_at']))$now=(int)$row['now_at'];}
        if(count($times)<$limit)return 0;$newest=max($times);$oldest=min($times);
        if(($newest-$oldest)>$windowMinutes*60)return 0;
        return max(0,$newest+$blockMinutes*60-$now);
    }

    public function userBlockSeconds(string $email,int $limit,int $windowMinutes,int $blockMinutes): int
    { return $this->remainingSeconds($this->attempts->recentFailuresForUser($email,$limit),$limit,$windowMinutes,$blockMinutes); }
    public function ipBlockSeconds(string $ip,int $limit,int $windowMinutes,int $blockMinutes): int
    { return $this->remainingSeconds($this->attempts->recentFailuresForIp($ip,$limit),$limit,$windowMinutes,$blockMinutes); }
}
