<?php
final class SctOnboardingRepository
{
    private PDO $pdo;
    public function __construct(PDO $pdo){$this->pdo=$pdo;}

    public function schemaAvailable(): bool
    {
        static $ready=null;
        if($ready!==null)return $ready;
        try{$s=$this->pdo->query("SHOW TABLES LIKE 'user_onboarding'");return $ready=(bool)$s->fetchColumn();}
        catch(Throwable $e){error_log('Onboarding schema: '.$e->getMessage());return $ready=false;}
    }

    public function ensureState(string $id): void
    {
        $s=$this->pdo->prepare('INSERT INTO user_onboarding(id_users,date_create,last_update) VALUES(:id,NOW(),NOW()) ON DUPLICATE KEY UPDATE last_update=last_update');
        $s->execute(['id'=>$id]);
    }

    public function state(string $id): ?array
    {
        if(!$this->schemaAvailable())return null;
        $this->ensureState($id);
        $s=$this->pdo->prepare('SELECT * FROM user_onboarding WHERE id_users=:id LIMIT 1');
        $s->execute(['id'=>$id]);$r=$s->fetch();return $r?:null;
    }

    public function hasExtendedProfile(string $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT 1
                 FROM users u
                 INNER JOIN user_profile_details d
                    ON d.id_users = u.id_users
                 WHERE u.id_users = :id_users
                   AND u.mutual_code IN (\'achs\', \'isl\', \'ist\', \'mutual\')
                   AND d.emergency_contact_name <> \'\'
                   AND d.emergency_contact_phone <> \'\'
                   AND d.emergency_contact_relation <> \'\'
                 LIMIT 1'
            );
            $stmt->execute(['id_users' => $userId]);

            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function hasConsent(string $id,string $version): bool
    {
        $s=$this->pdo->prepare('SELECT 1 FROM user_data_consent WHERE id_users=:id AND policy_version=:v ORDER BY accepted_at DESC LIMIT 1');
        $s->execute(['id'=>$id,'v'=>$version]);return (bool)$s->fetchColumn();
    }

    public function profile(string $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                u.id_users,
                u.id_company,
                u.id_worker,
                u.name,
                u.lastname,
                u.rut,
                u.language,
                u.state,
                u.mutual_code,
                c.razon_social,
                w.phone,
                w.position,
                d.confirmed_project_id,
                d.hired_by_contractor,
                d.id_contractor_company,
                cc.rut AS contractor_rut,
                cc.business_name AS contractor_business_name,
                cc.trade_name AS contractor_trade_name,
                d.years_experience_current_role,
                d.emergency_contact_name,
                d.emergency_contact_phone,
                d.emergency_contact_relation,
                d.confirmed_at
             FROM users u
             INNER JOIN company c
                ON c.id_company = u.id_company
             LEFT JOIN workers w
                ON w.id_worker = u.id_worker
               AND w.id_company = u.id_company
             LEFT JOIN user_profile_details d
                ON d.id_users = u.id_users
             LEFT JOIN contractor_companies cc
                ON cc.id_contractor_company = d.id_contractor_company
               AND cc.id_company = u.id_company
             WHERE u.id_users = :id_users
               AND u.state = 1
             LIMIT 1'
        );
        $stmt->execute(['id_users' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function roles(string $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT g.name
             FROM users_role ur
             INNER JOIN users_role_group g
                ON g.id_role_group = ur.id_role_group
             WHERE ur.id_users = :id_users
               AND ur.state = 1
               AND g.state = 1
             ORDER BY g.id_role_group'
        );
        $stmt->execute(['id_users' => $userId]);

        return array_values(array_filter(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    public function availableProjects(string $userId): array
    {
        $profile = $this->profile($userId);
        if (!$profile) return [];

        $idCompany = (int)$profile['id_company'];
        $idWorker = !empty($profile['id_worker']) ? (int)$profile['id_worker'] : null;

        if ($idWorker) {
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT p.id_project, p.name
                 FROM worker_projects wp
                 INNER JOIN projects p
                    ON p.id_project = wp.id_project
                 WHERE wp.id_worker = :id_worker
                   AND p.id_company = :id_company
                   AND p.state = 1
                 ORDER BY p.name'
            );
            $stmt->execute([
                'id_worker' => $idWorker,
                'id_company' => $idCompany,
            ]);
            $assigned = $stmt->fetchAll();

            if ($assigned) {
                return $assigned;
            }
        }

        // Fallback para perfiles autenticados sin asociación worker_projects:
        // muestra proyectos activos de su propia empresa, sin modificar asignaciones.
        $stmt = $this->pdo->prepare(
            'SELECT id_project, name
             FROM projects
             WHERE id_company = :id_company
               AND state = 1
             ORDER BY name'
        );
        $stmt->execute(['id_company' => $idCompany]);

        return $stmt->fetchAll();
    }

    public function projectBelongsToUserScope(string $userId, int $projectId): bool
    {
        foreach ($this->availableProjects($userId) as $project) {
            if ((int)$project['id_project'] === $projectId) return true;
        }
        return false;
    }

    public function saveProfile(string $id,array $d): void
    {
        $s=$this->pdo->prepare('UPDATE users SET name=:name,lastname=:lastname,rut=:rut,language=:language,last_update=NOW() WHERE id_users=:id LIMIT 1');
        $s->execute(['name'=>$d['name'],'lastname'=>$d['lastname'],'rut'=>$d['rut'],'language'=>$d['language'],'id'=>$id]);
        if(!empty($d['id_worker'])){
            $s=$this->pdo->prepare('UPDATE workers SET name=:name,lastname=:lastname,rut=:rut,email=:email,phone=:phone,position=:position,last_update=NOW()
            WHERE id_worker=:wid AND id_company=:cid LIMIT 1');
            $s->execute(['name'=>$d['name'],'lastname'=>$d['lastname'],'rut'=>$d['rut'],'email'=>$id,'phone'=>$d['phone'],'position'=>$d['position'],'wid'=>(int)$d['id_worker'],'cid'=>(int)$d['id_company']]);
        }
    }

    public function addConsent(
        string $id,
        string $version,
        string $language,
        string $hash,
        string $ip,
        string $ua
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO user_data_consent
                (id_users, policy_version, policy_language, policy_hash, accepted_at, ip_address, user_agent)
             VALUES
                (:id, :version, :language, :hash, NOW(), :ip, :user_agent)'
        );
        $stmt->execute([
            'id' => $id,
            'version' => $version,
            'language' => $language,
            'hash' => $hash,
            'ip' => substr($ip, 0, 45),
            'user_agent' => substr($ua, 0, 255),
        ]);
    }

    public function setMutualCode(string $userId, string $mutualCode): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET mutual_code = :mutual_code,
                 last_update = NOW()
             WHERE id_users = :id_users
             LIMIT 1'
        );
        $stmt->execute([
            'mutual_code' => $mutualCode,
            'id_users' => $userId,
        ]);
    }

    public function searchContractors(int $idCompany, string $term = '', int $limit = 20): array
    {
        $limit = max(1, min($limit, 50));
        $term = trim($term);

        if ($term === '') {
            $stmt = $this->pdo->prepare(
                'SELECT
                    id_contractor_company,
                    rut,
                    business_name,
                    trade_name
                 FROM contractor_companies
                 WHERE id_company = :id_company
                   AND state = 1
                 ORDER BY business_name
                 LIMIT ' . $limit
            );
            $stmt->execute(['id_company' => $idCompany]);
            return $stmt->fetchAll();
        }

        $like = '%' . $term . '%';
        $normalizedRut = preg_replace('/[^0-9kK]/', '', $term);
        $rutLike = '%' . $normalizedRut . '%';

        $stmt = $this->pdo->prepare(
            'SELECT
                id_contractor_company,
                rut,
                business_name,
                trade_name
             FROM contractor_companies
             WHERE id_company = :id_company
               AND state = 1
               AND (
                    business_name LIKE :name_term
                    OR trade_name LIKE :trade_term
                    OR rut LIKE :rut_term
                    OR REPLACE(REPLACE(rut, \'.\', \'\'), \'-\', \'\') LIKE :normalized_rut_term
               )
             ORDER BY business_name
             LIMIT ' . $limit
        );
        $stmt->execute([
            'id_company' => $idCompany,
            'name_term' => $like,
            'trade_term' => $like,
            'rut_term' => $like,
            'normalized_rut_term' => $rutLike,
        ]);

        return $stmt->fetchAll();
    }

    public function contractorInCompany(int $idCompany, int $contractorId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                id_contractor_company,
                rut,
                business_name,
                trade_name
             FROM contractor_companies
             WHERE id_contractor_company = :id_contractor_company
               AND id_company = :id_company
               AND state = 1
             LIMIT 1'
        );
        $stmt->execute([
            'id_contractor_company' => $contractorId,
            'id_company' => $idCompany,
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveProfileDetails(string $userId, array $data): void
    {
        // Compatibilidad de despliegue:
        // E3-VS1 migró la mutualidad desde user_profile_details.health_system
        // hacia users.mutual_code. Algunas BD de QA pueden conservar todavía
        // la columna histórica NOT NULL. Si existe, se alimenta con el mismo
        // código canónico para evitar que el INSERT falle.
        $legacyHealthSystem = false;

        try {
            $columnCheck = $this->pdo->prepare(
                "SELECT 1
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'user_profile_details'
                   AND COLUMN_NAME = 'health_system'
                 LIMIT 1"
            );
            $columnCheck->execute();
            $legacyHealthSystem = (bool)$columnCheck->fetchColumn();
        } catch (Throwable $ignored) {
            $legacyHealthSystem = false;
        }

        $columns = [
            'id_users',
            'id_company',
            'id_worker',
            'confirmed_project_id',
            'hired_by_contractor',
            'id_contractor_company',
        ];

        $values = [
            ':id_users',
            ':id_company',
            ':id_worker',
            ':confirmed_project_id',
            ':hired_by_contractor',
            ':id_contractor_company',
        ];

        $updates = [
            'id_company = VALUES(id_company)',
            'id_worker = VALUES(id_worker)',
            'confirmed_project_id = VALUES(confirmed_project_id)',
            'hired_by_contractor = VALUES(hired_by_contractor)',
            'id_contractor_company = VALUES(id_contractor_company)',
        ];

        $params = [
            'id_users' => $userId,
            'id_company' => (int)$data['id_company'],
            'id_worker' => $data['id_worker'] ?: null,
            'confirmed_project_id' => $data['confirmed_project_id'] ?: null,
            'hired_by_contractor' => $data['hired_by_contractor'] ? 1 : 0,
            'id_contractor_company' => $data['id_contractor_company'] ?: null,
        ];

        if ($legacyHealthSystem) {
            $columns[] = 'health_system';
            $values[] = ':health_system';
            $updates[] = 'health_system = VALUES(health_system)';
            $params['health_system'] = (string)$data['mutual_code'];
        }

        $columns = array_merge(
            $columns,
            [
                'years_experience_current_role',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
                'confirmed_at',
                'created_by',
                'date_create',
                'last_update',
            ]
        );

        $values = array_merge(
            $values,
            [
                ':years_experience_current_role',
                ':emergency_contact_name',
                ':emergency_contact_phone',
                ':emergency_contact_relation',
                'NOW()',
                ':created_by',
                'NOW()',
                'NOW()',
            ]
        );

        $updates = array_merge(
            $updates,
            [
                'years_experience_current_role = VALUES(years_experience_current_role)',
                'emergency_contact_name = VALUES(emergency_contact_name)',
                'emergency_contact_phone = VALUES(emergency_contact_phone)',
                'emergency_contact_relation = VALUES(emergency_contact_relation)',
                'confirmed_at = NOW()',
                'last_update = NOW()',
            ]
        );

        $params += [
            'years_experience_current_role' => (int)$data['years_experience_current_role'],
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
            'emergency_contact_relation' => $data['emergency_contact_relation'],
            'created_by' => $userId,
        ];

        $sql =
            'INSERT INTO user_profile_details (' .
            implode(', ', $columns) .
            ') VALUES (' .
            implode(', ', $values) .
            ') ON DUPLICATE KEY UPDATE ' .
            implode(', ', $updates);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function markProfile(string $id): void
    {
        $s=$this->pdo->prepare('UPDATE user_onboarding SET profile_completed_at=COALESCE(profile_completed_at,NOW()),last_update=NOW() WHERE id_users=:id');
        $s->execute(['id'=>$id]);
    }

    public function activeAttempt(string $id): ?array
    {
        $s=$this->pdo->prepare("SELECT * FROM onboarding_assessment_attempts WHERE id_users=:id AND status='in_progress' ORDER BY id_attempt DESC LIMIT 1");
        $s->execute(['id'=>$id]);$r=$s->fetch();return $r?:null;
    }

    public function createAttempt(string $id,int $n): int
    {
        $this->pdo->beginTransaction();
        try{
            $s=$this->pdo->prepare("INSERT INTO onboarding_assessment_attempts(id_users,status,started_at) VALUES(:id,'in_progress',NOW())");
            $s->execute(['id'=>$id]);$attempt=(int)$this->pdo->lastInsertId();
            $s=$this->pdo->prepare('INSERT INTO onboarding_assessment_attempt_questions(id_attempt,id_question,sort_order)
            SELECT :a,id_question,@r:=@r+1 FROM (SELECT id_question FROM onboarding_questions WHERE state=1 ORDER BY RAND() LIMIT '.$n.') q
            CROSS JOIN (SELECT @r:=0) vars');
            $s->execute(['a'=>$attempt]);
            $c=$this->pdo->prepare('SELECT COUNT(*) FROM onboarding_assessment_attempt_questions WHERE id_attempt=:a');$c->execute(['a'=>$attempt]);
            if((int)$c->fetchColumn()!==$n)throw new RuntimeException('Banco de preguntas insuficiente.');
            $this->pdo->commit();return $attempt;
        }catch(Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    public function questions(int $attempt,string $id,string $language='es'): array
    {
        $language = in_array($language,['es','en','pt','fr','zh'],true) ? $language : 'es';

        try {
            $s=$this->pdo->prepare(
                "SELECT
                    aq.id_question,
                    aq.sort_order,
                    COALESCE(qt.question_text,qes.question_text,q.question) AS question
                 FROM onboarding_assessment_attempt_questions aq
                 INNER JOIN onboarding_assessment_attempts a
                    ON a.id_attempt=aq.id_attempt
                 INNER JOIN onboarding_questions q
                    ON q.id_question=aq.id_question
                 LEFT JOIN onboarding_question_translations qt
                    ON qt.id_question=q.id_question
                   AND qt.language_code=:language
                 LEFT JOIN onboarding_question_translations qes
                    ON qes.id_question=q.id_question
                   AND qes.language_code='es'
                 WHERE aq.id_attempt=:a
                   AND a.id_users=:id
                   AND a.status='in_progress'
                 ORDER BY aq.sort_order"
            );
            $s->execute([
                'language'=>$language,
                'a'=>$attempt,
                'id'=>$id,
            ]);
            $rows=$s->fetchAll();

            $o=$this->pdo->prepare(
                "SELECT
                    o.id_option,
                    COALESCE(ot.option_text,oes.option_text,o.option_text) AS option_text
                 FROM onboarding_question_options o
                 LEFT JOIN onboarding_question_option_translations ot
                    ON ot.id_option=o.id_option
                   AND ot.language_code=:language
                 LEFT JOIN onboarding_question_option_translations oes
                    ON oes.id_option=o.id_option
                   AND oes.language_code='es'
                 WHERE o.id_question=:q
                 ORDER BY RAND()"
            );

            foreach($rows as &$row){
                $o->execute([
                    'language'=>$language,
                    'q'=>(int)$row['id_question'],
                ]);
                $row['options']=$o->fetchAll();
            }
            unset($row);

            return $rows;
        } catch (PDOException $e) {
            // Compatibilidad durante despliegue: si la migración i18n todavía
            // no existe, mantener el banco histórico en español.
            $s=$this->pdo->prepare(
                "SELECT aq.id_question,aq.sort_order,q.question
                 FROM onboarding_assessment_attempt_questions aq
                 INNER JOIN onboarding_assessment_attempts a
                    ON a.id_attempt=aq.id_attempt
                 INNER JOIN onboarding_questions q
                    ON q.id_question=aq.id_question
                 WHERE aq.id_attempt=:a
                   AND a.id_users=:id
                   AND a.status='in_progress'
                 ORDER BY aq.sort_order"
            );
            $s->execute(['a'=>$attempt,'id'=>$id]);
            $rows=$s->fetchAll();

            $o=$this->pdo->prepare(
                'SELECT id_option,option_text
                 FROM onboarding_question_options
                 WHERE id_question=:q
                 ORDER BY RAND()'
            );

            foreach($rows as &$row){
                $o->execute(['q'=>(int)$row['id_question']]);
                $row['options']=$o->fetchAll();
            }
            unset($row);

            return $rows;
        }
    }

    public function expectedQuestions(int $attempt,string $id): array
    {
        $s=$this->pdo->prepare("SELECT aq.id_question FROM onboarding_assessment_attempt_questions aq
        INNER JOIN onboarding_assessment_attempts a ON a.id_attempt=aq.id_attempt
        WHERE aq.id_attempt=:a AND a.id_users=:id AND a.status='in_progress' ORDER BY aq.sort_order");
        $s->execute(['a'=>$attempt,'id'=>$id]);return array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));
    }

    public function option(int $q,int $o): ?array
    {
        $s=$this->pdo->prepare('SELECT id_option,id_question,is_correct FROM onboarding_question_options WHERE id_question=:q AND id_option=:o LIMIT 1');
        $s->execute(['q'=>$q,'o'=>$o]);$r=$s->fetch();return $r?:null;
    }

    public function draftAnswers(int $attempt, string $id): array
    {
        try {
            $s = $this->pdo->prepare(
                "SELECT d.id_question,d.id_option
                 FROM onboarding_assessment_draft_answers d
                 INNER JOIN onboarding_assessment_attempts a
                    ON a.id_attempt=d.id_attempt
                 WHERE d.id_attempt=:a
                   AND a.id_users=:id
                   AND a.status='in_progress'
                 ORDER BY d.id_question"
            );
            $s->execute([
                'a' => $attempt,
                'id' => $id,
            ]);

            $result = [];
            foreach ($s->fetchAll() as $row) {
                $result[(int)$row['id_question']] = (int)$row['id_option'];
            }

            return $result;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function saveDraftAnswers(int $attempt, string $id, array $answers): void
    {
        $expected = $this->expectedQuestions($attempt, $id);
        if (!$expected) {
            throw new RuntimeException('Intento no disponible.');
        }

        $validQuestionIds = array_fill_keys($expected, true);

        $this->pdo->beginTransaction();

        try {
            $check = $this->pdo->prepare(
                "SELECT id_attempt
                 FROM onboarding_assessment_attempts
                 WHERE id_attempt=:a
                   AND id_users=:id
                   AND status='in_progress'
                 FOR UPDATE"
            );
            $check->execute([
                'a' => $attempt,
                'id' => $id,
            ]);

            if (!$check->fetchColumn()) {
                throw new RuntimeException('Intento no disponible.');
            }

            $upsert = $this->pdo->prepare(
                'INSERT INTO onboarding_assessment_draft_answers(
                    id_attempt,id_question,id_option,saved_at,last_update
                 ) VALUES(
                    :a,:q,:o,NOW(),NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    id_option=VALUES(id_option),
                    last_update=NOW()'
            );

            foreach ($answers as $answer) {
                $questionId = (int)($answer['id_question'] ?? 0);
                $optionId = (int)($answer['id_option'] ?? 0);

                if (
                    $questionId <= 0
                    || $optionId <= 0
                    || empty($validQuestionIds[$questionId])
                ) {
                    throw new RuntimeException('Respuesta de borrador inválida.');
                }

                $option = $this->option($questionId, $optionId);
                if (!$option) {
                    throw new RuntimeException('Alternativa de borrador inválida.');
                }

                $upsert->execute([
                    'a' => $attempt,
                    'q' => $questionId,
                    'o' => $optionId,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function clearDraftAnswers(int $attempt): void
    {
        $s = $this->pdo->prepare(
            'DELETE FROM onboarding_assessment_draft_answers
             WHERE id_attempt=:a'
        );
        $s->execute(['a' => $attempt]);
    }

    public function completeAttempt(int $attempt,string $id,array $answers): array
    {
        $this->pdo->beginTransaction();
        try{
            $s=$this->pdo->prepare("SELECT id_attempt FROM onboarding_assessment_attempts WHERE id_attempt=:a AND id_users=:id AND status='in_progress' FOR UPDATE");
            $s->execute(['a'=>$attempt,'id'=>$id]);if(!$s->fetchColumn())throw new RuntimeException('Intento no disponible.');
            $this->clearDraftAnswers($attempt);
            $correct=0;$ins=$this->pdo->prepare('INSERT INTO onboarding_assessment_answers(id_attempt,id_question,id_option,is_correct,answered_at) VALUES(:a,:q,:o,:c,NOW())');
            foreach($answers as $ans){
                $op=$this->option((int)$ans['id_question'],(int)$ans['id_option']);if(!$op)throw new RuntimeException('Alternativa inválida.');
                $c=(int)$op['is_correct']===1?1:0;$correct+=$c;
                $ins->execute(['a'=>$attempt,'q'=>(int)$ans['id_question'],'o'=>(int)$ans['id_option'],'c'=>$c]);
            }
            $total=count($answers);$score=$total?round($correct*100/$total,2):0;
            $s=$this->pdo->prepare("UPDATE onboarding_assessment_attempts SET status='completed',score=:score,completed_at=NOW() WHERE id_attempt=:a AND id_users=:id");
            $s->execute(['score'=>$score,'a'=>$attempt,'id'=>$id]);
            $s=$this->pdo->prepare('UPDATE user_onboarding SET assessment_completed_at=COALESCE(assessment_completed_at,NOW()),assessment_score=:score,
            completed_at=CASE WHEN profile_completed_at IS NOT NULL THEN COALESCE(completed_at,NOW()) ELSE completed_at END,last_update=NOW() WHERE id_users=:id');
            $s->execute(['score'=>$score,'id'=>$id]);
            $this->pdo->commit();return ['correct'=>$correct,'total'=>$total,'score'=>$score];
        }catch(Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
}
