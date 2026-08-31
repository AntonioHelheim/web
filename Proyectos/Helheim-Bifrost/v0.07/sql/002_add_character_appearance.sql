-- Ejecuta este archivo UNA sola vez, solo si ya habías creado la base de
-- datos ANTES de esta actualización (con schema.sql importado previamente).
-- Si acabas de importar schema.sql completo por primera vez, ignóralo:
-- las columnas ya vienen incluidas ahí.
--
-- Cómo usarlo: phpMyAdmin -> selecciona tu base -> pestaña "Importar" ->
-- elige este archivo -> Continuar.

ALTER TABLE saves
    ADD COLUMN character_created TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN gender ENUM('boy','girl') NULL DEFAULT NULL,
    ADD COLUMN skin_color VARCHAR(7) NOT NULL DEFAULT '#f1c27d',
    ADD COLUMN hair_color VARCHAR(7) NOT NULL DEFAULT '#2c1b18',
    ADD COLUMN eye_color VARCHAR(7) NOT NULL DEFAULT '#3b2415';
