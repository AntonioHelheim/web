<?php

/**
 * Canonical option catalogues used by onboarding/profile completion.
 *
 * Keep codes stable in database; translate labels in lang/*.php.
 */
final class SctProfileOptions
{

    public const EMERGENCY_RELATIONS = [
        'partner',
        'direct_family',
        'friend',
        'other',
    ];

    public const CONTRACTOR_FLAGS = [
        'no',
        'yes',
    ];


    public static function isEmergencyRelation(string $value): bool
    {
        return in_array($value, self::EMERGENCY_RELATIONS, true);
    }

    public static function isContractorFlag(string $value): bool
    {
        return in_array($value, self::CONTRACTOR_FLAGS, true);
    }
}
