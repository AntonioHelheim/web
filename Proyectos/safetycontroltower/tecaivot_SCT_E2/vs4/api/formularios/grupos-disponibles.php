<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'dynamic_forms.manage');
$idCompany = formularioIsGlobalAdmin($pdo)
    ? (filter_input(INPUT_GET, 'id_company', FILTER_VALIDATE_INT) ?: 0)
    : formularioCurrentCompany($pdo);
if ($idCompany <= 0) responderJSON(true, []);
try {
    $stmt=$pdo->prepare("SELECT rg.id_role_group,rg.name,rg.description,u.id_users
        FROM users_role_group rg
        INNER JOIN users_role ur ON ur.id_role_group=rg.id_role_group AND ur.state=1
        INNER JOIN users u ON u.id_users=ur.id_users AND u.state=1 AND u.id_company=rg.id_company
        WHERE rg.id_company=:company AND rg.state=1 ORDER BY rg.name,u.lastname,u.name");
    $stmt->execute(['company'=>$idCompany]);
    $groups=[];
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
        $k=(string)$row['id_role_group'];
        if(!isset($groups[$k])){
            $label=trim((string)($row['description']??''));
            if($label==='')$label=ucfirst(str_replace('_',' ',(string)$row['name']));
            $groups[$k]=['id'=>(int)$row['id_role_group'],'name'=>(string)$row['name'],'label'=>$label,'users'=>[]];
        }
        $groups[$k]['users'][]=(string)$row['id_users'];
    }
    foreach($groups as &$g){$g['users']=array_values(array_unique($g['users']));$g['count']=count($g['users']);}
    responderJSON(true,array_values($groups));
}catch(PDOException $e){error_log('formularios/grupos-disponibles.php: '.$e->getMessage());responderJSON(false,null,'No se pudieron obtener los grupos.',500);}
