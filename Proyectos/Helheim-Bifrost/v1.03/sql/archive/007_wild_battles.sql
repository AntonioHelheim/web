-- Ejecuta este archivo UNA sola vez, solo si ya habías creado la base de
-- datos ANTES de esta actualización (batallas silvestres autoritativas en
-- el servidor). Si acabas de importar schema.sql completo por primera
-- vez, ignóralo: la tabla ya viene incluida ahí.
--
-- Cómo usarlo: phpMyAdmin -> selecciona tu base -> pestaña "Importar" ->
-- elige este archivo -> Continuar.

CREATE TABLE IF NOT EXISTS wild_battles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    player_mon_json JSON NOT NULL,
    enemy_mon_json JSON NOT NULL,
    last_action VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('active','finished') NOT NULL DEFAULT 'active',
    outcome ENUM('win','lose','flee') NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
