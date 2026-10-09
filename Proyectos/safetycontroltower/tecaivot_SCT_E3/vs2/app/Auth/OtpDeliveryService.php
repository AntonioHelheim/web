<?php
final class SctOtpDeliveryService
{
    /** Demo identities are explicit and may show OTP on web for QA only. */
    public const DEMO_ACCOUNTS = [
        'superusuariodemo@demosct.cl',
        'gerenteempresademo@demosct.cl',
        'adminempresademo@demosct.cl',
        'jefaturaempresademo@demosct.cl',
        'usuarioempresademo@demosct.cl',
        'usuarionuevoempresademo@demosct.cl',
        'paramedicoempresademo@demosct.cl',
        'contratistaempresademo@demosct.cl',
    ];

    public static function isDemo(string $email): bool
    { return in_array(strtolower(trim($email)),self::DEMO_ACCOUNTS,true); }

    public static function mayDisplay(bool $isLocal,string $email): bool
    { return $isLocal || self::isDemo($email); }

    public function send(string $email,string $code,int $ttlMinutes): bool
    {
        $subject='Tu código de acceso — Safety Control Tower';
        $body="Tu código de verificación es: {$code}\n\nEste código vence en {$ttlMinutes} minutos.\nLa contraseña ya fue validada previamente.\nSi no intentaste iniciar sesión, ignora este mensaje.";
        $headers="From: Safety Control Tower <no-responder@safetycontroltower.cl>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        return @mail($email,$subject,$body,$headers);
    }
}
