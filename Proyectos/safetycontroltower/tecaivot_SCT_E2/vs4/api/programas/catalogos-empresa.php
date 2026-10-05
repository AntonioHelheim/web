<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.view');
programasRequireSchema($pdo);
$requested=isset($_GET['id_company'])?(int)$_GET['id_company']:null;
$idCompany=programasResolveCompany($pdo,$requested);
responderJSON(true,[
    'projects'=>programaListarProyectosEmpresa($pdo,$idCompany),
    'users'=>currentUserHasAnyCapability($pdo,['programs.create','programs.edit'])
        ? programaListarUsuariosEmpresa($pdo,$idCompany)
        : [],
],'Catálogos disponibles.');
