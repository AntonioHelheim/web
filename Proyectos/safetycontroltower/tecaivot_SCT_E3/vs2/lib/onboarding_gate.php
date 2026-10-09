<?php
require_once __DIR__.'/../app/Onboarding/OnboardingRepository.php';
require_once __DIR__.'/../app/Onboarding/OnboardingService.php';
function sctOnboarding(PDO $pdo): SctOnboardingService{static $i=[];$k=spl_object_id($pdo);return $i[$k]??=new SctOnboardingService($pdo,sctAuthorization($pdo));}
function onboardingGateIsExemptRequest(): bool{$p=str_replace('\\','/',(string)($_SERVER['SCRIPT_NAME']??$_SERVER['PHP_SELF']??''));return (bool)(preg_match('~/api/onboarding/[^/]+\.php$~',$p)||preg_match('~/(?:onboarding|login|logout|password-request|password-update|recuperar-password|restablecer-password)\.php$~',$p));}
function onboardingGateBlocked(PDO $pdo): bool{$id=currentUserId();if(!$id)return false;$s=sctOnboarding($pdo);return $s->schemaAvailable()&&empty($s->status($id)['complete']);}
function onboardingGateUrl(PDO $pdo): string{$id=currentUserId();$t=$id?sctOnboarding($pdo)->nextRedirect($id):'onboarding.php';$script=str_replace('\\','/',(string)($_SERVER['SCRIPT_NAME']??''));$pos=strpos($script,'/api/');if($pos!==false)return substr($script,0,$pos).'/'.ltrim($t,'/');$d=rtrim(dirname($script),'/');return ($d===''?'':$d).'/'.ltrim($t,'/');}
function onboardingGateMessage(): string
{
    return function_exists('t')
        ? t('onboarding_gate_required')
        : 'Debes completar el registro inicial y la evaluación de seguridad antes de acceder al sistema.';
}
