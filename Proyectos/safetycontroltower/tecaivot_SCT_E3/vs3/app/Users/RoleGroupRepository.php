<?php
final class SctRoleGroupRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ensure(int $companyId,string $name,string $description,string $actor): int
    {
        $stmt=$this->pdo->prepare(
            'SELECT id_role_group FROM users_role_group
             WHERE id_company=:id_company AND name=:name AND state=1
             ORDER BY id_role_group ASC LIMIT 1'
        );
        $stmt->execute(['id_company'=>$companyId,'name'=>$name]);
        $id=$stmt->fetchColumn();
        if ($id!==false) return (int)$id;

        $stmt=$this->pdo->prepare(
            'INSERT INTO users_role_group(id_company,name,description,state,create_by,date_create,last_update)
             VALUES(:id_company,:name,:description,1,:create_by,NOW(),NOW())'
        );
        $stmt->execute(['id_company'=>$companyId,'name'=>$name,'description'=>$description,'create_by'=>$actor]);
        return (int)$this->pdo->lastInsertId();
    }
}
