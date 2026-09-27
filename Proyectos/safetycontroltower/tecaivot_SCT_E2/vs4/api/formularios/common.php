<?php
/** Reglas compartidas del Motor de Formularios Dinámicos (Etapa 2). */
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/FormularioRepository.php';

const FORMULARIO_FIELD_TYPES = ['text','number','date','select','checkbox','file'];

function formularioIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo,'companies.view_all');
}

function formularioRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo,'dynamic_forms.manage');
}

function formularioRequireGestionPage(PDO $pdo,string $redirectTo='../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo,'dynamic_forms.manage',$redirectTo);
}

function formularioCurrentCompany(PDO $pdo): int
{
    $id=currentUserCompanyId($pdo);
    if(!$id) responderJSON(false,null,'Tu cuenta no tiene una empresa asociada.',403);
    return $id;
}

function formularioResolveManagementCompany(PDO $pdo,?int $requested): ?int
{
    if(formularioIsGlobalAdmin($pdo)) {
        return ($requested && $requested>0) ? $requested : null;
    }
    return formularioCurrentCompany($pdo);
}

function formularioAssertVisible(PDO $pdo,array $form): void
{
    if(formularioIsGlobalAdmin($pdo)) return;
    $own=formularioCurrentCompany($pdo);
    $owner=$form['id_company'] ?? null;
    if($owner!==null && (int)$owner!==$own) {
        responderJSON(false,null,'No tienes permisos para acceder a este formulario.',403);
    }
}


/**
 * P76: valida el acceso de ejecución cuando un formulario usa la asignación
 * individual incorporada en P73. Los formularios históricos sin registros de
 * asignación conservan su comportamiento abierto dentro de la empresa.
 */
function formularioAssertExecutionAccess(PDO $pdo,array $form,int $idCompany,string $idUsers): ?array
{
    $idForm=(int)($form['id_form']??0);
    if($idForm<=0 || !formularioAssignmentSchemaReady($pdo) || !formularioTieneAsignaciones($pdo,$idForm,$idCompany)) {
        return null;
    }

    $assignment=formularioAsignacionUsuario($pdo,$idForm,$idCompany,$idUsers);
    if(!$assignment) {
        responderJSON(false,null,'Este formulario no está asignado a tu usuario.',403);
    }

    $status=(string)($assignment['status']??'');
    if($status==='submitted') {
        responderJSON(false,null,'Este formulario asignado ya fue completado.',409);
    }
    if($status!=='pending') {
        responderJSON(false,null,'Esta asignación de formulario ya no está disponible.',403);
    }

    $accessStart=trim((string)($assignment['access_start']??''));
    if($accessStart!=='' && strtotime($accessStart)!==false && strtotime($accessStart)>time()) {
        responderJSON(false,null,'Este formulario todavía no está disponible para completar.',409);
    }

    return $assignment;
}

function formularioCanEdit(PDO $pdo,array $form): bool
{
    if(!currentUserHasCapability($pdo,'dynamic_forms.manage')) return false;
    if(formularioIsGlobalAdmin($pdo)) return true;
    $owner=$form['id_company'] ?? null;
    return $owner!==null && (int)$owner===formularioCurrentCompany($pdo);
}

function formularioAssertEditable(PDO $pdo,array $form): void
{
    if(!formularioCanEdit($pdo,$form)) {
        responderJSON(false,null,'Este formulario es de solo lectura para tu perfil.',403);
    }
    if(formularioSchemaReady($pdo) && formularioTieneEnvios($pdo,(int)$form['id_form'])) {
        responderJSON(false,null,'El formulario ya tiene respuestas registradas y su estructura quedó bloqueada. Crea un nuevo formulario para modificarla.',409);
    }
    if(formularioTieneUsoProtocolos($pdo,(int)$form['id_form'])) {
        responderJSON(false,null,'El formulario está asociado a una asignación de Protocolos y su estructura quedó bloqueada para preservar trazabilidad.',409);
    }
}

function formularioNormalizeOptions($raw): ?string
{
    if($raw===null) return null;
    if(is_array($raw)) $raw=implode('|',$raw);
    $parts=preg_split('/\|/',(string)$raw);
    $clean=[];
    foreach($parts as $part){
        $v=trim((string)$part);
        if($v!=='' && !in_array($v,$clean,true)) $clean[]=$v;
    }
    return $clean ? implode('|',$clean) : null;
}

function formularioValidateFieldPayload(array $input): array
{
    $label=trim((string)($input['label']??''));
    $fieldType=strtolower(trim((string)($input['field_type']??'')));
    $required=!empty($input['is_required'])?1:0;
    $sortOrder=filter_var($input['sort_order']??0,FILTER_VALIDATE_INT);
    if($label==='' || sctTextLength($label)>150) responderJSON(false,null,'La etiqueta del campo es obligatoria y admite hasta 150 caracteres.',400);
    if(!in_array($fieldType,FORMULARIO_FIELD_TYPES,true)) responderJSON(false,null,'Tipo de campo no soportado.',400);
    if($sortOrder===false || $sortOrder<0 || $sortOrder>10000) responderJSON(false,null,'El orden del campo no es válido.',400);
    $options=formularioNormalizeOptions($input['options']??null);
    if(in_array($fieldType,['select','checkbox'],true) && !$options) responderJSON(false,null,'Los campos select y checkbox requieren opciones separadas por |.',400);
    if(!in_array($fieldType,['select','checkbox'],true)) $options=null;
    return compact('label','fieldType','options','required','sortOrder');
}

function formularioMigrationMessage(Throwable $e): bool
{
    return $e->getMessage()==='MIGRATION_REQUIRED_DYNAMIC_FORMS';
}

function formularioDecodeStoredValue(array $answer)
{
    $value=$answer['value_text']??null;
    if(($answer['field_type']??'')==='checkbox' && is_string($value) && $value!=='') {
        $decoded=json_decode($value,true);
        return is_array($decoded)?$decoded:[$value];
    }
    return $value;
}
