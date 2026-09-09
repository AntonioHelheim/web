-- ============================================================
-- Bifrost — migración hacia v1.0-seed (31-08-2026)
--
-- Para bases de datos EXISTENTES (como la que subiste, con tus 2 cuentas
-- reales y su progreso) — actualiza tu base al estado v1.0-seed SIN
-- borrar usuarios, partidas guardadas ni historial de login.
--
-- Si estás instalando el proyecto desde cero, IGNORA este archivo — usa
-- sql/schema.sql directamente, que ya incluye todo esto.
--
-- Cómo usarlo: phpMyAdmin -> tu base de datos -> pestaña "Importar" ->
-- elige este archivo -> Continuar. Es seguro de correr una sola vez.
-- ============================================================

-- 1. Falta la tabla de batallas silvestres (por eso el juego tira error
--    de base de datos al entrar a la hierba alta hoy).
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

-- 2. Renombra la clave "pokeball" a "runa_captura" en el inventario ya
--    guardado (evita nombres de marcas registradas en el proyecto) — tus
--    partidas guardadas se actualizan en el lugar, sin perder el resto
--    del inventario.
UPDATE saves
SET inventory_json = REPLACE(inventory_json, '"pokeball"', '"runa_captura"')
WHERE inventory_json LIKE '%pokeball%';

-- 3. Quita la tabla `species`: era una copia informativa duplicada del
--    catálogo real, que ahora vive en data/species.json (una sola
--    fuente, ítem 4 de ROADMAP-ARQUITECTURA.md). Ningún endpoint la
--    consultaba directamente.
DROP TABLE IF EXISTS species;

-- 4. Índices de rendimiento en las consultas más frecuentes del proyecto.
ALTER TABLE login_attempts
    ADD INDEX idx_login_attempts_identifier (identifier),
    ADD INDEX idx_login_attempts_ip (ip_address);

ALTER TABLE player_positions
    ADD INDEX idx_player_positions_map (map_key);

-- 5. Relación explícita entre un reto y la batalla PvP que generó (si
--    ya se aceptó). Si battle_challenges está vacía (como en tu caso),
--    esto no tiene ningún dato que reordenar, solo agrega la restricción.
ALTER TABLE battle_challenges
    ADD FOREIGN KEY (battle_id) REFERENCES pvp_battles(id) ON DELETE SET NULL;
