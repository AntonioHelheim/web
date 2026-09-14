<?php
require __DIR__.'/common.php';
requireCapability($pdo,'programs.view');
if (!programasIsGlobalAdmin($pdo)) responderJSON(false,null,'No tienes alcance global.',403);
responderJSON(true,programaListarEmpresas($pdo),'Empresas disponibles.');
