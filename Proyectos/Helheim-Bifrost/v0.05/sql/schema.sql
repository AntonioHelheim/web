-- Esquema inicial para Bifrost, juego 2D estilo Game Boy
-- Ejecutar con: mysql -u tu_usuario -p < schema.sql

CREATE DATABASE IF NOT EXISTS bifrost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bifrost;

-- Cuentas de usuario
-- Cuentas de usuario. El acceso es por código de un solo uso enviado al
-- correo (ver api/login.php) — password_hash queda para compatibilidad,
-- pero ya no se usa para cuentas nuevas.
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
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(190) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
    character_created TINYINT(1) NOT NULL DEFAULT 0,
    gender ENUM('boy','girl') NULL DEFAULT NULL,
    skin_color VARCHAR(7) NOT NULL DEFAULT '#f1c27d',
    hair_color VARCHAR(7) NOT NULL DEFAULT '#2c1b18',
    eye_color VARCHAR(7) NOT NULL DEFAULT '#3b2415',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_save (user_id)
) ENGINE=InnoDB;

-- Catálogo de "criaturas" del juego (contenido 100% original). Esta tabla
-- es informativa/de referencia: el juego en sí lee el catálogo desde
-- js/data.js (frontend) y su espejo en api/config.php (backend, para PvP).
CREATE TABLE IF NOT EXISTS species (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(32) NOT NULL,
    type VARCHAR(16) NOT NULL DEFAULT 'normal',
    base_hp INT UNSIGNED NOT NULL DEFAULT 20,
    base_atk INT UNSIGNED NOT NULL DEFAULT 10,
    base_def INT UNSIGNED NOT NULL DEFAULT 10,
    sprite_key VARCHAR(64) NOT NULL
) ENGINE=InnoDB;

INSERT INTO species (name, type, base_hp, base_atk, base_def, sprite_key) VALUES
    ('Chispodrilo', 'fuego', 22, 12, 7, 'fire_1'),
    ('Braseryx', 'fuego', 28, 16, 11, 'fire_2'),
    ('Vulcanor', 'fuego', 35, 21, 15, 'fire_3'),
    ('Marejino', 'agua', 24, 9, 10, 'water_1'),
    ('Corrientauro', 'agua', 30, 13, 14, 'water_2'),
    ('Abisalgo', 'agua', 38, 17, 19, 'water_3'),
    ('Brotalín', 'planta', 23, 10, 9, 'grass_1'),
    ('Espigón', 'planta', 29, 14, 13, 'grass_2'),
    ('Follascorpio', 'planta', 36, 18, 17, 'grass_3'),
    ('Chispequín', 'electricidad', 21, 13, 6, 'electric_1'),
    ('Voltígero', 'electricidad', 27, 17, 10, 'electric_2'),
    ('Amperidna', 'electricidad', 33, 22, 13, 'electric_3'),
    ('Puñolet', 'lucha', 24, 13, 8, 'fighting_1'),
    ('Katáfaro', 'lucha', 30, 17, 12, 'fighting_2'),
    ('Granmaestro', 'lucha', 37, 22, 16, 'fighting_3'),
    ('Plumín', 'volador', 20, 11, 7, 'flying_1'),
    ('Ventizarro', 'volador', 26, 15, 10, 'flying_2'),
    ('Tormenpluma', 'volador', 32, 19, 13, 'flying_3'),
    ('Sombrigato', 'oscuro', 22, 12, 8, 'dark_1'),
    ('Penumbraz', 'oscuro', 28, 16, 11, 'dark_2'),
    ('Eclipsino', 'oscuro', 35, 20, 15, 'dark_3'),
    ('Solete', 'diurno', 23, 11, 9, 'day_1'),
    ('Auroraz', 'diurno', 29, 15, 12, 'day_2'),
    ('Radialbo', 'diurno', 36, 19, 16, 'day_3');

-- === MULTIJUGADOR ===

-- Posición "en vivo" de cada jugador conectado. Se actualiza en cada tick
-- de sondeo (polling) del cliente. Va separada de `saves` para no generar
-- una escritura pesada cada 1-2 segundos sobre la tabla de guardado real.
CREATE TABLE IF NOT EXISTS player_positions (
    user_id INT UNSIGNED PRIMARY KEY,
    username VARCHAR(32) NOT NULL,
    map_key VARCHAR(64) NOT NULL DEFAULT 'overworld',
    pos_x INT NOT NULL DEFAULT 0,
    pos_y INT NOT NULL DEFAULT 0,
    facing VARCHAR(8) NOT NULL DEFAULT 'down',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Retos de batalla entre dos jugadores humanos (equivalente al cable link
-- de las versiones originales de Game Boy).
CREATE TABLE IF NOT EXISTS battle_challenges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT UNSIGNED NOT NULL,
    to_user_id INT UNSIGNED NOT NULL,
    status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
    battle_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
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
