-- Esquema inicial para el juego estilo Pokémon 2D
-- Ejecutar con: mysql -u tu_usuario -p < schema.sql

CREATE DATABASE IF NOT EXISTS pokeweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pokeweb;

-- Cuentas de usuario
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_save (user_id)
) ENGINE=InnoDB;

-- Catálogo de "criaturas" del juego (tu contenido original, no de Nintendo)
CREATE TABLE IF NOT EXISTS species (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(32) NOT NULL,
    base_hp INT UNSIGNED NOT NULL DEFAULT 20,
    base_atk INT UNSIGNED NOT NULL DEFAULT 10,
    base_def INT UNSIGNED NOT NULL DEFAULT 10,
    sprite_key VARCHAR(64) NOT NULL
) ENGINE=InnoDB;

INSERT INTO species (name, base_hp, base_atk, base_def, sprite_key) VALUES
    ('Flamlet', 22, 12, 8, 'mon_fire'),
    ('Aquabub', 24, 9, 12, 'mon_water'),
    ('Leafkin', 23, 10, 11, 'mon_grass');

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
