-- Migracion local e idempotente para completar 5 niveles en users_role_group.
-- Convenciones existentes detectadas:
-- - administrador          -> Nivel 2 (Administrador por Empresa)
-- - cliente                -> Nivel 3 (Gerente Empresa)
-- - trabajador             -> Nivel 5 (Trabajador)
-- Roles faltantes:
-- - administrador_completo -> Nivel 1 (Administrador Completo)
-- - jefatura               -> Nivel 4 (Jefatura Empresa)
--
-- NOTA: No modifica dumps compartidos ni elimina datos.

START TRANSACTION;

INSERT INTO users_role_group (
    id_company,
    name,
    description,
    state,
    create_by,
    date_create,
    last_update
)
SELECT
    c.id_company,
    'administrador_completo',
    'Administrador global',
    1,
    'local_migracion_5_niveles',
    NOW(),
    NOW()
FROM company c
WHERE c.state = 1
  AND NOT EXISTS (
      SELECT 1
      FROM users_role_group urg
      WHERE urg.id_company = c.id_company
        AND LOWER(TRIM(urg.name)) = 'administrador_completo'
  );

INSERT INTO users_role_group (
    id_company,
    name,
    description,
    state,
    create_by,
    date_create,
    last_update
)
SELECT
    c.id_company,
    'jefatura',
    'Jefatura de empresa',
    1,
    'local_migracion_5_niveles',
    NOW(),
    NOW()
FROM company c
WHERE c.state = 1
  AND NOT EXISTS (
      SELECT 1
      FROM users_role_group urg
      WHERE urg.id_company = c.id_company
        AND LOWER(TRIM(urg.name)) = 'jefatura'
  );

COMMIT;
