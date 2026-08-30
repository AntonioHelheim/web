-- Ejecuta este archivo UNA sola vez, solo si ya habías creado la base de
-- datos ANTES de esta actualización (login por código en vez de
-- contraseña). Si acabas de importar schema.sql completo por primera vez,
-- ignóralo: las columnas y tablas ya vienen incluidas ahí.
--
-- Cómo usarlo: phpMyAdmin -> selecciona tu base -> pestaña "Importar" ->
-- elige este archivo -> Continuar.
--
-- ⚠️ IMPORTANTE: tus cuentas ya existentes NO tendrán correo (email queda
-- NULL), así que no vas a poder iniciar sesión con ellas hasta que les
-- agregues uno. Dos opciones:
--   a) Regístrate de nuevo con una cuenta nueva (usuario + correo).
--   b) Ejecuta manualmente, reemplazando los valores:
--        UPDATE users SET email = 'tu@correo.com' WHERE username = 'tu_usuario';

ALTER TABLE users ADD COLUMN email VARCHAR(190) NULL UNIQUE AFTER username;
ALTER TABLE users MODIFY password_hash VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS login_codes (
    id_login_code INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(190) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
