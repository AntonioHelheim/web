-- ============================================================
-- Bifrost — migración v1.1: presets de apariencia (31-08-2026)
--
-- Cambio de jugabilidad: el jugador ya no elige colores libremente —
-- elige una de 3 opciones preestablecidas por género (ver
-- data/graphics-catalog.json y APPEARANCE_PRESETS en
-- js/entities/CharacterVisual.js).
--
-- Para bases de datos que ya estén en v1.0-seed (ejecutaste
-- sql/v1.0-seed-migration.sql antes). Si es una instalación nueva desde
-- cero, ignora este archivo — sql/schema.sql ya lo incluye.
--
-- Es seguro de correr una sola vez. NO cambia la apariencia de las
-- cuentas ya existentes (sus colores actuales se mantienen tal cual) —
-- appearance_preset queda en NULL para ellas hasta que abran "Cambiar
-- apariencia" y elijan una de las 3 opciones nuevas.
-- ============================================================

ALTER TABLE saves
    ADD COLUMN appearance_preset TINYINT UNSIGNED NULL DEFAULT NULL AFTER gender;
