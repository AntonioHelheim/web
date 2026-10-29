<?php

final class SctAdminCompanyManagementService
{
    public function validateCompany(array $input): array
    {
        $rut=strtoupper(trim((string)($input['rut'] ?? '')));
        $name=trim((string)($input['razon_social'] ?? ''));
        $address=trim((string)($input['address'] ?? ''));
        $email=strtolower(trim((string)($input['email'] ?? '')));
        $isContractor=filter_var(
            $input['is_contractor'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($rut==='' || $name==='' || $address==='' || $email==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_required')
            );
        }
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                t('company_manage_err_email')
            );
        }

        return [
            'rut'=>$rut,
            'razon_social'=>$name,
            'address'=>$address,
            'email'=>$email,
            'is_contractor'=>$isContractor,
        ];
    }

    public function validateCenter(array $input): array
    {
        $name=trim((string)($input['name'] ?? ''));
        $address=trim((string)($input['address'] ?? ''));
        $description=trim((string)($input['description'] ?? ''));
        $state=(int)($input['state'] ?? 1);

        if ($name==='' || $address==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_center_required')
            );
        }

        return [
            'name'=>$name,
            'address'=>$address,
            'description'=>$description,
            'state'=>$state===0 ? 0 : 1,
        ];
    }

    public function validateProject(array $input): array
    {
        $gbu=trim((string)($input['gbu_code'] ?? ''));
        $site=trim((string)($input['site_name'] ?? ''));
        $name=trim((string)($input['name'] ?? ''));
        $description=trim((string)($input['description'] ?? ''));
        $state=(int)($input['state'] ?? 1);

        if ($gbu==='' || $site==='' || $name==='') {
            throw new InvalidArgumentException(
                t('company_manage_err_project_required')
            );
        }

        return [
            'gbu_code'=>$gbu,
            'site_name'=>$site,
            'name'=>$name,
            'description'=>$description,
            'state'=>$state===0 ? 0 : 1,
        ];
    }
}
