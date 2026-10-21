<?php
/**
 * Canonical role catalogue for Safety Control Tower E3-VS1.
 *
 * IMPORTANT DOMAIN RULE:
 * - "workers" is the personnel master-data entity.
 * - "usuario" is an access-enabled worker/person who owns a users account.
 * - A worker without a users row is data only and has no login or SCT role.
 *
 * Hierarchy is used for user-management scope.
 * Functional authorization is resolved from permissions/role_permissions when
 * the E3-VS1 migration marker is active.
 */
final class SctRolePolicy
{
    public const ROLES = [
        'superusuario' => [
            'level' => 1,
            'label' => 'SuperUsuario',
            'global_scope' => true,
            'user_scope' => 'global',
            'can_manage_assigned' => true,
            'viewable_user_levels' => [1,2,3,4,5,6,7],
            'managed_user_levels' => [1,2,3,4,5,6,7],
            'assignable_user_levels' => [1,2,3,4,5,6,7],
        ],
        'gerente' => [
            'level' => 2,
            'label' => 'Gerente',
            'global_scope' => false,
            'user_scope' => 'company',
            'can_manage_assigned' => true,
            // Puede ver Gerentes, pero sólo Administrador/SuperUsuario los administran.
            'viewable_user_levels' => [2,3,4,5,6,7],
            'managed_user_levels' => [3,4,5,6,7],
            'assignable_user_levels' => [3,4,5,6,7],
        ],
        'administrador_cliente' => [
            'level' => 3,
            'label' => 'Administrador Cliente',
            'global_scope' => false,
            'user_scope' => 'company',
            'can_manage_assigned' => true,
            'viewable_user_levels' => [2,3,4,5,6,7],
            // Administrador Cliente sí puede administrar Gerentes de su empresa.
            'managed_user_levels' => [2,3,4,5,6,7],
            'assignable_user_levels' => [2,3,4,5,6,7],
        ],
        'jefatura' => [
            'level' => 4,
            'label' => 'Jefatura',
            'global_scope' => false,
            'user_scope' => 'assigned',
            'can_manage_assigned' => true,
            // Sólo usuarios explícitamente asignados por Gerente/Administrador/SuperUsuario.
            'viewable_user_levels' => [2,3,4,5,6,7],
            // Gerentes asignados son sólo visibles; Jefatura no los administra.
            'managed_user_levels' => [3,4,5,6,7],
            // Conserva la capacidad actual de asignar el rol Usuario cuando corresponda.
            'assignable_user_levels' => [5],
        ],
        'usuario' => [
            'level' => 5,
            'label' => 'Usuario',
            'global_scope' => false,
            'user_scope' => 'self',
            'can_manage_assigned' => false,
            'viewable_user_levels' => [],
            'managed_user_levels' => [],
            'assignable_user_levels' => [],
        ],
        'paramedico' => [
            'level' => 6,
            'label' => 'Paramédico',
            'global_scope' => false,
            'user_scope' => 'assigned',
            'can_manage_assigned' => false,
            'viewable_user_levels' => [2,3,4,5,6,7],
            'managed_user_levels' => [],
            'assignable_user_levels' => [],
        ],
        'contratista' => [
            'level' => 7,
            'label' => 'Contratista',
            'global_scope' => false,
            'user_scope' => 'assigned',
            'can_manage_assigned' => false,
            'viewable_user_levels' => [2,3,4,5,6,7],
            'managed_user_levels' => [],
            'assignable_user_levels' => [],
        ],
    ];

    /**
     * Transitional aliases.
     * "trabajador" is no longer a login role: legacy assignments are interpreted
     * as "usuario" only while the corrective migration is being deployed.
     */
    public const ALIASES = [
        'administrador_completo' => 'superusuario',
        'admin_completo' => 'superusuario',
        'administrador' => 'administrador_cliente',
        'cliente' => 'gerente',
        'trabajador' => 'usuario',
    ];

    /**
     * Safe fallback used only while role_permissions is not authoritative.
     * Database RBAC remains the source of truth after schema migration.
     */
    public const FALLBACK_CAPABILITIES = [
        'superusuario' => ['*'],
        'gerente' => [
            'companies.view_own','companies.edit_own',
            'users.manage','users.create','users.assign_roles','users.view','users.edit','users.state','users.access','users.photo',
            'workers.manage','workers.view','workers.create','workers.edit','workers.state','workers.photo',
            'projects.manage','projects.view','projects.create','projects.edit','projects.state','projects.assign_workers',
            'centers.manage','centers.view','centers.create','centers.edit','centers.state',
            'events.manage','events.view','events.create','events.edit','events.state','events.evidence_upload','events.evidence_manage','events.tracking',
            'induction.manage','induction.view','induction.create','induction.edit','induction.state','induction.assign','induction.materials','induction.questions','induction.execute',
            'audits.manage','audits.view','audits.create','audits.edit','audits.state','audits.assign','audits.questions','audits.execute',
            'self_assessments.manage','self_assessments.view','self_assessments.create','self_assessments.edit','self_assessments.state','self_assessments.assign','self_assessments.questions','self_assessments.execute',
            'dynamic_forms.manage','dynamic_forms.view','dynamic_forms.create','dynamic_forms.edit','dynamic_forms.state','dynamic_forms.fields','dynamic_forms.submissions','dynamic_forms.submit','dynamic_forms.files',
            'protocols.manage','protocols.view','protocols.create','protocols.edit','protocols.state','protocols.forms','protocols.assign','protocols.review','protocols.tracking','protocols.execute',
            'health_profile.view_emergency','questions.manage','dashboard.view','change_history.view','programs.view','programs.create','programs.edit','programs.state','programs.tracking','permissions.manage',
        ],
        'administrador_cliente' => [
            'companies.view_own','companies.edit_own',
            'users.manage','users.create','users.assign_roles','users.view','users.edit','users.state','users.access','users.photo',
            'workers.manage','workers.view','workers.create','workers.edit','workers.state','workers.photo',
            'projects.manage','projects.view','projects.create','projects.edit','projects.state','projects.assign_workers',
            'centers.manage','centers.view','centers.create','centers.edit','centers.state',
            'events.manage','events.view','events.create','events.edit','events.state','events.evidence_upload','events.evidence_manage','events.tracking',
            'induction.manage','induction.view','induction.create','induction.edit','induction.state','induction.assign','induction.materials','induction.questions','induction.execute',
            'audits.manage','audits.view','audits.create','audits.edit','audits.state','audits.assign','audits.questions','audits.execute',
            'self_assessments.manage','self_assessments.view','self_assessments.create','self_assessments.edit','self_assessments.state','self_assessments.assign','self_assessments.questions','self_assessments.execute',
            'dynamic_forms.manage','dynamic_forms.view','dynamic_forms.create','dynamic_forms.edit','dynamic_forms.state','dynamic_forms.fields','dynamic_forms.submissions','dynamic_forms.submit','dynamic_forms.files',
            'protocols.manage','protocols.view','protocols.create','protocols.edit','protocols.state','protocols.forms','protocols.assign','protocols.review','protocols.tracking','protocols.execute',
            'health_profile.view_emergency','questions.manage','dashboard.view','change_history.view','programs.view','programs.create','programs.edit','programs.state','programs.tracking',
        ],
        'jefatura' => [
            'companies.view_own',
            'users.manage','users.create','users.assign_roles','users.view','users.edit','users.state','users.access','users.photo',
            'workers.manage','workers.view','workers.create','workers.edit','workers.state','workers.photo',
            'projects.manage','projects.view','projects.create','projects.edit','projects.state','projects.assign_workers',
            'centers.manage','centers.view','centers.create','centers.edit','centers.state',
            'events.manage','events.view','events.create','events.edit','events.state','events.evidence_upload','events.evidence_manage','events.tracking',
            'induction.manage','induction.view','induction.create','induction.edit','induction.state','induction.assign','induction.materials','induction.questions','induction.execute',
            'audits.manage','audits.view','audits.create','audits.edit','audits.state','audits.assign','audits.questions','audits.execute',
            'self_assessments.manage','self_assessments.view','self_assessments.create','self_assessments.edit','self_assessments.state','self_assessments.assign','self_assessments.questions','self_assessments.execute',
            'dynamic_forms.manage','dynamic_forms.view','dynamic_forms.create','dynamic_forms.edit','dynamic_forms.state','dynamic_forms.fields','dynamic_forms.submissions','dynamic_forms.submit','dynamic_forms.files',
            'protocols.manage','protocols.view','protocols.create','protocols.edit','protocols.state','protocols.forms','protocols.assign','protocols.review','protocols.tracking','protocols.execute',
            'dashboard.view','change_history.view','programs.view','programs.create','programs.edit','programs.state','programs.tracking',
        ],
        'usuario' => [
            'companies.view_own','workers.view','projects.view','centers.view','events.view',
            'induction.view','induction.execute','audits.view','audits.execute','self_assessments.view','self_assessments.execute',
            'dynamic_forms.view','dynamic_forms.submit','dynamic_forms.files',
            'protocols.view','dashboard.view','programs.view',
        ],
        'paramedico' => ['users.view','induction.view','induction.execute'],
        'contratista' => ['users.view','dynamic_forms.view','dynamic_forms.submit','dynamic_forms.files'],
    ];

    public static function canonical(string $role): string
    {
        $role = function_exists('mb_strtolower') ? mb_strtolower(trim($role), 'UTF-8') : strtolower(trim($role));
        $role = str_replace(['-', ' '], '_', $role);
        return self::ALIASES[$role] ?? $role;
    }

    public static function exists(string $role): bool
    {
        return isset(self::ROLES[self::canonical($role)]);
    }

    public static function level(string $role): ?int
    {
        $role = self::canonical($role);
        return isset(self::ROLES[$role]) ? (int) self::ROLES[$role]['level'] : null;
    }

    public static function label(string $role): string
    {
        $role = self::canonical($role);
        return self::ROLES[$role]['label'] ?? ucfirst(str_replace('_', ' ', $role));
    }

    public static function byLevel(int $level): ?string
    {
        foreach (self::ROLES as $name => $policy) {
            if ((int)$policy['level'] === $level) return $name;
        }
        return null;
    }

    public static function primary(array $roles): ?string
    {
        $best = null;
        $bestLevel = PHP_INT_MAX;
        foreach ($roles as $role) {
            $canonical = self::canonical((string)$role);
            $level = self::level($canonical);
            if ($level !== null && $level < $bestLevel) {
                $best = $canonical;
                $bestLevel = $level;
            }
        }
        return $best;
    }

    public static function fallbackHas(string $role, string $capability): bool
    {
        $role = self::canonical($role);
        $caps = self::FALLBACK_CAPABILITIES[$role] ?? [];
        return in_array('*', $caps, true) || in_array($capability, $caps, true);
    }
}
