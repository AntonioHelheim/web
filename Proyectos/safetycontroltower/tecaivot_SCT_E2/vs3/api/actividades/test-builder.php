<?php
/**
 * SCT P73 — API compartida del constructor de actividades evaluables.
 * Tipos soportados: induccion, auditoria, autoevaluacion.
 * Mantiene las tablas existentes y añade soporte opcional para multimedia
 * y orden de preguntas mediante la migración P73_activity_builder.sql.
 */
require __DIR__ . '/../induccion/common.php';
require_once __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

$action = (string) ($_GET['action'] ?? $_POST['action'] ?? '');
$type = (string) ($_GET['type'] ?? $_POST['type'] ?? '');
$allowedTypes = ['induccion', 'auditoria', 'autoevaluacion'];
if (!in_array($type, $allowedTypes, true)) {
    responderJSON(false, null, 'Tipo de actividad no válido.', 400);
}

$capabilities = [
    'induccion' => ['manage' => 'induction.manage', 'questions' => 'induction.questions', 'materials' => 'induction.materials'],
    'auditoria' => ['manage' => 'audits.manage', 'questions' => 'audits.questions', 'materials' => 'audits.manage'],
    'autoevaluacion' => ['manage' => 'self_assessments.manage', 'questions' => 'self_assessments.questions', 'materials' => 'self_assessments.manage'],
];
requireCapability($pdo, $capabilities[$type]['manage']);

function sctBuilderJsonInput()
{
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : $_POST;
}

function sctBuilderCheckCsrf(array $input)
{
    $csrf = (string) ($input['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        responderJSON(false, null, 'Tu sesión expiró o la página quedó desactualizada. Recarga e intenta nuevamente.', 403);
    }
}

function sctBuilderTableExists(PDO $pdo, $table)
{
    static $cache = [];
    $key = strtolower((string) $table);
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :name");
    $stmt->execute([':name' => $table]);
    return $cache[$key] = ((int) $stmt->fetchColumn() > 0);
}

function sctBuilderColumnExists(PDO $pdo, $table, $column)
{
    static $cache = [];
    $key = strtolower($table . '.' . $column);
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table_name AND column_name = :column_name");
    $stmt->execute([':table_name' => $table, ':column_name' => $column]);
    return $cache[$key] = ((int) $stmt->fetchColumn() > 0);
}

function sctBuilderCurrentCompany(PDO $pdo)
{
    if (function_exists('currentUserCompanyId')) return (int) currentUserCompanyId($pdo);
    $profile = function_exists('currentUserProfile') ? currentUserProfile($pdo) : [];
    return (int) ($profile['id_company'] ?? 0);
}

function sctBuilderIsGlobal(PDO $pdo)
{
    return currentUserHasCapability($pdo, 'companies.view_all') || currentUserHasCapability($pdo, 'dashboard.global');
}

function sctBuilderEnsureTest(PDO $pdo, $idTest, $type)
{
    $stmt = $pdo->prepare("SELECT * FROM company_test WHERE id_test = :id AND type = :type LIMIT 1");
    $stmt->execute([':id' => $idTest, ':type' => $type]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$test) responderJSON(false, null, 'Actividad no encontrada.', 404);
    if (!sctBuilderIsGlobal($pdo) && (int) $test['id_company'] !== sctBuilderCurrentCompany($pdo)) {
        responderJSON(false, null, 'No tienes permisos para administrar esta actividad.', 403);
    }
    return $test;
}

function sctBuilderQuestionType(PDO $pdo, array $question, array $options)
{
    if (isset($question['question_type']) && $question['question_type'] !== '') return (string) $question['question_type'];
    if (count($options) === 2) {
        $values = array_map(function ($o) { return mb_strtolower(trim((string) ($o['text_option'] ?? '')), 'UTF-8'); }, $options);
        sort($values);
        $vf = ['falso', 'verdadero'];
        sort($vf);
        if ($values === $vf) return 'true_false';
    }
    return 'multiple_choice';
}

function sctBuilderFileName($name)
{
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename((string) $name));
    $name = trim((string) $name, '._-');
    return $name !== '' ? $name : 'archivo';
}

function sctBuilderMoveUpload(array $file, $relativeDir, array $allowedMimes, $maxBytes)
{
    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No se pudo recibir uno de los archivos.');
    if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > $maxBytes) throw new RuntimeException('Uno de los archivos supera el tamaño permitido.');
    $tmp = (string) ($file['tmp_name'] ?? '');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);
    if (!in_array($mime, $allowedMimes, true)) throw new RuntimeException('Uno de los archivos tiene un formato no permitido.');
    $root = realpath(__DIR__ . '/../../');
    if ($root === false) throw new RuntimeException('No se pudo resolver el directorio de la aplicación.');
    $dir = $root . '/' . trim($relativeDir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('No se pudo preparar el directorio de carga.');
    $original = sctBuilderFileName((string) ($file['name'] ?? 'archivo'));
    $ext = pathinfo($original, PATHINFO_EXTENSION);
    $stored = bin2hex(random_bytes(10)) . ($ext !== '' ? '.' . strtolower($ext) : '');
    $absolute = $dir . '/' . $stored;
    if (!move_uploaded_file($tmp, $absolute)) throw new RuntimeException('No se pudo guardar uno de los archivos.');
    return [
        'relative_path' => trim($relativeDir, '/') . '/' . $stored,
        'original_name' => $original,
        'mime_type' => $mime,
    ];
}

try {
    if ($action === 'detail') {
        $idTest = filter_var($_GET['id_test'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest) responderJSON(false, null, 'Actividad inválida.', 400);
        $test = sctBuilderEnsureTest($pdo, $idTest, $type);
        $hasSort = sctBuilderColumnExists($pdo, 'company_test_rel_questions', 'sort_order');
        $hasType = sctBuilderColumnExists($pdo, 'questions', 'question_type');
        $sql = "SELECT r.id_rel, r.id_question, r.assigned_score" . ($hasSort ? ", r.sort_order" : "") . ", q.question, q.difficulty, q.points" . ($hasType ? ", q.question_type" : "") . "
                FROM company_test_rel_questions r
                INNER JOIN questions q ON q.id_questions = r.id_question
                WHERE r.id_test = :id
                ORDER BY " . ($hasSort ? "r.sort_order ASC, " : "") . "r.id_rel ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idTest]);
        $questions = [];
        $mediaAvailable = sctBuilderTableExists($pdo, 'question_media');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $q) {
            $opt = $pdo->prepare("SELECT id_questions_options, text_option, is_it_co FROM questions_options WHERE id_questions = :id AND state = 1 ORDER BY id_questions_options");
            $opt->execute([':id' => $q['id_question']]);
            $options = $opt->fetchAll(PDO::FETCH_ASSOC);
            $q['question_type'] = sctBuilderQuestionType($pdo, $q, $options);
            $q['options'] = $options;
            $q['media'] = [];
            if ($mediaAvailable) {
                $m = $pdo->prepare("SELECT id_question_media, media_type, file_path, original_name, mime_type, sort_order FROM question_media WHERE id_question = :id ORDER BY sort_order, id_question_media");
                $m->execute([':id' => $q['id_question']]);
                $q['media'] = $m->fetchAll(PDO::FETCH_ASSOC);
            }
            $questions[] = $q;
        }
        $mat = $pdo->prepare("SELECT id_material, id_test, title, material_type, file_path, content_text, sort_order, date_create FROM test_materials WHERE id_test = :id ORDER BY sort_order, id_material");
        $mat->execute([':id' => $idTest]);
        responderJSON(true, ['test' => $test, 'questions' => $questions, 'materials' => $mat->fetchAll(PDO::FETCH_ASSOC), 'supports_media' => $mediaAvailable, 'supports_reorder' => $hasSort]);
    }

    if ($action === 'question_bank') {
        $idTest = filter_var($_GET['id_test'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest) responderJSON(false, null, 'Actividad inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $term = trim((string) ($_GET['q'] ?? ''));
        $hasType = sctBuilderColumnExists($pdo, 'questions', 'question_type');
        $sql = "SELECT q.id_questions, q.question, q.points, q.difficulty" . ($hasType ? ", q.question_type" : "") . "
                FROM questions q
                WHERE q.state = 1
                  AND q.id_questions NOT IN (SELECT id_question FROM company_test_rel_questions WHERE id_test = :id)";
        $params = [':id' => $idTest];
        if ($term !== '') { $sql .= " AND q.question LIKE :term"; $params[':term'] = '%' . $term . '%'; }
        $sql .= " ORDER BY q.last_update DESC, q.id_questions DESC LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            if (empty($row['question_type'])) $row['question_type'] = 'multiple_choice';
        }
        responderJSON(true, $rows);
    }

    if ($action === 'groups') {
        $company = sctBuilderIsGlobal($pdo) ? (int) ($_GET['id_company'] ?? 0) : sctBuilderCurrentCompany($pdo);
        if ($company <= 0) responderJSON(true, []);
        $sql = "SELECT rg.id_role_group, rg.name, rg.description, u.id_users, u.name AS user_name, u.lastname
                FROM users_role_group rg
                INNER JOIN users_role ur ON ur.id_role_group = rg.id_role_group AND ur.state = 1
                INNER JOIN users u ON u.id_users = ur.id_users AND u.state = 1 AND u.id_company = rg.id_company
                WHERE rg.id_company = :company AND rg.state = 1
                ORDER BY rg.name, u.lastname, u.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':company' => $company]);
        $groups = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = (string) $row['id_role_group'];
            if (!isset($groups[$key])) {
                $label = trim((string) ($row['description'] ?? ''));
                if ($label === '') $label = ucfirst(str_replace('_', ' ', (string) $row['name']));
                $groups[$key] = ['id' => (int) $row['id_role_group'], 'name' => (string) $row['name'], 'label' => $label, 'users' => []];
            }
            $groups[$key]['users'][] = (string) $row['id_users'];
        }
        foreach ($groups as &$g) $g['count'] = count(array_unique($g['users']));
        responderJSON(true, array_values($groups));
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderJSON(false, null, 'Método no permitido.', 405);

    if ($action === 'material_upload') {
        sctBuilderCheckCsrf($_POST);
        requireCapability($pdo, $capabilities[$type]['materials']);
        $idTest = filter_var($_POST['id_test'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest) responderJSON(false, null, 'Actividad inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        if (empty($_FILES['files'])) responderJSON(false, null, 'Selecciona al menos un archivo.', 400);
        $files = $_FILES['files'];
        $count = is_array($files['name']) ? count($files['name']) : 1;
        $allowed = ['application/pdf','image/jpeg','image/png','image/webp','video/mp4','video/webm','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        $created = [];
        $pdo->beginTransaction();
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                'type' => is_array($files['type']) ? $files['type'][$i] : $files['type'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                'size' => is_array($files['size']) ? $files['size'][$i] : $files['size'],
            ];
            $saved = sctBuilderMoveUpload($file, 'uploads/activity-builder/materiales/' . $idTest, $allowed, 80 * 1024 * 1024);
            $mime = $saved['mime_type'];
            $materialType = strpos($mime, 'video/') === 0 ? 'video' : (strpos($mime, 'image/') === 0 ? 'otro' : 'documento');
            $order = (int) $pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM test_materials WHERE id_test = " . (int) $idTest)->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO test_materials (id_test,title,material_type,file_path,content_text,sort_order,created_by,date_create) VALUES (:test,:title,:type,:path,NULL,:sort,:by,NOW())");
            $stmt->execute([':test' => $idTest, ':title' => mb_substr($saved['original_name'], 0, 150, 'UTF-8'), ':type' => $materialType, ':path' => $saved['relative_path'], ':sort' => $order, ':by' => currentUserId()]);
            $created[] = (int) $pdo->lastInsertId();
        }
        $pdo->commit();
        responderJSON(true, ['created' => $created], count($created) . ' archivo(s) cargado(s).', 201);
    }

    $input = sctBuilderJsonInput();
    sctBuilderCheckCsrf($input);

    if ($action === 'material_delete') {
        requireCapability($pdo, $capabilities[$type]['materials']);
        $idMaterial = filter_var($input['id_material'] ?? null, FILTER_VALIDATE_INT);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        if (!$idMaterial || !$idTest) responderJSON(false, null, 'Material inválido.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $stmt = $pdo->prepare("SELECT file_path FROM test_materials WHERE id_material = :id AND id_test = :test");
        $stmt->execute([':id' => $idMaterial, ':test' => $idTest]);
        $path = $stmt->fetchColumn();
        if ($path === false) responderJSON(false, null, 'Material no encontrado.', 404);
        $pdo->prepare("DELETE FROM test_materials WHERE id_material = :id AND id_test = :test")->execute([':id' => $idMaterial, ':test' => $idTest]);
        if ($path) {
            $absolute = realpath(__DIR__ . '/../../') . '/' . ltrim((string) $path, '/');
            if (is_file($absolute)) @unlink($absolute);
        }
        responderJSON(true, null, 'Material eliminado.');
    }

    if ($action === 'question_create') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest) responderJSON(false, null, 'Actividad inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $question = trim((string) ($input['question'] ?? ''));
        $questionType = (string) ($input['question_type'] ?? 'multiple_choice');
        if (!in_array($questionType, ['multiple_choice','true_false'], true)) $questionType = 'multiple_choice';
        $difficulty = max(1, min(5, (int) ($input['difficulty'] ?? 1)));
        $points = max(1, min(100, (int) ($input['points'] ?? 10)));
        $assignedScore = max(1, min(100, (int) ($input['assigned_score'] ?? $points)));
        $options = isset($input['options']) && is_array($input['options']) ? $input['options'] : [];
        if ($question === '' || count($options) < 2) responderJSON(false, null, 'Completa la pregunta y sus alternativas.', 400);
        $correct = array_filter($options, function ($o) { return !empty($o['is_it_co']); });
        if (count($correct) !== 1) responderJSON(false, null, 'Marca una única respuesta correcta.', 400);
        $hasType = sctBuilderColumnExists($pdo, 'questions', 'question_type');
        $hasSort = sctBuilderColumnExists($pdo, 'company_test_rel_questions', 'sort_order');
        $pdo->beginTransaction();
        if ($hasType) {
            $stmt = $pdo->prepare("INSERT INTO questions (question,question_type,url_add_material,difficulty,points,state,add_expl_question,create_by,date_create,last_update) VALUES (:q,:qt,'',:d,:p,1,'',:by,NOW(),NOW())");
            $stmt->execute([':q' => $question, ':qt' => $questionType, ':d' => $difficulty, ':p' => $points, ':by' => currentUserId()]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO questions (question,url_add_material,difficulty,points,state,add_expl_question,create_by,date_create,last_update) VALUES (:q,'',:d,:p,1,'',:by,NOW(),NOW())");
            $stmt->execute([':q' => $question, ':d' => $difficulty, ':p' => $points, ':by' => currentUserId()]);
        }
        $idQuestion = (int) $pdo->lastInsertId();
        $optStmt = $pdo->prepare("INSERT INTO questions_options (id_questions,text_option,is_it_co,add_expl_opt,state,created_by,date_create,last_update) VALUES (:question,:text,:correct,'',1,:by,NOW(),NOW())");
        foreach ($options as $o) {
            $text = trim((string) ($o['text_option'] ?? ''));
            if ($text === '') { $pdo->rollBack(); responderJSON(false, null, 'Todas las alternativas requieren texto.', 400); }
            $optStmt->execute([':question' => $idQuestion, ':text' => mb_substr($text, 0, 50, 'UTF-8'), ':correct' => !empty($o['is_it_co']) ? 1 : 0, ':by' => currentUserId()]);
        }
        $order = (int) $pdo->query("SELECT COALESCE(MAX(" . ($hasSort ? 'sort_order' : 'id_rel') . "),0)+1 FROM company_test_rel_questions WHERE id_test = " . (int) $idTest)->fetchColumn();
        if ($hasSort) {
            $rel = $pdo->prepare("INSERT INTO company_test_rel_questions (id_test,id_question,assigned_score,sort_order,created_by,date_create,last_update) VALUES (:test,:question,:score,:sort,:by,NOW(),NOW())");
            $rel->execute([':test' => $idTest, ':question' => $idQuestion, ':score' => $assignedScore, ':sort' => $order, ':by' => currentUserId()]);
        } else {
            $rel = $pdo->prepare("INSERT INTO company_test_rel_questions (id_test,id_question,assigned_score,created_by,date_create,last_update) VALUES (:test,:question,:score,:by,NOW(),NOW())");
            $rel->execute([':test' => $idTest, ':question' => $idQuestion, ':score' => $assignedScore, ':by' => currentUserId()]);
        }
        $pdo->commit();
        responderJSON(true, ['id_question' => $idQuestion, 'id_rel' => (int) $pdo->lastInsertId()], 'Pregunta creada y agregada.', 201);
    }

    if ($action === 'question_update') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        $idRel = filter_var($input['id_rel'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest || !$idRel) responderJSON(false, null, 'Pregunta inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $relStmt = $pdo->prepare("SELECT id_question FROM company_test_rel_questions WHERE id_rel = :rel AND id_test = :test LIMIT 1");
        $relStmt->execute([':rel' => $idRel, ':test' => $idTest]);
        $idQuestion = (int) $relStmt->fetchColumn();
        if (!$idQuestion) responderJSON(false, null, 'La pregunta no pertenece a esta actividad.', 404);
        $question = trim((string) ($input['question'] ?? ''));
        $questionType = (string) ($input['question_type'] ?? 'multiple_choice');
        if (!in_array($questionType, ['multiple_choice','true_false'], true)) $questionType = 'multiple_choice';
        $difficulty = max(1, min(5, (int) ($input['difficulty'] ?? 1)));
        $assignedScore = max(1, min(100, (int) ($input['assigned_score'] ?? 10)));
        $options = isset($input['options']) && is_array($input['options']) ? $input['options'] : [];
        if ($question === '' || count($options) < 2) responderJSON(false, null, 'Completa la pregunta y sus alternativas.', 400);
        $correct = array_filter($options, function ($o) { return !empty($o['is_it_co']); });
        if (count($correct) !== 1) responderJSON(false, null, 'Marca una única respuesta correcta.', 400);
        $hasType = sctBuilderColumnExists($pdo, 'questions', 'question_type');
        $useStmt = $pdo->prepare("SELECT COUNT(*) FROM company_test_rel_questions WHERE id_question = :question");
        $useStmt->execute([':question' => $idQuestion]);
        $shared = ((int) $useStmt->fetchColumn() > 1);
        $pdo->beginTransaction();
        if ($shared) {
            if ($hasType) {
                $qStmt = $pdo->prepare("INSERT INTO questions (question,question_type,url_add_material,difficulty,points,state,add_expl_question,create_by,date_create,last_update) VALUES (:q,:qt,'',:d,:p,1,'',:by,NOW(),NOW())");
                $qStmt->execute([':q'=>$question, ':qt'=>$questionType, ':d'=>$difficulty, ':p'=>$assignedScore, ':by'=>currentUserId()]);
            } else {
                $qStmt = $pdo->prepare("INSERT INTO questions (question,url_add_material,difficulty,points,state,add_expl_question,create_by,date_create,last_update) VALUES (:q,'',:d,:p,1,'',:by,NOW(),NOW())");
                $qStmt->execute([':q'=>$question, ':d'=>$difficulty, ':p'=>$assignedScore, ':by'=>currentUserId()]);
            }
            $newQuestion = (int) $pdo->lastInsertId();
            if (sctBuilderTableExists($pdo, 'question_media')) {
                $copyMedia = $pdo->prepare("INSERT INTO question_media (id_question,media_type,file_path,original_name,mime_type,sort_order,created_by,date_create) SELECT :new_id,media_type,file_path,original_name,mime_type,sort_order,:by,NOW() FROM question_media WHERE id_question=:old_id");
                $copyMedia->execute([':new_id'=>$newQuestion, ':by'=>currentUserId(), ':old_id'=>$idQuestion]);
            }
            $idQuestion = $newQuestion;
            $pdo->prepare("UPDATE company_test_rel_questions SET id_question=:question, assigned_score=:score, last_update=NOW() WHERE id_rel=:rel AND id_test=:test")
                ->execute([':question'=>$idQuestion, ':score'=>$assignedScore, ':rel'=>$idRel, ':test'=>$idTest]);
        } else {
            if ($hasType) {
                $pdo->prepare("UPDATE questions SET question=:q, question_type=:qt, difficulty=:d, points=:p, last_update=NOW() WHERE id_questions=:id")
                    ->execute([':q'=>$question, ':qt'=>$questionType, ':d'=>$difficulty, ':p'=>$assignedScore, ':id'=>$idQuestion]);
            } else {
                $pdo->prepare("UPDATE questions SET question=:q, difficulty=:d, points=:p, last_update=NOW() WHERE id_questions=:id")
                    ->execute([':q'=>$question, ':d'=>$difficulty, ':p'=>$assignedScore, ':id'=>$idQuestion]);
            }
            $pdo->prepare("UPDATE company_test_rel_questions SET assigned_score=:score, last_update=NOW() WHERE id_rel=:rel AND id_test=:test")
                ->execute([':score'=>$assignedScore, ':rel'=>$idRel, ':test'=>$idTest]);
            $pdo->prepare("DELETE FROM questions_options WHERE id_questions=:id")->execute([':id'=>$idQuestion]);
        }
        if ($shared) {
            // La relación apunta a una copia: sus alternativas se crean desde cero.
        } else {
            // Alternativas antiguas eliminadas arriba.
        }
        $optStmt = $pdo->prepare("INSERT INTO questions_options (id_questions,text_option,is_it_co,add_expl_opt,state,created_by,date_create,last_update) VALUES (:question,:text,:correct,'',1,:by,NOW(),NOW())");
        foreach ($options as $o) {
            $text = trim((string) ($o['text_option'] ?? ''));
            if ($text === '') { $pdo->rollBack(); responderJSON(false, null, 'Todas las alternativas requieren texto.', 400); }
            $optStmt->execute([':question'=>$idQuestion, ':text'=>mb_substr($text,0,50,'UTF-8'), ':correct'=>!empty($o['is_it_co'])?1:0, ':by'=>currentUserId()]);
        }
        $pdo->commit();
        responderJSON(true, ['id_question'=>$idQuestion, 'id_rel'=>$idRel], 'Pregunta actualizada.');
    }

    if ($action === 'question_add_many') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        $items = isset($input['questions']) && is_array($input['questions']) ? $input['questions'] : [];
        if (!$idTest || !$items) responderJSON(false, null, 'Selecciona al menos una pregunta.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $hasSort = sctBuilderColumnExists($pdo, 'company_test_rel_questions', 'sort_order');
        $baseOrder = (int) $pdo->query("SELECT COALESCE(MAX(" . ($hasSort ? 'sort_order' : 'id_rel') . "),0) FROM company_test_rel_questions WHERE id_test = " . (int) $idTest)->fetchColumn();
        $created = 0;
        $pdo->beginTransaction();
        foreach ($items as $idx => $item) {
            $idQuestion = filter_var($item['id_question'] ?? null, FILTER_VALIDATE_INT);
            $score = max(1, min(100, (int) ($item['score'] ?? 10)));
            if (!$idQuestion) continue;
            $exists = $pdo->prepare("SELECT COUNT(*) FROM company_test_rel_questions WHERE id_test = :test AND id_question = :question");
            $exists->execute([':test' => $idTest, ':question' => $idQuestion]);
            if ((int) $exists->fetchColumn() > 0) continue;
            if ($hasSort) {
                $stmt = $pdo->prepare("INSERT INTO company_test_rel_questions (id_test,id_question,assigned_score,sort_order,created_by,date_create,last_update) VALUES (:test,:question,:score,:sort,:by,NOW(),NOW())");
                $stmt->execute([':test' => $idTest, ':question' => $idQuestion, ':score' => $score, ':sort' => $baseOrder + $idx + 1, ':by' => currentUserId()]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO company_test_rel_questions (id_test,id_question,assigned_score,created_by,date_create,last_update) VALUES (:test,:question,:score,:by,NOW(),NOW())");
                $stmt->execute([':test' => $idTest, ':question' => $idQuestion, ':score' => $score, ':by' => currentUserId()]);
            }
            $created++;
        }
        $pdo->commit();
        responderJSON(true, ['created_count' => $created], $created . ' pregunta(s) agregada(s).');
    }

    if ($action === 'question_remove') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        $idRel = filter_var($input['id_rel'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest || !$idRel) responderJSON(false, null, 'Pregunta inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $stmt = $pdo->prepare("DELETE FROM company_test_rel_questions WHERE id_rel = :rel AND id_test = :test");
        $stmt->execute([':rel' => $idRel, ':test' => $idTest]);
        responderJSON(true, null, 'Pregunta eliminada de la actividad.');
    }

    if ($action === 'question_weights_update') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        $weights = isset($input['weights']) && is_array($input['weights']) ? $input['weights'] : [];
        if (!$idTest || !$weights) responderJSON(false, null, 'Distribución de puntajes inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);

        $stmt = $pdo->prepare("SELECT id_rel FROM company_test_rel_questions WHERE id_test = :test ORDER BY id_rel");
        $stmt->execute([':test' => $idTest]);
        $expected = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $received = [];
        $total = 0;
        foreach ($weights as $row) {
            $idRel = filter_var($row['id_rel'] ?? null, FILTER_VALIDATE_INT);
            $score = filter_var($row['score'] ?? null, FILTER_VALIDATE_INT);
            if (!$idRel || $score === false || $score < 1 || $score > 100) responderJSON(false, null, 'Cada pregunta debe tener un porcentaje entre 1 y 100.', 400);
            $received[$idRel] = (int) $score;
            $total += (int) $score;
        }
        $receivedIds = array_keys($received);
        sort($expected);
        sort($receivedIds);
        if ($expected !== $receivedIds) responderJSON(false, null, 'La distribución debe incluir todas las preguntas de la actividad.', 400);
        if ($total !== 100) responderJSON(false, null, 'La suma de los porcentajes debe ser exactamente 100%.', 400);

        $update = $pdo->prepare("UPDATE company_test_rel_questions SET assigned_score = :score, last_update = NOW() WHERE id_rel = :rel AND id_test = :test");
        $pdo->beginTransaction();
        foreach ($received as $idRel => $score) $update->execute([':score' => $score, ':rel' => $idRel, ':test' => $idTest]);
        $pdo->commit();
        responderJSON(true, ['total' => 100], 'Distribución de porcentajes actualizada.');
    }

    if ($action === 'question_reorder') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        if (!sctBuilderColumnExists($pdo, 'company_test_rel_questions', 'sort_order')) responderJSON(false, null, 'Aplica la migración P73 para habilitar el reordenamiento.', 409);
        $idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
        $rels = isset($input['rel_ids']) && is_array($input['rel_ids']) ? $input['rel_ids'] : [];
        if (!$idTest || !$rels) responderJSON(false, null, 'Orden inválido.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $stmt = $pdo->prepare("UPDATE company_test_rel_questions SET sort_order = :sort, last_update = NOW() WHERE id_rel = :rel AND id_test = :test");
        $pdo->beginTransaction();
        foreach ($rels as $idx => $rel) $stmt->execute([':sort' => $idx + 1, ':rel' => (int) $rel, ':test' => $idTest]);
        $pdo->commit();
        responderJSON(true, null, 'Orden guardado.');
    }

    if ($action === 'question_media_upload') {
        requireCapability($pdo, $capabilities[$type]['questions']);
        if (!sctBuilderTableExists($pdo, 'question_media')) responderJSON(false, null, 'Aplica la migración P73 para habilitar multimedia por pregunta.', 409);
        $idTest = filter_var($_POST['id_test'] ?? null, FILTER_VALIDATE_INT);
        $idQuestion = filter_var($_POST['id_question'] ?? null, FILTER_VALIDATE_INT);
        if (!$idTest || !$idQuestion) responderJSON(false, null, 'Pregunta inválida.', 400);
        sctBuilderEnsureTest($pdo, $idTest, $type);
        $belongs = $pdo->prepare("SELECT COUNT(*) FROM company_test_rel_questions WHERE id_test = :test AND id_question = :question");
        $belongs->execute([':test' => $idTest, ':question' => $idQuestion]);
        if (!(int) $belongs->fetchColumn()) responderJSON(false, null, 'La pregunta no pertenece a esta actividad.', 400);
        if (empty($_FILES['files'])) responderJSON(false, null, 'Selecciona al menos un archivo.', 400);
        $files = $_FILES['files'];
        $count = is_array($files['name']) ? count($files['name']) : 1;
        $allowed = ['image/jpeg','image/png','image/webp','video/mp4','video/webm'];
        $pdo->beginTransaction();
        $created = [];
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                'type' => is_array($files['type']) ? $files['type'][$i] : $files['type'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                'size' => is_array($files['size']) ? $files['size'][$i] : $files['size'],
            ];
            $saved = sctBuilderMoveUpload($file, 'uploads/activity-builder/preguntas/' . $idQuestion, $allowed, 40 * 1024 * 1024);
            $mediaType = strpos($saved['mime_type'], 'video/') === 0 ? 'video' : 'image';
            $order = (int) $pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM question_media WHERE id_question = " . (int) $idQuestion)->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO question_media (id_question,media_type,file_path,original_name,mime_type,sort_order,created_by,date_create) VALUES (:question,:type,:path,:name,:mime,:sort,:by,NOW())");
            $stmt->execute([':question' => $idQuestion, ':type' => $mediaType, ':path' => $saved['relative_path'], ':name' => $saved['original_name'], ':mime' => $saved['mime_type'], ':sort' => $order, ':by' => currentUserId()]);
            $created[] = (int) $pdo->lastInsertId();
        }
        $pdo->commit();
        responderJSON(true, ['created' => $created], count($created) . ' archivo(s) multimedia agregado(s).', 201);
    }

    responderJSON(false, null, 'Acción no soportada.', 404);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('api/actividades/test-builder.php [' . $action . ']: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo completar la operación del constructor.', 500);
}
