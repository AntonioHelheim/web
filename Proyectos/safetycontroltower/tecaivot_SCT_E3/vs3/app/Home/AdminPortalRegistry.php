<?php

/**
 * Safety Control Tower — Etapa 3 VS2
 * Catálogo de navegación específico para Administrador Cliente.
 *
 * Este catálogo define únicamente estructura de portal. La autorización real
 * continúa resolviéndose mediante lib/auth.php y capabilities del usuario.
 */
final class SctAdminPortalRegistry
{
    public static function overviewModules(): array
    {
        return [
            [
                'id' => 'ds76',
                'label_key' => 'admin_portal_module_ds76',
                'icon' => 'bi-shield-check',
                'tone' => 'navy',
                'href' => null,
            ],
            [
                'id' => 'audits',
                'label_key' => 'admin_portal_module_audits',
                'icon' => 'bi-clipboard2-data',
                'tone' => 'cyan',
                'href' => 'api/auditorias/gestion-auditorias.php',
                'cap' => 'audits.manage',
            ],
            [
                'id' => 'medical_attention',
                'label_key' => 'admin_portal_module_medical_attention',
                'icon' => 'bi-heart-pulse',
                'tone' => 'violet',
                'href' => null,
            ],
            [
                'id' => 'company_tree',
                'label_key' => 'admin_portal_module_company_tree',
                'icon' => 'bi-diagram-3',
                'tone' => 'green',
                'href' => null,
            ],
            [
                'id' => 'charts',
                'label_key' => 'admin_portal_module_charts',
                'icon' => 'bi-bar-chart-line',
                'tone' => 'gold',
                'href' => 'api/dashboard/dashboard.php',
                'cap' => 'dashboard.view',
            ],
            [
                'id' => 'just_culture',
                'label_key' => 'admin_portal_module_just_culture',
                'icon' => 'bi-balance-scale',
                'tone' => 'rose',
                'href' => null,
            ],
            [
                'id' => 'programs',
                'label_key' => 'admin_portal_module_programs',
                'icon' => 'bi-calendar3-range',
                'tone' => 'cyan',
                'href' => 'api/programas/gestion-programas.php',
                'cap' => 'programs.view',
            ],
            [
                'id' => 'post_services',
                'label_key' => 'admin_portal_module_post_services',
                'icon' => 'bi-tools',
                'tone' => 'navy',
                'href' => null,
            ],
            [
                'id' => 'campaigns',
                'label_key' => 'admin_portal_module_campaigns',
                'icon' => 'bi-megaphone',
                'tone' => 'violet',
                'href' => null,
            ],
            [
                'id' => 'lessons_learned',
                'label_key' => 'admin_portal_module_lessons_learned',
                'icon' => 'bi-lightbulb',
                'tone' => 'gold',
                'href' => null,
            ],
            [
                'id' => 'courses',
                'label_key' => 'admin_portal_module_courses',
                'icon' => 'bi-mortarboard',
                'tone' => 'cyan',
                'href' => 'api/induccion/gestion-induccion.php',
                'cap' => 'induction.manage',
            ],
            [
                'id' => 'formats',
                'label_key' => 'admin_portal_module_formats',
                'icon' => 'bi-file-earmark-text',
                'tone' => 'green',
                'href' => 'api/formularios/gestion-formularios.php',
                'cap' => 'dynamic_forms.manage',
            ],
            [
                'id' => 'top_report',
                'label_key' => 'admin_portal_module_top_report',
                'icon' => 'bi-trophy',
                'tone' => 'gold',
                'href' => null,
            ],
            [
                'id' => 'gfc',
                'label_key' => 'admin_portal_module_gfc',
                'icon' => 'bi-person-lines-fill',
                'tone' => 'violet',
                'href' => null,
            ],
            [
                'id' => 'reports',
                'label_key' => 'admin_portal_module_reports',
                'icon' => 'bi-file-earmark-bar-graph',
                'tone' => 'navy',
                'href' => null,
            ],
            [
                'id' => 'blockages',
                'label_key' => 'admin_portal_module_blockages',
                'icon' => 'bi-sign-stop',
                'tone' => 'rose',
                'href' => null,
            ],
            [
                'id' => 'self_assessment',
                'label_key' => 'admin_portal_module_self_assessment',
                'icon' => 'bi-list-check',
                'tone' => 'violet',
                'href' => 'api/autoevaluaciones/gestion-autoevaluaciones.php',
                'cap' => 'self_assessments.manage',
            ],
            [
                'id' => 'microdecisions',
                'label_key' => 'admin_portal_module_microdecisions',
                'icon' => 'bi-bezier2',
                'tone' => 'cyan',
                'href' => null,
            ],
        ];
    }


    public static function moduleById(string $moduleId): ?array
    {
        foreach (self::overviewModules() as $module) {
            if ((string)($module['id'] ?? '') === $moduleId) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Catálogo inicial solicitado para la navegación por GBU / Sitio.
     *
     * La disponibilidad real de cada sitio se determina contra projects de la
     * empresa. Los nombres de sitio no crean proyectos ni saltan RBAC.
     */
    public static function siteCatalog(): array
    {
        return [
            'R&F' => [
                'BESS COYA II',
                'BESS CALAMA',
                'PV + BESS POSADA',
                'BESS LIBELULA II',
                'WIND ENTRE RIOS',
                'WIND EL ROSAL',
                'WIND LOS PORTONES',
                'WIND PAMPA FIDELIA',
                'WIND CHEQUENES',
            ],
            'NETWORK' => [],
            'GEN' => [],
        ];
    }
}
