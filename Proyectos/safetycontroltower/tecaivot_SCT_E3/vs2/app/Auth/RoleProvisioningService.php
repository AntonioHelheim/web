<?php
require_once __DIR__.'/RolePolicy.php';
require_once __DIR__.'/PermissionRepository.php';
require_once __DIR__.'/../Users/RoleGroupRepository.php';

final class SctRoleProvisioningService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function provisionCompany(int $companyId,string $actor): array
    {
        $groups=new SctRoleGroupRepository($this->pdo);
        $permissions=new SctPermissionRepository($this->pdo);
        $created=[];
        foreach(SctRolePolicy::ROLES as $name=>$policy){
            $description=$this->description($name);
            $roleId=$groups->ensure($companyId,$name,$description,$actor);
            $ids=$name==='superusuario'
                ? $permissions->allFunctionalIds()
                : $permissions->functionalIdsByCodes(SctRolePolicy::FALLBACK_CAPABILITIES[$name]??[]);
            $permissions->replaceRolePermissions($roleId,$ids,true);
            $created[$name]=$roleId;
        }
        return $created;
    }

    private function description(string $role): string
    {
        switch ($role) {
            case 'superusuario': return 'Acceso global a todo SCT';
            case 'gerente': return 'Gerente de empresa: control total de su empresa';
            case 'administrador_cliente': return 'Administrador cliente de empresa';
            case 'jefatura': return 'Jefatura de empresa';
            case 'usuario': return 'Trabajador con cuenta de acceso a SCT';
            case 'paramedico': return 'Paramédico';
            case 'contratista': return 'Contratista';
            default: return SctRolePolicy::label($role);
        }
    }
}
