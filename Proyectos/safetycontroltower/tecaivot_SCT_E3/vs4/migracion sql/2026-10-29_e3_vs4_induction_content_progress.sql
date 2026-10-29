-- Safety Control Tower E3-VS4
-- Persistencia de avance por usuario/perfil para inducciones modulares.

CREATE TABLE IF NOT EXISTS induction_content_progress (
    id_progress BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_users VARCHAR(50) NOT NULL,
    role_name VARCHAR(50) NOT NULL,
    induction_id VARCHAR(100) NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    last_slide SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    completed_slides_json LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_progress),
    UNIQUE KEY uq_induction_content_progress_user_role_module (id_users, role_name, induction_id, module_id),
    KEY idx_induction_content_progress_user (id_users),
    CONSTRAINT fk_induction_content_progress_user
        FOREIGN KEY (id_users) REFERENCES users(id_users)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_induction_content_progress_completed_json
        CHECK (JSON_VALID(completed_slides_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
