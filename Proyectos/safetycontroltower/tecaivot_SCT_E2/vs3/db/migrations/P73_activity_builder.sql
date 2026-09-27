-- Safety Control Tower — P73
-- Constructor guiado de actividades: extensiones aditivas y compatibles.
-- No elimina ni renombra columnas/tablas existentes.

ALTER TABLE company_test_rel_questions
    ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER assigned_score;

UPDATE company_test_rel_questions
SET sort_order = id_rel
WHERE sort_order = 0;

ALTER TABLE questions
    ADD COLUMN IF NOT EXISTS question_type VARCHAR(30) NOT NULL DEFAULT 'multiple_choice' AFTER question;

CREATE TABLE IF NOT EXISTS question_media (
    id_question_media INT NOT NULL AUTO_INCREMENT,
    id_question INT NOT NULL,
    media_type ENUM('image','video') NOT NULL DEFAULT 'image',
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_by VARCHAR(50) NOT NULL,
    date_create DATETIME NOT NULL,
    PRIMARY KEY (id_question_media),
    KEY idx_question_media_question (id_question),
    CONSTRAINT fk_question_media_question
        FOREIGN KEY (id_question) REFERENCES questions(id_questions)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Material de apoyo para módulos que no usan test_materials.
CREATE TABLE IF NOT EXISTS activity_support_materials (
    id_activity_material INT NOT NULL AUTO_INCREMENT,
    entity_type ENUM('dynamic_form','protocol') NOT NULL,
    entity_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_by VARCHAR(50) NOT NULL,
    date_create DATETIME NOT NULL,
    PRIMARY KEY (id_activity_material),
    KEY idx_activity_support_entity (entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignación explícita de formularios sin alterar el flujo de respuestas existente.
CREATE TABLE IF NOT EXISTS dynamic_form_assignments (
    id_form_assignment INT NOT NULL AUTO_INCREMENT,
    id_form INT NOT NULL,
    id_company INT NOT NULL,
    id_users VARCHAR(50) NOT NULL,
    access_start DATETIME DEFAULT NULL,
    deadline DATETIME DEFAULT NULL,
    status ENUM('pending','submitted','cancelled') NOT NULL DEFAULT 'pending',
    created_by VARCHAR(50) NOT NULL,
    date_create DATETIME NOT NULL,
    last_update DATETIME NOT NULL,
    PRIMARY KEY (id_form_assignment),
    UNIQUE KEY uq_dynamic_form_assignment_user (id_form,id_users),
    KEY idx_dynamic_form_assignment_company (id_company),
    KEY idx_dynamic_form_assignment_status (status),
    CONSTRAINT fk_dynamic_form_assignment_form
        FOREIGN KEY (id_form) REFERENCES dynamic_forms(id_form)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_dynamic_form_assignment_company
        FOREIGN KEY (id_company) REFERENCES company(id_company)
        ON DELETE NO ACTION ON UPDATE CASCADE,
    CONSTRAINT fk_dynamic_form_assignment_user
        FOREIGN KEY (id_users) REFERENCES users(id_users)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
