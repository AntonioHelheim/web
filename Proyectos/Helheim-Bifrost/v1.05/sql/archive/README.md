# sql/archive/ — migraciones históricas (pre-v1.0-seed)

Estos 4 archivos ya no hacen falta para nada — quedan solo como registro
histórico de cómo fue creciendo el esquema, por si algún día hay que
entender por qué algo quedó como quedó.

`sql/schema.sql` (instalación nueva) y `sql/v1.0-seed-migration.sql`
(actualizar una base existente de antes del 31-08-2026) ya incluyen todo
lo que hacían estos 4 archivos, más las mejoras de la v1.0-seed. **No
hace falta importar nada de esta carpeta.**

| Archivo | Qué hacía |
|---|---|
| `002_add_character_appearance.sql` | Agregó las columnas de apariencia del personaje a `saves`. |
| `003_expand_species.sql` | Amplió el catálogo a 24 criaturas (luego reemplazado del todo por `data/species.json`, ítem 4 del roadmap). |
| `005_email_login.sql` | Agregó el login por código (columna `email`, tablas `login_codes`/`login_attempts`). |
| `007_wild_battles.sql` | Agregó la tabla `wild_battles` (batallas silvestres autoritativas en servidor, ítem 3 del roadmap). |
