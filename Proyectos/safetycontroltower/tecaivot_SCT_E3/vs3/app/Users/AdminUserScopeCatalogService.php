<?php
require_once __DIR__ . '/../Company/AdminCompanyManagementRepository.php';

final class SctAdminUserScopeCatalogService
{
    public function __construct(private PDO $pdo) {}

    public function activeCompanyCatalog(int $companyId): array
    {
        if ($companyId<=0) {
            return [
                'gbus'=>[],
                'sites'=>[],
                'projects'=>[],
            ];
        }

        $companyRepo=new SctAdminCompanyManagementRepository($this->pdo);
        $catalog=$companyRepo->scopeCatalog($companyId);

        $projects=array_values(array_filter(
            $catalog['projects'] ?? [],
            static fn(array $row): bool =>
                (int)($row['state'] ?? 0)===1
                && trim((string)($row['gbu_code'] ?? ''))!==''
                && trim((string)($row['site_name'] ?? ''))!==''
        ));

        $gbus=[];
        $sites=[];

        foreach ($projects as $row) {
            $gbu=trim((string)$row['gbu_code']);
            $site=trim((string)$row['site_name']);

            $gbus[mb_strtolower($gbu,'UTF-8')]=$gbu;
            $sites[
                mb_strtolower($gbu,'UTF-8')
                . '|'
                . mb_strtolower($site,'UTF-8')
            ]=[
                'gbu'=>$gbu,
                'site'=>$site,
            ];
        }

        natcasesort($gbus);

        uasort(
            $sites,
            static function(array $a,array $b): int {
                $gbuCmp=strcasecmp($a['gbu'],$b['gbu']);
                return $gbuCmp!==0
                    ? $gbuCmp
                    : strcasecmp($a['site'],$b['site']);
            }
        );

        return [
            'gbus'=>array_values($gbus),
            'sites'=>array_values($sites),
            'projects'=>array_map(
                static fn(array $row): array => [
                    'id_project'=>(int)$row['id_project'],
                    'name'=>(string)$row['name'],
                    'gbu'=>trim((string)$row['gbu_code']),
                    'site'=>trim((string)$row['site_name']),
                ],
                $projects
            ),
        ];
    }
}
