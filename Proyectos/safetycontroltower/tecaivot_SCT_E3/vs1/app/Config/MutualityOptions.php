<?php

/**
 * Mutualidades / administradores del seguro laboral disponibles en SCT.
 *
 * Código canónico usado en BD y aplicación:
 * - achs
 * - isl
 * - ist
 * - mutual
 */
final class SctMutualityOptions
{
    public const OPTIONS = [
        'achs' => [
            'sigla' => 'ACHS',
            'name' => 'Asociación Chilena de Seguridad',
            'logo' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/0/09/Logo_ACHS.svg/250px-Logo_ACHS.svg.png',
        ],
        'isl' => [
            'sigla' => 'ISL',
            'name' => 'Instituto de Seguridad Laboral',
            'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_del_Instituto_de_Seguridad_Laboral_%28Chile%29.png',
        ],
        'ist' => [
            'sigla' => 'IST',
            'name' => 'Instituto de Seguridad del Trabajo',
            'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/27/Logo_instituto_de_seguridad_del_trabajo_IST.jpg',
        ],
        'mutual' => [
            'sigla' => 'MUTUAL',
            'name' => 'Mutual de Seguridad',
            'logo' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/4e/Mutual_of_Security.svg/330px-Mutual_of_Security.svg.png',
        ],
    ];

    public static function codes(): array
    {
        return array_keys(self::OPTIONS);
    }

    public static function isValid(string $code): bool
    {
        return isset(self::OPTIONS[$code]);
    }

    public static function get(string $code): ?array
    {
        return self::OPTIONS[$code] ?? null;
    }
}
