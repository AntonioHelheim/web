<?php
/**
 * Repositorio compartido del Motor de Formularios Dinámicos (Etapa 2).
 */

function formularioSchemaReady(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) return $ready;
    try {
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.TABLES "
            . "WHERE TABLE_SCHEMA = DATABASE() "
            . "AND TABLE_NAME IN ('dynamic_form_submissions','dynamic_form_answers')"
        );
        return $ready = ((int)$stmt->fetchColumn() === 2);
    } catch (Throwable $e) {
        error_log('FormularioRepository formularioSchemaReady: '.$e->getMessage());
        return $ready = false;
    }
}

function formularioRequireSchema(PDO $pdo): void
{
    if (!formularioSchemaReady($pdo)) {
        throw new RuntimeException('MIGRATION_REQUIRED_DYNAMIC_FORMS');
    }
}

function formularioObtener(PDO $pdo, int $idForm): ?array
{
    $stmt = $pdo->prepare(
        'SELECT f.*, c.razon_social AS company_name, '
        . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count, '
        . '       '.(formularioSchemaReady($pdo)
            ? '(SELECT COUNT(*) FROM dynamic_form_submissions s WHERE s.id_form=f.id_form AND s.status<>\'voided\')'
            : '0').' AS submission_count '
        . 'FROM dynamic_forms f '
        . 'LEFT JOIN company c ON c.id_company=f.id_company '
        . 'WHERE f.id_form=:id_form '
        . 'LIMIT 1'
    );
    $stmt->execute(['id_form'=>$idForm]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function formularioListarGestion(PDO $pdo, ?int $idCompany, bool $globalAdmin): array
{
    $submissionExpr = formularioSchemaReady($pdo)
        ? '(SELECT COUNT(*) FROM dynamic_form_submissions s WHERE s.id_form=f.id_form AND s.status<>\'voided\')'
        : '0';

    if ($globalAdmin) {
        if ($idCompany === null || $idCompany <= 0) {
            $sql = 'SELECT f.*, c.razon_social AS company_name, '
                 . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count, '
                 . '       '.$submissionExpr.' AS submission_count '
                 . 'FROM dynamic_forms f '
                 . 'LEFT JOIN company c ON c.id_company=f.id_company '
                 . 'WHERE f.id_company IS NULL '
                 . 'ORDER BY f.state DESC, f.name ASC';
            return $pdo->query($sql)->fetchAll();
        }
        $stmt = $pdo->prepare(
            'SELECT f.*, c.razon_social AS company_name, '
            . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count, '
            . '       '.$submissionExpr.' AS submission_count '
            . 'FROM dynamic_forms f '
            . 'LEFT JOIN company c ON c.id_company=f.id_company '
            . 'WHERE f.id_company=:id_company OR f.id_company IS NULL '
            . 'ORDER BY (f.id_company IS NULL) DESC, f.state DESC, f.name ASC'
        );
        $stmt->execute(['id_company'=>$idCompany]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->prepare(
        'SELECT f.*, c.razon_social AS company_name, '
        . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count, '
        . '       '.$submissionExpr.' AS submission_count '
        . 'FROM dynamic_forms f '
        . 'LEFT JOIN company c ON c.id_company=f.id_company '
        . 'WHERE f.id_company=:id_company OR f.id_company IS NULL '
        . 'ORDER BY (f.id_company IS NULL) DESC, f.state DESC, f.name ASC'
    );
    $stmt->execute(['id_company'=>$idCompany]);
    return $stmt->fetchAll();
}

function formularioCrear(PDO $pdo, ?int $idCompany, string $name, ?string $description, string $createdBy): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO dynamic_forms (id_company,name,description,state,created_by,date_create,last_update) '
        . 'VALUES (:id_company,:name,:description,1,:created_by,NOW(),NOW())'
    );
    $stmt->execute([
        'id_company'=>$idCompany,
        'name'=>$name,
        'description'=>$description,
        'created_by'=>$createdBy,
    ]);
    return (int)$pdo->lastInsertId();
}

function formularioEditar(PDO $pdo, int $idForm, string $name, ?string $description): void
{
    $stmt = $pdo->prepare(
        'UPDATE dynamic_forms SET name=:name, description=:description, last_update=NOW() WHERE id_form=:id_form'
    );
    $stmt->execute(['name'=>$name,'description'=>$description,'id_form'=>$idForm]);
}

function formularioCambiarEstado(PDO $pdo, int $idForm, int $state): void
{
    $stmt = $pdo->prepare('UPDATE dynamic_forms SET state=:state,last_update=NOW() WHERE id_form=:id_form');
    $stmt->execute(['state'=>$state,'id_form'=>$idForm]);
}

function formularioTieneEnvios(PDO $pdo, int $idForm): bool
{
    formularioRequireSchema($pdo);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM dynamic_form_submissions WHERE id_form=:id_form');
    $stmt->execute(['id_form'=>$idForm]);
    return (int)$stmt->fetchColumn() > 0;
}

function formularioCampos(PDO $pdo, int $idForm): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM dynamic_form_fields WHERE id_form=:id_form ORDER BY sort_order ASC,id_field ASC'
    );
    $stmt->execute(['id_form'=>$idForm]);
    return $stmt->fetchAll();
}

function formularioCampoObtener(PDO $pdo, int $idField): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM dynamic_form_fields WHERE id_field=:id_field LIMIT 1');
    $stmt->execute(['id_field'=>$idField]);
    $row=$stmt->fetch();
    return $row ?: null;
}

function formularioCampoCrear(PDO $pdo, int $idForm, string $label, string $fieldType, ?string $options, int $required, int $sortOrder): int
{
    $stmt=$pdo->prepare(
        'INSERT INTO dynamic_form_fields (id_form,label,field_type,options,is_required,sort_order) '
        . 'VALUES (:id_form,:label,:field_type,:options,:required,:sort_order)'
    );
    $stmt->execute([
        'id_form'=>$idForm,'label'=>$label,'field_type'=>$fieldType,'options'=>$options,
        'required'=>$required,'sort_order'=>$sortOrder,
    ]);
    return (int)$pdo->lastInsertId();
}

function formularioCampoEditar(PDO $pdo, int $idField, string $label, string $fieldType, ?string $options, int $required, int $sortOrder): void
{
    $stmt=$pdo->prepare(
        'UPDATE dynamic_form_fields '
        . 'SET label=:label,field_type=:field_type,options=:options,is_required=:required,sort_order=:sort_order '
        . 'WHERE id_field=:id_field'
    );
    $stmt->execute([
        'label'=>$label,'field_type'=>$fieldType,'options'=>$options,'required'=>$required,
        'sort_order'=>$sortOrder,'id_field'=>$idField,
    ]);
}

function formularioCampoEliminar(PDO $pdo, int $idField): void
{
    $stmt=$pdo->prepare('DELETE FROM dynamic_form_fields WHERE id_field=:id_field');
    $stmt->execute(['id_field'=>$idField]);
}

function formularioDisponiblesUsuario(PDO $pdo, int $idCompany): array
{
    $stmt=$pdo->prepare(
        'SELECT f.*, c.razon_social AS company_name, '
        . '       (SELECT COUNT(*) FROM dynamic_form_fields ff WHERE ff.id_form=f.id_form) AS field_count '
        . 'FROM dynamic_forms f '
        . 'LEFT JOIN company c ON c.id_company=f.id_company '
        . 'WHERE f.state=1 AND (f.id_company=:id_company OR f.id_company IS NULL) '
        . 'ORDER BY (f.id_company IS NULL) DESC,f.name ASC'
    );
    $stmt->execute(['id_company'=>$idCompany]);
    return $stmt->fetchAll();
}

function formularioHistorialUsuario(PDO $pdo, string $idUsers): array
{
    formularioRequireSchema($pdo);
    $stmt=$pdo->prepare(
        'SELECT s.id_submission,s.id_form,s.id_company,s.id_users,s.status,s.submitted_at, '
        . '       f.name AS form_name,f.description AS form_description, '
        . '       c.razon_social AS company_name '
        . 'FROM dynamic_form_submissions s '
        . 'INNER JOIN dynamic_forms f ON f.id_form=s.id_form '
        . 'INNER JOIN company c ON c.id_company=s.id_company '
        . 'WHERE s.id_users=:id_users '
        . 'ORDER BY s.submitted_at DESC,s.id_submission DESC'
    );
    $stmt->execute(['id_users'=>$idUsers]);
    return $stmt->fetchAll();
}

function formularioEnviosGestion(PDO $pdo, int $idForm, ?int $restrictCompany): array
{
    formularioRequireSchema($pdo);
    $sql='SELECT s.id_submission,s.id_form,s.id_company,s.id_users,s.status,s.submitted_at, '
       . '       u.name,u.lastname,c.razon_social AS company_name '
       . 'FROM dynamic_form_submissions s '
       . 'INNER JOIN users u ON u.id_users=s.id_users '
       . 'INNER JOIN company c ON c.id_company=s.id_company '
       . 'WHERE s.id_form=:id_form';
    $params=['id_form'=>$idForm];
    if ($restrictCompany !== null) {
        $sql.=' AND s.id_company=:id_company';
        $params['id_company']=$restrictCompany;
    }
    $sql.=' ORDER BY s.submitted_at DESC,s.id_submission DESC';
    $stmt=$pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function formularioEnvioObtener(PDO $pdo, int $idSubmission): ?array
{
    formularioRequireSchema($pdo);
    $stmt=$pdo->prepare(
        'SELECT s.*,f.name AS form_name,f.description AS form_description,f.id_company AS form_company, '
        . '       u.name,u.lastname,c.razon_social AS company_name '
        . 'FROM dynamic_form_submissions s '
        . 'INNER JOIN dynamic_forms f ON f.id_form=s.id_form '
        . 'INNER JOIN users u ON u.id_users=s.id_users '
        . 'INNER JOIN company c ON c.id_company=s.id_company '
        . 'WHERE s.id_submission=:id_submission LIMIT 1'
    );
    $stmt->execute(['id_submission'=>$idSubmission]);
    $row=$stmt->fetch();
    return $row ?: null;
}

function formularioRespuestasEnvio(PDO $pdo, int $idSubmission): array
{
    formularioRequireSchema($pdo);
    $stmt=$pdo->prepare(
        'SELECT a.*,ff.label,ff.field_type,ff.options,ff.is_required,ff.sort_order '
        . 'FROM dynamic_form_answers a '
        . 'INNER JOIN dynamic_form_fields ff ON ff.id_field=a.id_field '
        . 'WHERE a.id_submission=:id_submission '
        . 'ORDER BY ff.sort_order ASC,ff.id_field ASC'
    );
    $stmt->execute(['id_submission'=>$idSubmission]);
    return $stmt->fetchAll();
}

/**
 * Indica si la estructura de un formulario ya forma parte de una asignación
 * operativa de Protocolos MINSAL. Mientras exista una asignación que dependa
 * de este formulario, su definición debe permanecer inmutable para conservar
 * trazabilidad, incluso antes del primer envío.
 */
function formularioTieneUsoProtocolos(PDO $pdo, int $idForm): bool
{
    try {
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.TABLES "
            . "WHERE TABLE_SCHEMA=DATABASE() "
            . "AND TABLE_NAME IN ('protocol_forms','protocol_assignments')"
        );
        if ((int) $stmt->fetchColumn() !== 2) {
            return false;
        }

        $check = $pdo->prepare(
            'SELECT COUNT(*) '
            . 'FROM protocol_forms pf '
            . 'INNER JOIN protocol_assignments pa '
            . '  ON pa.id_protocol=pf.id_protocol '
            . ' AND (pf.id_company IS NULL OR pf.id_company=pa.id_company) '
            . 'WHERE pf.id_form=:id_form'
        );
        $check->execute(['id_form' => $idForm]);
        return (int) $check->fetchColumn() > 0;
    } catch (Throwable $e) {
        error_log('FormularioRepository formularioTieneUsoProtocolos: ' . $e->getMessage());
        return false;
    }
}

