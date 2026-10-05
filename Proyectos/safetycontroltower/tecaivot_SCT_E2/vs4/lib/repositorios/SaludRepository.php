<?php
require_once __DIR__ . '/../health_crypto.php';

const HEALTH_DECLARATION_VERSION = 'v1.0';
const HEALTH_FORM_ROLES = ['trabajador'];

function healthSchemaAvailable(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) return $ready;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'worker_health_profile'");
        return $ready = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('healthSchemaAvailable: ' . $e->getMessage());
        return $ready = false;
    }
}

function healthUserRequiresForm(PDO $pdo, string $idUsers): bool
{
    if (!healthSchemaAvailable($pdo)) return false; // despliegue código→migración sin bloqueo accidental
    $stmt = $pdo->prepare('SELECT hp.id_health_profile
        FROM worker_health_profile hp
        INNER JOIN worker_health_declaration hd ON hd.id_health_profile = hp.id_health_profile
        WHERE hp.id_users = :id_users AND hp.is_current = 1 AND hd.declaration_version = :version
        ORDER BY hd.accepted_at DESC LIMIT 1');
    $stmt->execute(['id_users' => $idUsers, 'version' => HEALTH_DECLARATION_VERSION]);
    return !$stmt->fetchColumn();
}

function healthCurrentProfile(PDO $pdo, string $idUsers): ?array
{
    if (!healthSchemaAvailable($pdo)) return null;
    $stmt = $pdo->prepare('SELECT hp.*, hd.accepted_at, hd.declaration_version, hd.declaration_hash
        FROM worker_health_profile hp
        LEFT JOIN worker_health_declaration hd ON hd.id_health_profile = hp.id_health_profile
        WHERE hp.id_users = :id_users AND hp.is_current = 1
        ORDER BY hp.id_health_profile DESC, hd.id_health_declaration DESC LIMIT 1');
    $stmt->execute(['id_users' => $idUsers]);
    $row = $stmt->fetch();
    if (!$row) return null;
    foreach (['condition_other_enc','medications_text_enc','medications_emergency_enc','allergy_details_enc','severe_reaction_info_enc','occupational_other_enc','restriction_details_enc'] as $field) {
        // Si existe información cifrada pero la clave no está configurada o no
        // permite descifrarla, no degradar a NULL silenciosamente: el endpoint
        // debe fallar de forma segura sin exponer ni perder datos sensibles.
        $row[$field] = healthDecrypt($row[$field] ?? null);
    }
    foreach (['conditions_json','allergies_json','occupational_diseases_json'] as $field) {
        $value = json_decode((string)($row[$field] ?? '[]'), true);
        $row[$field] = is_array($value) ? $value : [];
    }
    return $row;
}

function healthWorkerPhone(PDO $pdo, ?int $idWorker): ?string
{
    if (!$idWorker) return null;
    $stmt = $pdo->prepare('SELECT phone FROM workers WHERE id_worker = :id_worker LIMIT 1');
    $stmt->execute(['id_worker' => $idWorker]);
    $phone = $stmt->fetchColumn();
    return $phone !== false ? (string)$phone : null;
}

function healthValidPhone(string $value): bool
{
    return (bool)preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/', trim($value));
}

function healthValidatePayload(array $in): array
{
    $errors = [];
    $clean = [];
    $phone = trim((string)($in['phone'] ?? ''));
    if ($phone === '' || !healthValidPhone($phone)) $errors[] = 'phone';
    $clean['phone'] = $phone;

    $healthSystems = ['fonasa','isapre','other','prefer_not'];
    $clean['health_system'] = in_array(($in['health_system'] ?? ''), $healthSystems, true) ? $in['health_system'] : '';
    if ($clean['health_system'] === '') $errors[] = 'health_system';

    $relations = ['partner','parent','child','sibling','family','friend','other'];
    foreach ([1,2] as $i) {
        $name = trim((string)($in['emergency_name_'.$i] ?? ''));
        $rel = (string)($in['emergency_relation_'.$i] ?? '');
        $tel = trim((string)($in['emergency_phone_'.$i] ?? ''));
        $clean['emergency_name_'.$i] = sctTextSubstr($name, 0, 150);
        $clean['emergency_relation_'.$i] = in_array($rel, $relations, true) ? $rel : '';
        $clean['emergency_phone_'.$i] = sctTextSubstr($tel, 0, 30);
        if ($i === 1) {
            if ($name === '' || $clean['emergency_relation_'.$i] === '' || $tel === '' || !healthValidPhone($tel)) $errors[] = 'emergency_1';
        } elseif (($name !== '' || $rel !== '' || $tel !== '') && ($name === '' || $clean['emergency_relation_'.$i] === '' || $tel === '' || !healthValidPhone($tel))) {
            $errors[] = 'emergency_2';
        }
    }

    $conditionAllowed = ['asthma','diabetes','epilepsy','hypertension','cardiac','severe_allergy','musculoskeletal','other','none','prefer_not'];
    $conditions = array_values(array_unique(array_filter((array)($in['conditions'] ?? []), function($v) use ($conditionAllowed) { return in_array($v, $conditionAllowed, true); })));
    if (!$conditions) $errors[] = 'conditions';
    if ((in_array('none',$conditions,true) || in_array('prefer_not',$conditions,true)) && count($conditions) > 1) $errors[] = 'conditions_exclusive';
    $clean['conditions'] = $conditions;
    $clean['condition_other'] = sctTextSubstr(trim((string)($in['condition_other'] ?? '')),0,255);
    if (in_array('other',$conditions,true) && $clean['condition_other'] === '') $errors[] = 'condition_other';

    $tri = ['yes','no','prefer_not'];
    $clean['medication_choice'] = in_array(($in['medication_choice'] ?? ''), $tri, true) ? $in['medication_choice'] : '';
    if ($clean['medication_choice'] === '') $errors[] = 'medication_choice';
    $clean['medications_text'] = sctTextSubstr(trim((string)($in['medications_text'] ?? '')),0,1000);
    $clean['medications_emergency'] = sctTextSubstr(trim((string)($in['medications_emergency'] ?? '')),0,1500);
    if ($clean['medication_choice'] === 'yes' && $clean['medications_text'] === '') $errors[] = 'medications_text';

    $allergyAllowed = ['medicines','food','insects','latex','other','none','prefer_not'];
    $allergies = array_values(array_unique(array_filter((array)($in['allergies'] ?? []), function($v) use ($allergyAllowed) { return in_array($v, $allergyAllowed, true); })));
    if (!$allergies) $errors[] = 'allergies';
    if ((in_array('none',$allergies,true) || in_array('prefer_not',$allergies,true)) && count($allergies) > 1) $errors[] = 'allergies_exclusive';
    $clean['allergies'] = $allergies;
    $hasAllergy = (bool)array_diff($allergies, ['none','prefer_not']);
    $clean['allergy_details'] = sctTextSubstr(trim((string)($in['allergy_details'] ?? '')),0,1000);
    if ($hasAllergy && $clean['allergy_details'] === '') $errors[] = 'allergy_details';
    $severeAllowed = ['yes','no','dont_know'];
    $clean['severe_reaction'] = $hasAllergy && in_array(($in['severe_reaction'] ?? ''), $severeAllowed, true) ? $in['severe_reaction'] : null;
    if ($hasAllergy && $clean['severe_reaction'] === null) $errors[] = 'severe_reaction';
    $clean['severe_reaction_info'] = sctTextSubstr(trim((string)($in['severe_reaction_info'] ?? '')),0,1500);

    $occAllowed = ['yes','no','under_evaluation','prefer_not'];
    $clean['occupational_choice'] = in_array(($in['occupational_choice'] ?? ''), $occAllowed, true) ? $in['occupational_choice'] : '';
    if ($clean['occupational_choice'] === '') $errors[] = 'occupational_choice';
    $diseaseAllowed = ['hearing','silicosis','musculoskeletal','dermatitis','chemical','uv','altitude','mental','other'];
    $diseases = array_values(array_unique(array_filter((array)($in['occupational_diseases'] ?? []), function($v) use ($diseaseAllowed) { return in_array($v, $diseaseAllowed, true); })));
    $clean['occupational_diseases'] = $clean['occupational_choice'] === 'yes' ? $diseases : [];
    if ($clean['occupational_choice'] === 'yes' && !$diseases) $errors[] = 'occupational_diseases';
    $clean['occupational_other'] = sctTextSubstr(trim((string)($in['occupational_other'] ?? '')),0,1000);
    if (in_array('other',$diseases,true) && $clean['occupational_other'] === '') $errors[] = 'occupational_other';

    $clean['restriction_choice'] = in_array(($in['restriction_choice'] ?? ''), $tri, true) ? $in['restriction_choice'] : '';
    if ($clean['restriction_choice'] === '') $errors[] = 'restriction_choice';
    $clean['restriction_details'] = sctTextSubstr(trim((string)($in['restriction_details'] ?? '')),0,1500);
    if ($clean['restriction_choice'] === 'yes' && $clean['restriction_details'] === '') $errors[] = 'restriction_details';

    $clean['accepted'] = !empty($in['accepted']);
    if (!$clean['accepted']) $errors[] = 'accepted';
    return ['valid' => !$errors, 'errors' => array_values(array_unique($errors)), 'data' => $clean];
}

function healthSaveProfile(PDO $pdo, array $profile, array $data, string $declarationText, ?string $ip): int
{
    healthEncryptionKey(); // fail safe before starting transaction
    $idUsers = (string)$profile['id_users'];
    $idCompany = (int)$profile['id_company'];
    $idWorker = !empty($profile['id_worker']) ? (int)$profile['id_worker'] : null;
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE worker_health_profile SET is_current = 0, last_update = NOW() WHERE id_users = :id_users AND is_current = 1')->execute(['id_users'=>$idUsers]);
        $stmt = $pdo->prepare('INSERT INTO worker_health_profile
            (id_company,id_users,id_worker,is_current,phone,health_system,emergency_name_1,emergency_relation_1,emergency_phone_1,emergency_name_2,emergency_relation_2,emergency_phone_2,conditions_json,condition_other_enc,medication_choice,medications_text_enc,medications_emergency_enc,allergies_json,allergy_details_enc,severe_reaction,severe_reaction_info_enc,occupational_choice,occupational_diseases_json,occupational_other_enc,restriction_choice,restriction_details_enc,created_by,date_create,last_update)
            VALUES
            (:id_company,:id_users,:id_worker,1,:phone,:health_system,:e1n,:e1r,:e1p,:e2n,:e2r,:e2p,:conditions,:condition_other,:med_choice,:med_text,:med_info,:allergies,:allergy_details,:severe_reaction,:severe_info,:occ_choice,:occ_diseases,:occ_other,:restriction_choice,:restriction_details,:created_by,NOW(),NOW())');
        $stmt->execute([
            'id_company'=>$idCompany,'id_users'=>$idUsers,'id_worker'=>$idWorker,'phone'=>$data['phone'],'health_system'=>$data['health_system'],
            'e1n'=>$data['emergency_name_1'],'e1r'=>$data['emergency_relation_1'],'e1p'=>$data['emergency_phone_1'],
            'e2n'=>$data['emergency_name_2'] ?: null,'e2r'=>$data['emergency_relation_2'] ?: null,'e2p'=>$data['emergency_phone_2'] ?: null,
            'conditions'=>json_encode($data['conditions'],JSON_UNESCAPED_UNICODE),'condition_other'=>healthEncrypt($data['condition_other']),
            'med_choice'=>$data['medication_choice'],'med_text'=>healthEncrypt($data['medications_text']),'med_info'=>healthEncrypt($data['medications_emergency']),
            'allergies'=>json_encode($data['allergies'],JSON_UNESCAPED_UNICODE),'allergy_details'=>healthEncrypt($data['allergy_details']),
            'severe_reaction'=>$data['severe_reaction'],'severe_info'=>healthEncrypt($data['severe_reaction_info']),
            'occ_choice'=>$data['occupational_choice'],'occ_diseases'=>json_encode($data['occupational_diseases'],JSON_UNESCAPED_UNICODE),'occ_other'=>healthEncrypt($data['occupational_other']),
            'restriction_choice'=>$data['restriction_choice'],'restriction_details'=>healthEncrypt($data['restriction_details']),'created_by'=>$idUsers,
        ]);
        $idHealth = (int)$pdo->lastInsertId();
        $stmtDecl = $pdo->prepare('INSERT INTO worker_health_declaration
            (id_company,id_users,id_health_profile,accepted_at,declaration_version,declaration_hash,ip_address,created_by,date_create,last_update)
            VALUES (:id_company,:id_users,:id_health_profile,NOW(),:version,:hash,:ip,:created_by,NOW(),NOW())');
        $stmtDecl->execute(['id_company'=>$idCompany,'id_users'=>$idUsers,'id_health_profile'=>$idHealth,'version'=>HEALTH_DECLARATION_VERSION,'hash'=>hash('sha256',$declarationText),'ip'=>$ip,'created_by'=>$idUsers]);
        if ($idWorker) {
            $pdo->prepare('UPDATE workers SET phone = :phone, last_update = NOW() WHERE id_worker = :id_worker AND id_company = :id_company')->execute(['phone'=>$data['phone'],'id_worker'=>$idWorker,'id_company'=>$idCompany]);
        }
        $pdo->commit();
        return $idHealth;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
