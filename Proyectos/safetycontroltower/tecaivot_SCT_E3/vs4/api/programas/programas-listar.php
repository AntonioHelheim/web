<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.view');
programasRequireSchema($pdo);
$requested=isset($_GET['id_company'])?(int)$_GET['id_company']:null;
$idCompany=programasResolveCompany($pdo,$requested);
$status=trim((string)($_GET['status']??''));
if ($status!=='' && !in_array($status,PROGRAM_STATUS,true)) responderJSON(false,null,'Estado operativo inválido.',400);
$state=$_GET['state']??'';
if ($state!=='' && !in_array((string)$state,['0','1'],true)) responderJSON(false,null,'Estado de registro inválido.',400);
$idProject=isset($_GET['id_project']) && (int)$_GET['id_project']>0 ? (int)$_GET['id_project'] : null;
if ($idProject) programasValidateProject($pdo,$idProject,$idCompany,false);
$search=trim((string)($_GET['search']??''));
if (sctTextLength($search)>100) responderJSON(false,null,'La búsqueda admite hasta 100 caracteres.',400);
$filters=['status'=>$status,'state'=>$state,'id_project'=>$idProject,'search'=>$search];
responderJSON(true,[
    'programs'=>programaListar($pdo,$idCompany,$filters),
    'summary'=>programaResumen($pdo,$idCompany,$filters),
],'Programas cargados.');
