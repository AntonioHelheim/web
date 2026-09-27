<?php
require __DIR__ . '/common.php';
requireCapability($pdo, 'dynamic_forms.manage');
$idForm=filter_input(INPUT_GET,'id_form',FILTER_VALIDATE_INT);
if(!$idForm) responderJSON(false,null,'Formulario no válido.',400);
try{
    $form=formularioObtener($pdo,(int)$idForm);
    if(!$form)responderJSON(false,null,'Formulario no encontrado.',404);
    formularioAssertVisible($pdo,$form);
    $ready=(int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='dynamic_form_assignments'")->fetchColumn()>0;
    if(!$ready)responderJSON(false,null,'Aplica la migración P73 para habilitar asignaciones de formularios.',409);
    $stmt=$pdo->prepare("SELECT a.*,u.name,u.lastname,u.email,
        (SELECT COUNT(*) FROM dynamic_form_submissions s WHERE s.id_form=a.id_form AND s.id_users=a.id_users AND s.status='submitted') AS submission_count
        FROM dynamic_form_assignments a LEFT JOIN users u ON u.id_users=a.id_users
        WHERE a.id_form=:form ORDER BY a.date_create DESC");
    $stmt->execute(['form'=>$idForm]);
    responderJSON(true,$stmt->fetchAll(PDO::FETCH_ASSOC));
}catch(PDOException $e){error_log('formularios/asignaciones-listar.php: '.$e->getMessage());responderJSON(false,null,'No se pudieron obtener las asignaciones.',500);}
