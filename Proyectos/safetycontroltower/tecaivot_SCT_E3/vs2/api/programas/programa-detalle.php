<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.view');
programasRequireSchema($pdo);
$id=(int)($_GET['id_program']??0);
if ($id<=0) responderJSON(false,null,'Programa inválido.',400);
$program=programaObtener($pdo,$id);
if (!$program) responderJSON(false,null,'Programa no encontrado.',404);
programasAssertVisible($pdo,$program);
responderJSON(true,['program'=>$program,'tracking'=>programaTrackingListar($pdo,$id)],'Detalle cargado.');
