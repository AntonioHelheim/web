<?php
final class SctPermissionRepository
{
    private PDO $pdo;
    public const MARKER='system.permissions.enabled';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function functionalIdsByCodes(array $codes): array
    {
        $codes=array_values(array_unique(array_filter(array_map('strval',$codes))));
        if (!$codes) return [];
        $ph=implode(',',array_fill(0,count($codes),'?'));
        $stmt=$this->pdo->prepare('SELECT id_permission FROM permissions WHERE code IN ('.$ph.') AND code<>?');
        $params=$codes;$params[]=self::MARKER;$stmt->execute($params);
        return array_map('intval',array_column($stmt->fetchAll(),'id_permission'));
    }

    public function allFunctionalIds(): array
    {
        $stmt=$this->pdo->prepare('SELECT id_permission FROM permissions WHERE code<>:marker ORDER BY id_permission');
        $stmt->execute(['marker'=>self::MARKER]);
        return array_map('intval',array_column($stmt->fetchAll(),'id_permission'));
    }

    public function markerId(): ?int
    {
        $stmt=$this->pdo->prepare('SELECT id_permission FROM permissions WHERE code=:code LIMIT 1');
        $stmt->execute(['code'=>self::MARKER]);
        $id=$stmt->fetchColumn();return $id===false?null:(int)$id;
    }

    public function replaceRolePermissions(int $roleGroupId,array $permissionIds,bool $enableRbac=true): void
    {
        $ids=array_values(array_unique(array_filter(array_map('intval',$permissionIds),fn($v)=>$v>0)));
        if ($enableRbac && ($marker=$this->markerId())!==null) $ids[]=$marker;
        $ids=array_values(array_unique($ids));
        $del=$this->pdo->prepare('DELETE FROM role_permissions WHERE id_role_group=:id_role_group');
        $del->execute(['id_role_group'=>$roleGroupId]);
        if (!$ids) return;
        $ins=$this->pdo->prepare('INSERT INTO role_permissions(id_role_group,id_permission) VALUES(:role,:perm)');
        foreach($ids as $id)$ins->execute(['role'=>$roleGroupId,'perm'=>$id]);
    }
}
