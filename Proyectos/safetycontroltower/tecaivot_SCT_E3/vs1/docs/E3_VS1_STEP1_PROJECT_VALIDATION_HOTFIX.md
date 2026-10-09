# E3-VS1 — Hotfix Paso 1

## Objetivo

Corregir la regresión del Paso 1 manteniendo la experiencia visual del fix
anterior.

La capa visual ahora sólo refleja el estado DOM después de los handlers
funcionales. No modifica valores ni selecciones.

## Contrato indefinido

El selector de proyecto incorpora `Contrato indefinido`.

No se crea un registro artificial en `projects`. La selección se persiste
mediante `confirmed_project_id = NULL`, que representa ausencia intencional de
un proyecto específico y respeta la FK existente.

## Validación

Antes de enviar el formulario se validan los requisitos del frontend y los
requisitos condicionales de salud. Si existe un pendiente, no se realiza la
llamada al backend: la primera sección incompleta se marca en rojo, recibe
scroll y foco.

Las secciones completas se resaltan con su color normal de acento.
