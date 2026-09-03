-- ============================================================
-- Bifrost — esquema de base de datos
-- v1.0-seed (31-08-2026) — línea base consolidada. Reemplaza el
-- historial de migraciones incrementales (002/003/005/007, archivadas en
-- sql/archive/ solo como referencia histórica) para instalaciones nuevas.
--
-- Si ya tienes una base de datos con datos reales de antes de esta fecha,
-- NO reimportes este archivo entero (lo dejaría vacío) — usa
-- sql/v1.0-seed-migration.sql en su lugar, que actualiza tu base
-- existente sin perder cuentas ni progreso.
--
-- Ejecutar con: mysql -u tu_usuario -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS bifrost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bifrost;

-- === CUENTAS Y LOGIN ===

-- Cuentas de usuario. El acceso es por código de un solo uso enviado al
-- correo (ver api/login.php) — password_hash queda para compatibilidad
-- con instalaciones muy viejas, pero ya no se usa para cuentas nuevas.
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE,
    email VARCHAR(190) NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Códigos de acceso de un solo uso (login sin contraseña).
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

-- Historial de intentos de verificación (rate limiting / bloqueo tras
-- fallos repetidos). `identifier` es el correo usado en el intento.
-- Índices en identifier/ip_address (nuevo en v1.0-seed): esta tabla se
-- consulta en CADA intento de login (api/login.php busca por identifier
-- O ip_address), así que un índice ahí ayuda apenas empiece a crecer.
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(190) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_identifier (identifier),
    INDEX idx_login_attempts_ip (ip_address)
) ENGINE=InnoDB;

-- === PARTIDA GUARDADA ===

-- Una partida guardada por usuario. El estado del juego (posición, equipo,
-- inventario, mapa actual) se guarda como JSON para no tener que migrar el
-- esquema cada vez que agregues una mecánica nueva.
CREATE TABLE IF NOT EXISTS saves (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    map_key VARCHAR(64) NOT NULL DEFAULT 'overworld',
    pos_x INT NOT NULL DEFAULT 0,
    pos_y INT NOT NULL DEFAULT 0,
    party_json JSON NOT NULL,
    inventory_json JSON NOT NULL,
    -- Apariencia del personaje, elegida la primera vez que se juega.
    -- Desde el 31-08-2026 el jugador elige una de 3 opciones
    -- preestablecidas por género (appearance_preset, 1-3) en vez de
    -- colores libres — skin_color/hair_color/eye_color quedan como el
    -- color YA RESUELTO de esa opción (los resuelve el servidor, ver
    -- resolve_appearance_preset() en api/config.php), para que el resto
    -- del código de renderizado no tenga que cambiar.
    character_created TINYINT(1) NOT NULL DEFAULT 0,
    gender ENUM('boy','girl') NULL DEFAULT NULL,
    appearance_preset TINYINT UNSIGNED NULL DEFAULT NULL,
    skin_color VARCHAR(7) NOT NULL DEFAULT '#f1c27d',
    hair_color VARCHAR(7) NOT NULL DEFAULT '#2c1b18',
    eye_color VARCHAR(7) NOT NULL DEFAULT '#3b2415',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_save (user_id)
) ENGINE=InnoDB;

-- NOTA v1.0-seed: la tabla `species` que existía antes se eliminó — era
-- una copia informativa duplicada del catálogo real, que desde el ítem 4
-- de ROADMAP-ARQUITECTURA.md vive en data/species.json (una sola fuente,
-- la lee tanto api/config.php como js/scenes/PreloadScene.js). Ningún
-- endpoint la consultaba (se verificó antes de borrarla).

-- === MULTIJUGADOR ===

-- Posición "en vivo" de cada jugador conectado. Se actualiza en cada tick
-- de sondeo (polling) del cliente. Va separada de `saves` para no generar
-- una escritura pesada cada 1-2 segundos sobre la tabla de guardado real.
-- Índice en map_key (nuevo en v1.0-seed): api/nearby_players.php filtra
-- por mapa en cada sondeo (~cada 1.3s por jugador activo) — es la
-- consulta de mayor frecuencia de todo el proyecto.
CREATE TABLE IF NOT EXISTS player_positions (
    user_id INT UNSIGNED PRIMARY KEY,
    username VARCHAR(32) NOT NULL,
    map_key VARCHAR(64) NOT NULL DEFAULT 'overworld',
    pos_x INT NOT NULL DEFAULT 0,
    pos_y INT NOT NULL DEFAULT 0,
    facing VARCHAR(8) NOT NULL DEFAULT 'down',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_player_positions_map (map_key)
) ENGINE=InnoDB;

-- Estado de una batalla PvP activa. El servidor es la única autoridad que
-- calcula daño y decide turnos; ambos clientes solo leen y proponen acciones,
-- así que nunca pueden quedar en desacuerdo sobre el resultado.
CREATE TABLE IF NOT EXISTS pvp_battles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player1_id INT UNSIGNED NOT NULL,
    player2_id INT UNSIGNED NOT NULL,
    mon1_json JSON NOT NULL,
    mon2_json JSON NOT NULL,
    turn_user_id INT UNSIGNED NOT NULL,
    last_action VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('active','finished') NOT NULL DEFAULT 'active',
    winner_id INT UNSIGNED NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (player1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (player2_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Retos de batalla entre dos jugadores humanos (equivalente al cable link
-- de las versiones originales de Game Boy). FK a pvp_battles (nuevo en
-- v1.0-seed): antes battle_id no tenía relación declarada con la tabla
-- que en verdad referencia; si esa batalla se borra alguna vez, el reto
-- queda con battle_id=NULL en vez de un puntero colgante.
CREATE TABLE IF NOT EXISTS battle_challenges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT UNSIGNED NOT NULL,
    to_user_id INT UNSIGNED NOT NULL,
    status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
    battle_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (battle_id) REFERENCES pvp_battles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Batallas contra criaturas salvajes, calculadas en el servidor (ítem 3
-- de ROADMAP-ARQUITECTURA.md) — antes el daño se calculaba en el cliente
-- y cualquiera podía alterar el JS del navegador para volverse invencible.
-- Mismo patrón que pvp_battles, pero de un solo jugador contra la criatura
-- generada por el servidor.
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
