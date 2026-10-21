<?php
require_once __DIR__ . '/../app/Auth/RolePolicy.php';

$expected = [
    'superusuario'=>1,
    'gerente'=>2,
    'administrador_cliente'=>3,
    'usuario'=>4,
    'paramedico'=>5,
    'contratista'=>6,
    'trabajador'=>7,
];
foreach ($expected as $role=>$level) {
    if (SctRolePolicy::level($role) !== $level) throw new RuntimeException("Nivel incorrecto para {$role}");
}
if (SctRolePolicy::canonical('administrador_completo') !== 'superusuario') throw new RuntimeException('Alias superusuario incorrecto');
if (SctRolePolicy::canonical('cliente') !== 'gerente') throw new RuntimeException('Alias gerente incorrecto');
if (SctRolePolicy::canonical('administrador') !== 'administrador_cliente') throw new RuntimeException('Alias admin cliente incorrecto');
if (SctRolePolicy::canonical('jefatura') !== 'usuario') throw new RuntimeException('Jefatura debe migrar en mínimo privilegio');

$assertHas = static function(string $role,string $cap,bool $expected): void {
    $actual=SctRolePolicy::fallbackHas($role,$cap);
    if ($actual!==$expected) throw new RuntimeException("{$role} {$cap}: esperado ".($expected?'true':'false'));
};
$assertHas('superusuario','companies.view_all',true);
$assertHas('gerente','permissions.manage',true);
$assertHas('gerente','companies.edit_own',true);
$assertHas('gerente','health_profile.view_emergency',true);
$assertHas('administrador_cliente','permissions.manage',false);
$assertHas('administrador_cliente','users.create',true);
$assertHas('administrador_cliente','health_profile.view_emergency',true);
$assertHas('usuario','dashboard.view',true);
$assertHas('usuario','events.edit',false);
$assertHas('usuario','dynamic_forms.submit',true);
$assertHas('paramedico','induction.execute',true);
$assertHas('paramedico','dashboard.view',false);
$assertHas('contratista','dynamic_forms.submit',true);
$assertHas('contratista','workers.view',false);
$assertHas('trabajador','induction.execute',true);
$assertHas('trabajador','dynamic_forms.view',false);

echo "E3-VS1 role policy: OK\n";
