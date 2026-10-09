<?php
/** Central authentication parameters for Safety Control Tower E3-VS1. */
final class SctAuthConfig
{
    public const OTP_LENGTH = 6;
    public const OTP_TTL_MINUTES = 10;
    public const SECOND_FACTOR_PENDING_MINUTES = 15;
    public const MAX_OTP_REQUESTS_BY_IP = 10;
    public const MAX_OTP_REQUESTS_BY_USER = 5;
    public const OTP_REQUEST_WINDOW_MINUTES = 30;
    public const MAX_FAILED_PASSWORDS_BY_USER = 10;
    public const MAX_FAILED_PASSWORDS_BY_IP = 500;
    public const MAX_OTP_ATTEMPTS = 10;
    public const LOGIN_ATTEMPT_WINDOW_MINUTES = 10;
    public const LOGIN_BLOCK_MINUTES = 15;
}
