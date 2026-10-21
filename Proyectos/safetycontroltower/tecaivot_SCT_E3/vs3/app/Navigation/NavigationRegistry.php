<?php
/** Single navigation catalogue consumed by navbar and welcome portal. */
final class SctNavigationRegistry
{
    public static function navbar(PDO $pdo, string $role, string $basePath='./'): array
    {
        $role = SctRolePolicy::canonical($role);

        $home = [
            'id' => 'home',
            'label_key' => 'nav_home',
            'href' => 'bienvenida.php',
            'icon' => 'bi-house-door',
            'brand' => 'navy',
            'authenticated' => true,
        ];

        if ($role === 'usuario') {
            $items = [
                $home,
                [
                    'id' => 'my_reports',
                    'label_key' => 'home_my_reports',
                    'href' => 'api/eventos/gestion-eventos.php?mine=1',
                    'icon' => 'bi-journal-text',
                    'brand' => 'cyan',
                    'cap' => 'events.view',
                ],
                [
                    'id' => 'my_evaluations',
                    'label_key' => 'home_my_evaluations',
                    'href' => 'api/evaluaciones/mis-evaluaciones.php',
                    'icon' => 'bi-clipboard2-check',
                    'brand' => 'gold',
                    'authenticated' => true,
                ],
                [
                    'id' => 'my_data',
                    'label_key' => 'home_my_data',
                    'href' => 'bienvenida.php?edit=profile',
                    'icon' => 'bi-person-vcard',
                    'brand' => 'navy',
                    'authenticated' => true,
                ],
                [
                    'id' => 'modules',
                    'label_key' => 'home_user_modules_title',
                    'href' => 'bienvenida.php#modulos',
                    'icon' => 'bi-grid',
                    'brand' => 'green',
                    'authenticated' => true,
                ],
            ];

            return [
                'primary' => self::filterAndResolve($pdo, $items, $basePath),
                'management' => [],
            ];
        }

        $items = array_merge([$home], self::directDefinitions());

        return [
            'primary' => self::filterAndResolve($pdo, $items, $basePath),
            'management' => self::management($pdo, $basePath),
        ];
    }

    public static function direct(PDO $pdo, string $basePath='./'): array
    {
        return self::filterAndResolve($pdo, self::directDefinitions(), $basePath);
    }

    private static function directDefinitions(): array
    {
        return [
            ['id'=>'health_safety','label_key'=>'home_health_safety','href'=>'api/actividades/health-safety.php','icon'=>'bi-heart-pulse','brand'=>'navy','any_cap'=>['protocols.view','dynamic_forms.view','audits.view','self_assessments.view']],
            ['id'=>'standard_16','label_key'=>'home_standard_16','href'=>null,'icon'=>'bi-shield-check','brand'=>'cyan','disabled'=>true],
            ['id'=>'it','label_key'=>'home_it','href'=>null,'icon'=>'bi-pc-display','brand'=>'gold','disabled'=>true],
            ['id'=>'environment','label_key'=>'home_environment','href'=>null,'icon'=>'bi-tree','brand'=>'navy','disabled'=>true],
            ['id'=>'courses','label_key'=>'home_courses','href'=>'api/induccion/mis-induccion.php','icon'=>'bi-mortarboard','brand'=>'cyan','cap'=>'induction.view'],
            ['id'=>'certificates','label_key'=>'home_certificates','href'=>'api/induccion/mis-certificados.php','icon'=>'bi-award','brand'=>'gold','cap'=>'induction.view'],
            ['id'=>'my_data','label_key'=>'home_my_data','href'=>'api/usuarios/mi-perfil.php','icon'=>'bi-person-vcard','brand'=>'navy','authenticated'=>true],
            ['id'=>'support','label_key'=>'home_support','href'=>'mailto:contacto@safetycontroltower.cl','icon'=>'bi-headset','brand'=>'cyan','authenticated'=>true,'absolute'=>true],
        ];
    }

    public static function userOperationalModules(PDO $pdo, string $basePath='./'): array
    {
        $items = [
            [
                'id'=>'health_safety',
                'label_key'=>'home_health_safety',
                'href'=>'api/actividades/health-safety.php',
                'icon'=>'bi-heart-pulse',
                'brand'=>'navy',
                'any_cap'=>['protocols.view','dynamic_forms.view','audits.view','self_assessments.view'],
            ],
            [
                'id'=>'environment',
                'label_key'=>'home_environment',
                'href'=>null,
                'icon'=>'bi-tree',
                'brand'=>'green',
                'disabled'=>true,
            ],
            [
                'id'=>'archaeology',
                'label_key'=>'home_archaeology',
                'href'=>null,
                'icon'=>'bi-bank',
                'brand'=>'gold',
                'disabled'=>true,
            ],
            [
                'id'=>'paleontology',
                'label_key'=>'home_paleontology',
                'href'=>null,
                'icon'=>'bi-gem',
                'brand'=>'cyan',
                'disabled'=>true,
            ],
            [
                'id'=>'standard_16',
                'label_key'=>'home_standard_16',
                'href'=>null,
                'icon'=>'bi-shield-check',
                'brand'=>'cyan',
                'disabled'=>true,
            ],
            [
                'id'=>'major_risk',
                'label_key'=>'home_major_risk',
                'href'=>null,
                'icon'=>'bi-exclamation-octagon',
                'brand'=>'rose',
                'disabled'=>true,
            ],
            [
                'id'=>'high_risk_work',
                'label_key'=>'home_high_risk_work',
                'href'=>null,
                'icon'=>'bi-cone-striped',
                'brand'=>'gold',
                'disabled'=>true,
            ],
            [
                'id'=>'one_safety',
                'label_key'=>'home_one_safety',
                'href'=>null,
                'icon'=>'bi-shield-fill-check',
                'brand'=>'navy',
                'disabled'=>true,
            ],
        ];

        return self::filterAndResolve($pdo,$items,$basePath);
    }

    public static function management(PDO $pdo, string $basePath='./'): array
    {
        $items = [
            ['id'=>'dashboard','label_key'=>'nav_dashboard','desc_key'=>'dashboard_intro','href'=>'api/dashboard/dashboard.php','icon'=>'bi-speedometer2','tone'=>'blue','cap'=>'dashboard.view'],
            ['id'=>'events','label_key'=>'nav_events','desc_key'=>'events_intro','href'=>'api/eventos/gestion-eventos.php','icon'=>'bi-exclamation-triangle','tone'=>'amber','cap'=>'events.view'],
            ['id'=>'induction','label_key'=>'mgmt_induction_title','desc_key'=>'mgmt_induction_text','href'=>'api/induccion/gestion-induccion.php','icon'=>'bi-mortarboard-fill','tone'=>'blue','cap'=>'induction.manage'],
            ['id'=>'audits','label_key'=>'mgmt_audits_title','desc_key'=>'mgmt_audits_text','href'=>'api/auditorias/gestion-auditorias.php','icon'=>'bi-clipboard2-data','tone'=>'cyan','cap'=>'audits.manage'],
            ['id'=>'self_assessments','label_key'=>'mgmt_self_title','desc_key'=>'mgmt_self_text','href'=>'api/autoevaluaciones/gestion-autoevaluaciones.php','icon'=>'bi-list-check','tone'=>'violet','cap'=>'self_assessments.manage'],
            ['id'=>'protocols','label_key'=>'mgmt_protocols_title','desc_key'=>'mgmt_protocols_text','href'=>'api/protocolos/gestion-protocolos.php','icon'=>'bi-clipboard2-pulse','tone'=>'rose','cap'=>'protocols.manage'],
            ['id'=>'forms','label_key'=>'mgmt_forms_title','desc_key'=>'mgmt_forms_text','href'=>'api/formularios/gestion-formularios.php','icon'=>'bi-card-list','tone'=>'green','cap'=>'dynamic_forms.manage'],
            ['id'=>'question_bank','label_key'=>'question_bank_nav','desc_key'=>'question_bank_nav_desc','href'=>'api/preguntas/gestion-preguntas.php','icon'=>'bi-translate','tone'=>'violet','cap'=>'questions.manage'],
            ['id'=>'programs','label_key'=>'nav_programs','desc_key'=>'programs_intro','href'=>'api/programas/gestion-programas.php','icon'=>'bi-calendar3-range','tone'=>'cyan','cap'=>'programs.view'],
            ['id'=>'users','label_key'=>'nav_users','desc_key'=>'users_intro','href'=>'api/usuarios/gestion-usuarios.php','icon'=>'bi-people','tone'=>'blue','any_cap'=>['users.manage','users.view']],
            ['id'=>'workers','label_key'=>'nav_workers','desc_key'=>'workers_intro','href'=>'api/trabajadores/gestion-trabajadores.php','icon'=>'bi-person-badge','tone'=>'cyan','cap'=>'workers.manage'],
            ['id'=>'projects','label_key'=>'nav_projects','desc_key'=>'projects_intro','href'=>'api/proyectos/gestion-proyectos.php','icon'=>'bi-diagram-3','tone'=>'violet','cap'=>'projects.manage'],
            ['id'=>'centers','label_key'=>'nav_centers','desc_key'=>'centers_intro','href'=>'api/centros/gestion-centros.php','icon'=>'bi-geo-alt','tone'=>'green','cap'=>'centers.manage'],
            ['id'=>'companies','label_key'=>'nav_companies','desc_key'=>'companies_intro','href'=>'api/empresas/gestion-empresas.php','icon'=>'bi-buildings','tone'=>'amber','any_cap'=>['companies.manage_all','companies.edit_own']],
            ['id'=>'permissions','label_key'=>'nav_permissions','desc_key'=>'permissions_unavailable_notice','href'=>'api/permisos/gestion-permisos.php','icon'=>'bi-shield-lock','tone'=>'violet','cap'=>'permissions.manage'],
            ['id'=>'history','label_key'=>'nav_history','desc_key'=>'history_intro','href'=>'api/historial/gestion-historial.php','icon'=>'bi-clock-history','tone'=>'cyan','cap'=>'change_history.view'],
        ];
        return self::filterAndResolve($pdo,$items,$basePath);
    }

    private static function filterAndResolve(PDO $pdo,array $items,string $basePath): array
    {
        if ($basePath!=='' && substr($basePath,-1)!=='/') $basePath.='/';
        $result=[];
        foreach ($items as $item) {
            $allowed=true;
            if (!empty($item['cap'])) $allowed=currentUserHasCapability($pdo,(string)$item['cap']);
            if (!empty($item['any_cap'])) $allowed=currentUserHasAnyCapability($pdo,(array)$item['any_cap']);
            if (!$allowed) continue;
            $item['label']=function_exists('t') ? t((string)$item['label_key']) : (string)$item['label_key'];
            if (!empty($item['desc_key'])) $item['description']=function_exists('t') ? t((string)$item['desc_key']) : (string)$item['desc_key'];
            if (!empty($item['href']) && empty($item['absolute'])) $item['href']=$basePath.ltrim((string)$item['href'],'/');
            $result[]=$item;
        }
        return $result;
    }
}
