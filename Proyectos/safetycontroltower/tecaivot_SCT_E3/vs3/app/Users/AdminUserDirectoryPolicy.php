<?php

/**
 * Safety Control Tower — Etapa 3 VS2
 *
 * Política específica del directorio Administrador Cliente.
 * El Administrador puede consultar/editar perfiles de su propia empresa
 * en niveles 2..7. Nivel 1 (SuperUsuario) queda excluido explícitamente.
 *
 * Alias históricos soportados:
 * - administrador_completo -> superusuario
 * - cliente -> gerente
 * - administrador -> administrador_cliente
 * - trabajador -> usuario
 */
final class SctAdminUserDirectoryPolicy
{
    public const LEVEL_SUPERUSER = 1;
    public const MANAGED_LEVELS = [2, 3, 4, 5, 6, 7];
    public const JEFATURA_MANAGED_LEVELS = [4, 5, 6, 7];

    public static function roleLevelSql(string $roleAlias='rg'): string
    {
        /*
         * E3 VS2:
         * Las empresas pueden conservar nombres históricos o descriptivos
         * para sus grupos ("Perfil Jefatura", "Administrador cliente", etc).
         * La clasificación no debe depender de igualdad literal.
         *
         * El orden es intencional: perfiles privilegiados se reconocen antes
         * que términos más genéricos.
         */
        $name="LOWER(TRIM({$roleAlias}.name))";

        return "CASE
            WHEN {$name} LIKE '%superusuario%'
              OR {$name} LIKE '%super user%'
              OR {$name} LIKE '%administrador_completo%'
              OR {$name} LIKE '%administrador completo%'
                THEN 1

            WHEN {$name} LIKE '%gerente%'
              OR {$name} LIKE '%gerencia%'
              OR {$name}='cliente'
                THEN 2

            WHEN {$name} LIKE '%administrador_cliente%'
              OR {$name} LIKE '%administrador cliente%'
              OR {$name}='administrador'
                THEN 3

            WHEN {$name} LIKE '%jefatura%'
                THEN 4

            WHEN {$name} LIKE '%paramedico%'
              OR {$name} LIKE '%paramédico%'
              OR {$name} LIKE '%paramedic%'
                THEN 6

            WHEN {$name} LIKE '%contratista%'
              OR {$name} LIKE '%contractor%'
                THEN 7

            WHEN {$name} LIKE '%usuario%'
              OR {$name} LIKE '%trabajador%'
              OR {$name} LIKE '%worker%'
                THEN 5

            ELSE NULL
        END";
    }

    public static function displayLabel(?int $level): string
    {
        return match ($level) {
            1 => 'SuperUsuario',
            2 => 'Gerente',
            3 => 'Administrador Cliente',
            4 => 'Jefatura',
            5 => 'Usuario',
            6 => 'Paramédico',
            7 => 'Contratista',
            default => 'Sin perfil reconocido',
        };
    }


    public static function managedLevelsForActorRole(string $actorRole): array
    {
        $actorRole=mb_strtolower(trim($actorRole),'UTF-8');

        return match ($actorRole) {
            'jefatura' => self::JEFATURA_MANAGED_LEVELS,
            'administrador_cliente',
            'administrador' => self::MANAGED_LEVELS,
            default => [],
        };
    }

    public static function canManageForActor(
        string $actorRole,
        int $actorCompanyId,
        int $targetCompanyId,
        ?int $targetLevel,
        bool $assignedToActor=true
    ): bool {
        if (
            $actorCompanyId<=0
            || $actorCompanyId!==$targetCompanyId
            || $targetLevel===null
        ) {
            return false;
        }

        $levels=self::managedLevelsForActorRole($actorRole);
        if (!in_array($targetLevel,$levels,true)) {
            return false;
        }

        if (mb_strtolower(trim($actorRole),'UTF-8')==='jefatura') {
            return $assignedToActor;
        }

        return true;
    }

    public static function canManage(
        int $actorCompanyId,
        int $targetCompanyId,
        ?int $targetLevel
    ): bool {
        return $actorCompanyId > 0
            && $actorCompanyId === $targetCompanyId
            && $targetLevel !== null
            && in_array($targetLevel, self::MANAGED_LEVELS, true);
    }
}
