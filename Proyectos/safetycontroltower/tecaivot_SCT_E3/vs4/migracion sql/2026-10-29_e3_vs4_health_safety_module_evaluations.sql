-- Safety Control Tower E3-VS4
-- Evaluaciones modulares de la inducción Health & Safety.
-- Registra resultado por Nº de evaluación / Nº de intento y conserva respuestas.

CREATE TABLE IF NOT EXISTS induction_module_evaluation_attempts (
    id_attempt BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_users VARCHAR(50) NOT NULL,
    role_name VARCHAR(50) NOT NULL,
    induction_id VARCHAR(100) NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    evaluation_id VARCHAR(100) NOT NULL,
    evaluation_number SMALLINT UNSIGNED NOT NULL,
    attempt_number SMALLINT UNSIGNED NOT NULL,
    answers_json LONGTEXT NOT NULL,
    correct_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    question_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    score_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_attempt),
    UNIQUE KEY uq_induction_eval_attempt (id_users, role_name, induction_id, module_id, evaluation_id, attempt_number),
    KEY idx_induction_eval_user_module (id_users, induction_id, module_id),
    KEY idx_induction_eval_passed (id_users, induction_id, module_id, passed),
    CONSTRAINT fk_induction_eval_user
        FOREIGN KEY (id_users) REFERENCES users(id_users)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_induction_eval_answers_json CHECK (JSON_VALID(answers_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
