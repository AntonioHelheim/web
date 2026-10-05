-- Safety Control Tower vs5
-- Navbar / mutualidad / logros / formulario de salud ocupacional
-- Migración aditiva e idempotente para MariaDB 10.6+.
SET NAMES utf8mb4;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS mutual_code VARCHAR(20) NULL AFTER profile_photo_path;

ALTER TABLE company_test
    ADD COLUMN IF NOT EXISTS module_code VARCHAR(32) NOT NULL DEFAULT 'health_safety' AFTER type;

CREATE TABLE IF NOT EXISTS worker_health_profile (
    id_health_profile INT NOT NULL AUTO_INCREMENT,
    id_company INT NOT NULL,
    id_users VARCHAR(50) NOT NULL,
    id_worker INT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 1,
    phone VARCHAR(30) NULL,
    health_system VARCHAR(20) NOT NULL,
    emergency_name_1 VARCHAR(150) NOT NULL,
    emergency_relation_1 VARCHAR(30) NOT NULL,
    emergency_phone_1 VARCHAR(30) NOT NULL,
    emergency_name_2 VARCHAR(150) NULL,
    emergency_relation_2 VARCHAR(30) NULL,
    emergency_phone_2 VARCHAR(30) NULL,
    conditions_json LONGTEXT NOT NULL,
    condition_other_enc LONGTEXT NULL,
    medication_choice VARCHAR(20) NOT NULL,
    medications_text_enc LONGTEXT NULL,
    medications_emergency_enc LONGTEXT NULL,
    allergies_json LONGTEXT NOT NULL,
    allergy_details_enc LONGTEXT NULL,
    severe_reaction VARCHAR(20) NULL,
    severe_reaction_info_enc LONGTEXT NULL,
    occupational_choice VARCHAR(30) NOT NULL,
    occupational_diseases_json LONGTEXT NULL,
    occupational_other_enc LONGTEXT NULL,
    restriction_choice VARCHAR(20) NOT NULL,
    restriction_details_enc LONGTEXT NULL,
    created_by VARCHAR(50) NOT NULL,
    date_create DATETIME NOT NULL,
    last_update DATETIME NOT NULL,
    PRIMARY KEY (id_health_profile),
    KEY idx_health_user_current (id_users, is_current),
    KEY idx_health_company (id_company),
    KEY idx_health_worker (id_worker),
    CONSTRAINT fk_health_profile_company FOREIGN KEY (id_company) REFERENCES company(id_company) ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT fk_health_profile_user FOREIGN KEY (id_users) REFERENCES users(id_users) ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT fk_health_profile_worker FOREIGN KEY (id_worker) REFERENCES workers(id_worker) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_health_declaration (
    id_health_declaration INT NOT NULL AUTO_INCREMENT,
    id_company INT NOT NULL,
    id_users VARCHAR(50) NOT NULL,
    id_health_profile INT NOT NULL,
    accepted_at DATETIME NOT NULL,
    declaration_version VARCHAR(20) NOT NULL,
    declaration_hash CHAR(64) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_by VARCHAR(50) NOT NULL,
    date_create DATETIME NOT NULL,
    last_update DATETIME NOT NULL,
    PRIMARY KEY (id_health_declaration),
    KEY idx_health_decl_user (id_users, accepted_at),
    KEY idx_health_decl_profile (id_health_profile),
    CONSTRAINT fk_health_decl_company FOREIGN KEY (id_company) REFERENCES company(id_company) ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT fk_health_decl_user FOREIGN KEY (id_users) REFERENCES users(id_users) ON UPDATE CASCADE ON DELETE NO ACTION,
    CONSTRAINT fk_health_decl_profile FOREIGN KEY (id_health_profile) REFERENCES worker_health_profile(id_health_profile) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (code, description)
SELECT 'health_profile.view_emergency', 'Consultar antecedentes de salud necesarios para responder a una emergencia.'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE code = 'health_profile.view_emergency');
