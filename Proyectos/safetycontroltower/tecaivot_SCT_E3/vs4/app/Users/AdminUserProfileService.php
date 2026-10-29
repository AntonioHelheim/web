<?php

require_once __DIR__ . '/../Config/MutualityOptions.php';
require_once __DIR__ . '/../Config/ProfileOptions.php';

final class SctAdminUserProfileService
{
    public function __construct(
        private PDO $pdo,
        private SctAdminUserDirectoryRepository $repository
    ) {}

    public function validate(array $input, int $companyId): array
    {
        $name=trim((string)($input['name'] ?? ''));
        $lastname=trim((string)($input['lastname'] ?? ''));
        $rut=strtoupper(trim((string)($input['rut'] ?? '')));
        $language=strtolower(trim((string)($input['language'] ?? 'es')));
        $phone=trim((string)($input['phone'] ?? ''));
        $position=trim((string)($input['position'] ?? ''));
        $projectId=filter_var($input['confirmed_project_id'] ?? null,FILTER_VALIDATE_INT) ?: 0;
        $hired=strtolower(trim((string)($input['hired_by_contractor'] ?? 'no')));
        $contractorId=filter_var($input['id_contractor_company'] ?? null,FILTER_VALIDATE_INT) ?: 0;
        $mutual=strtolower(trim((string)($input['mutual_code'] ?? '')));
        $years=filter_var(
            $input['years_experience_current_role'] ?? null,
            FILTER_VALIDATE_INT,
            ['options'=>['min_range'=>0,'max_range'=>80]]
        );
        $emName=trim((string)($input['emergency_contact_name'] ?? ''));
        $emPhone=trim((string)($input['emergency_contact_phone'] ?? ''));
        $emRelation=strtolower(trim((string)($input['emergency_contact_relation'] ?? '')));

        if ($name==='' || $lastname==='' || $rut==='' || $phone==='' || $position==='') {
            throw new InvalidArgumentException('Completa nombre, apellido, RUT, teléfono y cargo.');
        }
        if (mb_strlen($name)>50 || mb_strlen($lastname)>50) {
            throw new InvalidArgumentException('Nombre y apellido no pueden superar 50 caracteres.');
        }
        if (!preg_match('/^[0-9]{7,8}-[0-9K]$/i',$rut)) {
            throw new InvalidArgumentException('El RUT no tiene un formato válido.');
        }
        $allowed=defined('IDIOMAS_DISPONIBLES') ? IDIOMAS_DISPONIBLES : ['es','en','pt','fr','zh'];
        if (!in_array($language,$allowed,true)) {
            throw new InvalidArgumentException('Idioma no válido.');
        }
        if (!preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/',$phone)) {
            throw new InvalidArgumentException('Teléfono no válido.');
        }
        if (!$projectId || !$this->repository->projectInCompany($companyId,$projectId)) {
            throw new InvalidArgumentException('Selecciona un proyecto válido de la empresa.');
        }
        if (!SctProfileOptions::isContractorFlag($hired)) {
            throw new InvalidArgumentException('Indica si fue contratado por una empresa contratista.');
        }
        $isContractor=$hired==='yes';
        if ($isContractor && (!$contractorId || !$this->repository->contractorInCompany($companyId,$contractorId))) {
            throw new InvalidArgumentException('Selecciona una empresa contratista válida.');
        }
        if (!SctMutualityOptions::isValid($mutual)) {
            throw new InvalidArgumentException('Selecciona una afiliación de salud válida.');
        }
        if ($years===false) {
            throw new InvalidArgumentException('Los años de experiencia deben estar entre 0 y 80.');
        }
        if ($emName==='') {
            throw new InvalidArgumentException('Ingresa el contacto de emergencia.');
        }
        if (!preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/',$emPhone)) {
            throw new InvalidArgumentException('Teléfono de emergencia no válido.');
        }
        if (!SctProfileOptions::isEmergencyRelation($emRelation)) {
            throw new InvalidArgumentException('Relación del contacto de emergencia no válida.');
        }

        return [
            'name'=>$name,
            'lastname'=>$lastname,
            'rut'=>$rut,
            'language'=>$language,
            'phone'=>$phone,
            'position'=>$position,
            'confirmed_project_id'=>(int)$projectId,
            'hired_by_contractor'=>$isContractor,
            'id_contractor_company'=>$isContractor ? (int)$contractorId : null,
            'mutual_code'=>$mutual,
            'years_experience_current_role'=>(int)$years,
            'emergency_contact_name'=>$emName,
            'emergency_contact_phone'=>$emPhone,
            'emergency_contact_relation'=>$emRelation,
        ];
    }
}
