-- Ejecuta este archivo UNA sola vez, solo si ya habías creado la base de
-- datos ANTES de esta actualización del catálogo de criaturas. Actualiza la
-- tabla `species` (solo informativa; el juego en sí no la consulta, lee su
-- catálogo de js/data.js y api/config.php) para que coincida con las 24
-- criaturas nuevas. Si acabas de importar schema.sql completo por primera
-- vez, ignora este archivo: ya viene incluido ahí.
--
-- Cómo usarlo: phpMyAdmin -> selecciona tu base -> pestaña "Importar" ->
-- elige este archivo -> Continuar.

ALTER TABLE species ADD COLUMN IF NOT EXISTS type VARCHAR(16) NOT NULL DEFAULT 'normal';

DELETE FROM species;

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

-- Nota: si tu versión de MySQL/MariaDB no soporta "ADD COLUMN IF NOT EXISTS"
-- (las versiones muy antiguas no lo soportan), quita "IF NOT EXISTS" de esa
-- línea. Si la columna ya existiera te daría un error inofensivo en esa
-- única línea; el resto del archivo se ejecuta igual.
