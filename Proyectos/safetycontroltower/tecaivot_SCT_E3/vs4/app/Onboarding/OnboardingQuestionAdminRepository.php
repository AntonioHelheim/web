<?php

final class SctOnboardingQuestionAdminRepository
{
    public const LANGUAGES = ['es','en','pt','fr','zh'];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listAll(): array
    {
        $questions = $this->pdo->query(
            'SELECT id_question,question_code,state
             FROM onboarding_questions
             ORDER BY question_code'
        )->fetchAll();

        $qText = $this->pdo->prepare(
            'SELECT language_code,question_text
             FROM onboarding_question_translations
             WHERE id_question=:id_question'
        );

        $options = $this->pdo->prepare(
            'SELECT id_option,is_correct,sort_order
             FROM onboarding_question_options
             WHERE id_question=:id_question
             ORDER BY sort_order'
        );

        $oText = $this->pdo->prepare(
            'SELECT language_code,option_text
             FROM onboarding_question_option_translations
             WHERE id_option=:id_option'
        );

        foreach ($questions as &$question) {
            $question['id_question'] = (int)$question['id_question'];
            $question['state'] = (int)$question['state'];
            $question['translations'] = [];

            $qText->execute(['id_question'=>$question['id_question']]);
            foreach ($qText->fetchAll() as $row) {
                $question['translations'][(string)$row['language_code']] = [
                    'question' => (string)$row['question_text'],
                    'options' => [],
                ];
            }

            foreach (self::LANGUAGES as $language) {
                $question['translations'][$language] ??= [
                    'question' => '',
                    'options' => [],
                ];
            }

            $options->execute(['id_question'=>$question['id_question']]);
            $question['options'] = $options->fetchAll();

            foreach ($question['options'] as &$option) {
                $option['id_option'] = (int)$option['id_option'];
                $option['is_correct'] = (int)$option['is_correct'];
                $option['sort_order'] = (int)$option['sort_order'];

                $oText->execute(['id_option'=>$option['id_option']]);
                $translations = [];
                foreach ($oText->fetchAll() as $row) {
                    $translations[(string)$row['language_code']] = (string)$row['option_text'];
                }

                foreach (self::LANGUAGES as $language) {
                    $question['translations'][$language]['options'][] = [
                        'id_option' => $option['id_option'],
                        'sort_order' => $option['sort_order'],
                        'text' => $translations[$language] ?? '',
                    ];
                }
            }
            unset($option);
        }
        unset($question);

        return $questions;
    }

    public function save(array $input, string $actor): int
    {
        $idQuestion = filter_var($input['id_question'] ?? null, FILTER_VALIDATE_INT);
        $state = !empty($input['state']) ? 1 : 0;
        $correctIndex = filter_var(
            $input['correct_index'] ?? null,
            FILTER_VALIDATE_INT,
            ['options'=>['min_range'=>0,'max_range'=>3]]
        );
        $translations = $input['translations'] ?? [];

        if ($correctIndex === false || !is_array($translations)) {
            throw new InvalidArgumentException('question_bank_invalid');
        }

        foreach (self::LANGUAGES as $language) {
            if (!isset($translations[$language]) || !is_array($translations[$language])) {
                throw new InvalidArgumentException('question_bank_all_languages_required');
            }

            $question = trim((string)($translations[$language]['question'] ?? ''));
            $options = $translations[$language]['options'] ?? [];

            if ($question === '' || sctTextLength($question) > 2000 || !is_array($options) || count($options) !== 4) {
                throw new InvalidArgumentException('question_bank_all_languages_required');
            }

            foreach ($options as $option) {
                $text = trim((string)($option['text'] ?? $option ?? ''));
                if ($text === '' || sctTextLength($text) > 500) {
                    throw new InvalidArgumentException('question_bank_all_languages_required');
                }
            }
        }

        $this->pdo->beginTransaction();

        try {
            if ($idQuestion) {
                $check = $this->pdo->prepare(
                    'SELECT id_question
                     FROM onboarding_questions
                     WHERE id_question=:id_question
                     FOR UPDATE'
                );
                $check->execute(['id_question'=>$idQuestion]);
                if (!$check->fetchColumn()) {
                    throw new InvalidArgumentException('question_bank_not_found');
                }

                $this->pdo->prepare(
                    'UPDATE onboarding_questions
                     SET question=:question,
                         state=:state,
                         last_update=NOW()
                     WHERE id_question=:id_question'
                )->execute([
                    'question'=>trim((string)$translations['es']['question']),
                    'state'=>$state,
                    'id_question'=>$idQuestion,
                ]);
            } else {
                $code = $this->nextCode();
                $stmt = $this->pdo->prepare(
                    'INSERT INTO onboarding_questions(
                        question_code,question,state,date_create,last_update
                     ) VALUES(
                        :code,:question,:state,NOW(),NOW()
                     )'
                );
                $stmt->execute([
                    'code'=>$code,
                    'question'=>trim((string)$translations['es']['question']),
                    'state'=>$state,
                ]);
                $idQuestion = (int)$this->pdo->lastInsertId();

                $insertOption = $this->pdo->prepare(
                    'INSERT INTO onboarding_question_options(
                        id_question,option_text,is_correct,sort_order
                     ) VALUES(
                        :id_question,:option_text,:is_correct,:sort_order
                     )'
                );

                for ($index=0; $index<4; $index++) {
                    $insertOption->execute([
                        'id_question'=>$idQuestion,
                        'option_text'=>trim((string)$translations['es']['options'][$index]['text']),
                        'is_correct'=>$index===$correctIndex ? 1 : 0,
                        'sort_order'=>$index+1,
                    ]);
                }
            }

            $options = $this->options($idQuestion);
            if (count($options) !== 4) {
                throw new RuntimeException('question_bank_options_invalid');
            }

            $questionTranslation = $this->pdo->prepare(
                'INSERT INTO onboarding_question_translations(
                    id_question,language_code,question_text,updated_by,date_create,last_update
                 ) VALUES(
                    :id_question,:language_code,:question_text,:updated_by,NOW(),NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    question_text=VALUES(question_text),
                    updated_by=VALUES(updated_by),
                    last_update=NOW()'
            );

            $optionTranslation = $this->pdo->prepare(
                'INSERT INTO onboarding_question_option_translations(
                    id_option,language_code,option_text,updated_by,date_create,last_update
                 ) VALUES(
                    :id_option,:language_code,:option_text,:updated_by,NOW(),NOW()
                 )
                 ON DUPLICATE KEY UPDATE
                    option_text=VALUES(option_text),
                    updated_by=VALUES(updated_by),
                    last_update=NOW()'
            );

            foreach (self::LANGUAGES as $language) {
                $questionTranslation->execute([
                    'id_question'=>$idQuestion,
                    'language_code'=>$language,
                    'question_text'=>trim((string)$translations[$language]['question']),
                    'updated_by'=>$actor,
                ]);

                for ($index=0; $index<4; $index++) {
                    $optionId = (int)$options[$index]['id_option'];
                    $optionText = trim((string)$translations[$language]['options'][$index]['text']);

                    $optionTranslation->execute([
                        'id_option'=>$optionId,
                        'language_code'=>$language,
                        'option_text'=>$optionText,
                        'updated_by'=>$actor,
                    ]);

                    if ($language === 'es') {
                        $this->pdo->prepare(
                            'UPDATE onboarding_question_options
                             SET option_text=:option_text,
                                 is_correct=:is_correct
                             WHERE id_option=:id_option'
                        )->execute([
                            'option_text'=>$optionText,
                            'is_correct'=>$index===$correctIndex ? 1 : 0,
                            'id_option'=>$optionId,
                        ]);
                    }
                }
            }

            $this->pdo->commit();
            return $idQuestion;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function options(int $idQuestion): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_option,sort_order,is_correct
             FROM onboarding_question_options
             WHERE id_question=:id_question
             ORDER BY sort_order'
        );
        $stmt->execute(['id_question'=>$idQuestion]);
        return $stmt->fetchAll();
    }

    private function nextCode(): string
    {
        $max = 0;
        foreach ($this->pdo->query(
            "SELECT question_code
             FROM onboarding_questions
             WHERE question_code LIKE 'SST%'"
        )->fetchAll(PDO::FETCH_COLUMN) as $code) {
            if (preg_match('/^SST(\d+)$/', (string)$code, $match)) {
                $max = max($max, (int)$match[1]);
            }
        }

        return 'SST' . str_pad((string)($max + 1), 3, '0', STR_PAD_LEFT);
    }
}
