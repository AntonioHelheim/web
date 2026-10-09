<?php

require_once __DIR__ . '/../Config/ProfileOptions.php';
require_once __DIR__ . '/../Config/MutualityOptions.php';
require_once __DIR__ . '/../Health/HealthRepository.php';

final class SctOnboardingDomainException extends RuntimeException
{
}

final class SctOnboardingService
{
    public const CONSENT_VERSION = 'SCT-UNIFIED-CONSENT-v2.1';
    public const QUESTION_COUNT = 15;

    private PDO $pdo;
    private SctOnboardingRepository $repository;
    private SctAuthorizationService $authorization;

    public function __construct(PDO $pdo, SctAuthorizationService $authorization)
    {
        $this->pdo = $pdo;
        $this->repository = new SctOnboardingRepository($pdo);
        $this->authorization = $authorization;
    }

    public function schemaAvailable(): bool
    {
        return $this->repository->schemaAvailable();
    }

    public function isExempt(string $userId): bool
    {
        return in_array('superusuario', $this->canonicalRoles($userId), true);
    }

    public function requiresHealthProfile(string $userId): bool
    {
        return in_array('usuario', $this->canonicalRoles($userId), true);
    }

    public function status(string $userId): array
    {
        if (!$this->schemaAvailable() || $this->isExempt($userId)) {
            return [
                'exempt' => true,
                'profile_complete' => true,
                'assessment_complete' => true,
                'complete' => true,
                'next_step' => null,
            ];
        }

        $state = $this->repository->state($userId) ?: [];

        $healthComplete = true;
        if ($this->requiresHealthProfile($userId)) {
            $healthComplete = healthSchemaAvailable($this->pdo)
                && !healthUserRequiresForm($this->pdo, $userId);
        }

        $profileComplete = !empty($state['profile_completed_at'])
            && $this->repository->hasConsent($userId, self::CONSENT_VERSION)
            && $this->repository->hasExtendedProfile($userId)
            && $healthComplete;

        $assessmentComplete = !empty($state['assessment_completed_at']);

        return [
            'exempt' => false,
            'profile_complete' => $profileComplete,
            'assessment_complete' => $assessmentComplete,
            'assessment_score' => $state['assessment_score'] ?? null,
            'complete' => $profileComplete && $assessmentComplete,
            'next_step' => !$profileComplete
                ? 'profile'
                : (!$assessmentComplete ? 'assessment' : null),
        ];
    }

    public function nextRedirect(string $userId): string
    {
        $status = $this->status($userId);

        return !empty($status['complete'])
            ? 'bienvenida.php'
            : 'onboarding.php?step=' . rawurlencode((string)$status['next_step']);
    }

    public function profile(string $userId): ?array
    {
        return $this->repository->profile($userId);
    }

    public function profilePresentation(string $userId): array
    {
        $roles = $this->repository->roles($userId);
        $roleLabels = [];

        foreach ($roles as $role) {
            $canonical = SctRolePolicy::canonical($role);
            $roleLabels[] = SctRolePolicy::label($canonical);
        }

        return [
            'roles' => $roles,
            'role_labels' => array_values(array_unique($roleLabels)),
            'projects' => $this->repository->availableProjects($userId),
            'requires_health_profile' => $this->requiresHealthProfile($userId),
        ];
    }

    public function searchContractors(string $userId, string $term = '', int $limit = 20): array
    {
        $profile = $this->repository->profile($userId);

        if (!$profile) {
            throw new SctOnboardingDomainException('onboarding_error_profile_not_found');
        }

        return $this->repository->searchContractors(
            (int)$profile['id_company'],
            $term,
            $limit
        );
    }

    public function saveProfile(
        string $userId,
        array $input,
        string $ip,
        string $userAgent,
        string $consentText,
        string $consentLanguage,
        string $healthDeclarationText
    ): array {
        if ($this->isExempt($userId)) {
            return ['redirect' => 'bienvenida.php'];
        }

        $profile = $this->repository->profile($userId);
        if (!$profile) {
            throw new SctOnboardingDomainException('onboarding_error_profile_not_found');
        }

        $name = trim((string)($input['name'] ?? ''));
        $lastname = trim((string)($input['lastname'] ?? ''));
        $rut = strtoupper(trim((string)($input['rut'] ?? '')));
        $rawLanguage = strtolower(trim((string)($input['language'] ?? '')));
        $phone = trim((string)($input['phone'] ?? ''));
        $position = trim((string)($input['position'] ?? ''));

        $projectSelection = trim((string)($input['confirmed_project_id'] ?? ''));
        $isIndefiniteContract = $projectSelection === 'indefinite';

        $projectId = $isIndefiniteContract
            ? null
            : filter_var(
                $projectSelection !== '' ? $projectSelection : null,
                FILTER_VALIDATE_INT
            );

        $contractorFlag = strtolower(trim((string)($input['hired_by_contractor'] ?? '')));
        $contractorId = filter_var(
            $input['id_contractor_company'] ?? null,
            FILTER_VALIDATE_INT
        );

        $mutualCode = strtolower(trim((string)($input['mutual_code'] ?? '')));

        $yearsExperience = filter_var(
            $input['years_experience_current_role'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => 80]]
        );

        $emergencyName = trim((string)($input['emergency_contact_name'] ?? ''));
        $emergencyPhone = trim((string)($input['emergency_contact_phone'] ?? ''));
        $emergencyRelation = strtolower(trim((string)($input['emergency_contact_relation'] ?? '')));

        if ($name === '' || $lastname === '' || $rut === '' || empty($input['accepted'])) {
            throw new SctOnboardingDomainException('onboarding_error_required');
        }

        if (!preg_match('/^[0-9]{7,8}-[0-9K]$/i', $rut)) {
            throw new SctOnboardingDomainException('onboarding_error_invalid_rut');
        }

        $allowedLanguages = defined('IDIOMAS_DISPONIBLES')
            ? IDIOMAS_DISPONIBLES
            : ['es', 'en', 'pt', 'fr', 'zh'];

        if (!in_array($rawLanguage, $allowedLanguages, true)) {
            throw new SctOnboardingDomainException('onboarding_error_invalid_language');
        }

        $availableProjects = $this->repository->availableProjects($userId);

        if (!$isIndefiniteContract) {
            if (
                !$projectId
                || !$this->repository->projectBelongsToUserScope(
                    $userId,
                    (int)$projectId
                )
            ) {
                throw new SctOnboardingDomainException(
                    'onboarding_error_invalid_project'
                );
            }
        } else {
            // "Contrato indefinido" representa una relación no asociada a
            // un proyecto específico. confirmed_project_id permanece NULL,
            // respetando la FK existente hacia projects.
            $projectId = null;
        }

        if (!SctProfileOptions::isContractorFlag($contractorFlag)) {
            throw new SctOnboardingDomainException('onboarding_error_contractor_required');
        }

        $isContractorHired = $contractorFlag === 'yes';

        if ($isContractorHired) {
            if (!$contractorId) {
                throw new SctOnboardingDomainException(
                    'onboarding_error_contractor_required_selection'
                );
            }

            $contractor = $this->repository->contractorInCompany(
                (int)$profile['id_company'],
                (int)$contractorId
            );

            if (!$contractor) {
                throw new SctOnboardingDomainException('onboarding_error_invalid_contractor');
            }
        } else {
            $contractorId = null;
        }

        if (!SctMutualityOptions::isValid($mutualCode)) {
            throw new SctOnboardingDomainException('onboarding_error_mutuality');
        }

        if ($yearsExperience === false) {
            throw new SctOnboardingDomainException('onboarding_error_experience');
        }

        if ($emergencyName === '') {
            throw new SctOnboardingDomainException('onboarding_error_emergency_name');
        }

        if (
            $emergencyPhone === ''
            || !preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/', $emergencyPhone)
        ) {
            throw new SctOnboardingDomainException('onboarding_error_emergency_phone');
        }

        if (!SctProfileOptions::isEmergencyRelation($emergencyRelation)) {
            throw new SctOnboardingDomainException('onboarding_error_emergency_relation');
        }

        $language = function_exists('normalizarIdiomaUsuario')
            ? normalizarIdiomaUsuario($rawLanguage)
            : $rawLanguage;

        if (!empty($profile['id_worker'])) {
            if (
                $phone === ''
                || !preg_match('/^\+?[0-9][0-9\s().-]{6,24}$/', $phone)
            ) {
                throw new SctOnboardingDomainException('onboarding_error_invalid_phone');
            }

            if ($position === '') {
                throw new SctOnboardingDomainException('onboarding_error_position_required');
            }
        }

        $healthValidation = null;

        if ($this->requiresHealthProfile($userId)) {
            $healthInput = [
                'phone' => $phone,
                'health_system' => $mutualCode,

                'emergency_name_1' => $emergencyName,
                'emergency_relation_1' => $emergencyRelation,
                'emergency_phone_1' => $emergencyPhone,

                'emergency_name_2' => $input['emergency_name_2'] ?? '',
                'emergency_relation_2' => $input['emergency_relation_2'] ?? '',
                'emergency_phone_2' => $input['emergency_phone_2'] ?? '',

                'conditions' => $input['conditions'] ?? [],
                'condition_other' => $input['condition_other'] ?? '',

                'medication_choice' => $input['medication_choice'] ?? '',
                'medications_text' => $input['medications_text'] ?? '',
                'medications_emergency' => $input['medications_emergency'] ?? '',

                'allergies' => $input['allergies'] ?? [],
                'allergy_details' => $input['allergy_details'] ?? '',
                'severe_reaction' => $input['severe_reaction'] ?? '',
                'severe_reaction_info' => $input['severe_reaction_info'] ?? '',

                'occupational_choice' => $input['occupational_choice'] ?? '',
                'occupational_diseases' => $input['occupational_diseases'] ?? [],
                'occupational_other' => $input['occupational_other'] ?? '',

                'restriction_choice' => $input['restriction_choice'] ?? '',
                'restriction_details' => $input['restriction_details'] ?? '',

                'accepted' => !empty($input['accepted']),
            ];

            $healthValidation = healthValidatePayload($healthInput);

            if (!$healthValidation['valid']) {
                throw new SctOnboardingDomainException('onboarding_error_health_required');
            }
        }

        $data = [
            'id_worker' => $profile['id_worker'] ?? null,
            'id_company' => (int)$profile['id_company'],
            'name' => self::cut($name, 50),
            'lastname' => self::cut($lastname, 50),
            'rut' => self::cut($rut, 10),
            'language' => $language,
            'phone' => self::cut($phone, 20),
            'position' => self::cut($position, 100),

            'confirmed_project_id' => $projectId ? (int)$projectId : null,
            'hired_by_contractor' => $isContractorHired,
            'id_contractor_company' => $contractorId ? (int)$contractorId : null,
            'mutual_code' => $mutualCode,
            'years_experience_current_role' => (int)$yearsExperience,
            'emergency_contact_name' => self::cut($emergencyName, 150),
            'emergency_contact_phone' => self::cut($emergencyPhone, 30),
            'emergency_contact_relation' => $emergencyRelation,
        ];

        $this->pdo->beginTransaction();
        $saveStage = 'ensure_state';

        try {
            $this->repository->ensureState($userId);

            $saveStage = 'user_profile';
            $this->repository->saveProfile($userId, $data);

            $saveStage = 'mutuality';
            $this->repository->setMutualCode($userId, $mutualCode);

            $saveStage = 'profile_details';
            $this->repository->saveProfileDetails($userId, $data);

            $saveStage = 'consent';
            if (!$this->repository->hasConsent($userId, self::CONSENT_VERSION)) {
                $this->repository->addConsent(
                    $userId,
                    self::CONSENT_VERSION,
                    $consentLanguage,
                    hash('sha256', $consentText),
                    $ip,
                    $userAgent
                );
            }

            $saveStage = 'health_profile';
            if ($healthValidation !== null) {
                healthSaveProfile(
                    $this->pdo,
                    $profile,
                    $healthValidation['data'],
                    $healthDeclarationText,
                    $ip,
                    false
                );
            }

            $saveStage = 'mark_profile';
            $this->repository->markProfile($userId);

            $saveStage = 'commit';
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RuntimeException(
                '[onboarding_step1:' . $saveStage . '] ' . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }

        return ['redirect' => 'onboarding.php?step=assessment'];
    }

    public function assessment(string $userId): array
    {
        $status = $this->status($userId);

        if (!$status['profile_complete']) {
            throw new SctOnboardingDomainException('onboarding_error_profile_first');
        }

        if ($status['assessment_complete']) {
            return ['complete' => true, 'redirect' => 'bienvenida.php'];
        }

        $activeAttempt = $this->repository->activeAttempt($userId);
        $attemptId = $activeAttempt
            ? (int)$activeAttempt['id_attempt']
            : $this->repository->createAttempt($userId, self::QUESTION_COUNT);

        $profile = $this->repository->profile($userId) ?: [];
        $language = function_exists('normalizarIdiomaUsuario')
            ? normalizarIdiomaUsuario((string)($profile['language'] ?? 'es'))
            : (string)($profile['language'] ?? 'es');

        return [
            'complete' => false,
            'attempt_id' => $attemptId,
            'language' => $language,
            'questions' => $this->repository->questions($attemptId, $userId, $language),
            'draft_answers' => $this->repository->draftAnswers($attemptId, $userId),
        ];
    }

    public function saveAssessmentDraft(
        string $userId,
        int $attemptId,
        array $answers
    ): array {
        $status = $this->status($userId);

        if (!$status['profile_complete']) {
            throw new SctOnboardingDomainException('onboarding_error_profile_first');
        }

        if ($status['assessment_complete']) {
            return [
                'saved' => false,
                'complete' => true,
            ];
        }

        $normalized = [];

        foreach ($answers as $answer) {
            $questionId = filter_var(
                $answer['id_question'] ?? null,
                FILTER_VALIDATE_INT
            );
            $optionId = filter_var(
                $answer['id_option'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$questionId || !$optionId) {
                continue;
            }

            $normalized[] = [
                'id_question' => (int)$questionId,
                'id_option' => (int)$optionId,
            ];
        }

        $this->repository->saveDraftAnswers(
            $attemptId,
            $userId,
            $normalized
        );

        return [
            'saved' => true,
            'count' => count($normalized),
        ];
    }

    public function submit(string $userId, int $attemptId, array $answers): array
    {
        $status = $this->status($userId);

        if (!$status['profile_complete']) {
            throw new SctOnboardingDomainException('onboarding_error_profile_first');
        }

        if ($status['assessment_complete']) {
            return [
                'complete' => true,
                'redirect' => 'bienvenida.php',
                'score' => $status['assessment_score'],
            ];
        }

        $expectedQuestions = $this->repository->expectedQuestions($attemptId, $userId);

        if (count($expectedQuestions) !== self::QUESTION_COUNT) {
            throw new SctOnboardingDomainException(
                'onboarding_error_assessment_unavailable'
            );
        }

        $normalized = [];
        $seen = [];

        foreach ($answers as $answer) {
            $questionId = filter_var(
                $answer['id_question'] ?? null,
                FILTER_VALIDATE_INT
            );
            $optionId = filter_var(
                $answer['id_option'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                !$questionId
                || !$optionId
                || !in_array($questionId, $expectedQuestions, true)
                || isset($seen[$questionId])
            ) {
                throw new SctOnboardingDomainException(
                    'onboarding_error_invalid_answers'
                );
            }

            $seen[$questionId] = true;
            $normalized[] = [
                'id_question' => $questionId,
                'id_option' => $optionId,
            ];
        }

        if (count($normalized) !== self::QUESTION_COUNT) {
            throw new SctOnboardingDomainException(
                'onboarding_error_answer_all'
            );
        }

        $result = $this->repository->completeAttempt(
            $attemptId,
            $userId,
            $normalized
        );

        $result['incorrect'] = max(
            0,
            (int)$result['total'] - (int)$result['correct']
        );
        $result['correct_percentage'] = (float)$result['score'];
        $result['incorrect_percentage'] = round(
            max(0, 100 - (float)$result['score']),
            2
        );
        $result['complete'] = true;
        $result['redirect'] = 'bienvenida.php';

        return $result;
    }

    private function canonicalRoles(string $userId): array
    {
        return array_values(array_unique(array_map(
            static fn(string $role): string => SctRolePolicy::canonical($role),
            $this->authorization->rolesForUser($userId)
        )));
    }

    private static function cut(string $value, int $length): string
    {
        return function_exists('mb_substr')
            ? mb_substr($value, 0, $length, 'UTF-8')
            : substr($value, 0, $length);
    }
}
