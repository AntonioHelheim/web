# E3-VS1 — Retroceso de onboarding y catálogo de contratistas

## Retroceso

Mientras el usuario haya completado el Paso 1 y tenga pendiente el Paso 2:

`onboarding.php?step=profile`

permite volver al formulario inicial.

Guardar nuevamente el Paso 1 conserva el intento de evaluación existente. El usuario retorna
al Paso 2 con sus mismas 15 preguntas asignadas.

## Contratistas

Nueva estructura:

`contractor_companies`

Campos principales:
- id_contractor_company
- id_company
- rut
- business_name
- trade_name
- state

La empresa contratista pertenece al contexto multiempresa de la empresa cliente.

`user_profile_details.id_contractor_company`

es la asociación canónica cuando `hired_by_contractor = 1`.

## Búsqueda

Endpoint:

`api/onboarding/contratistas-buscar.php?q=...`

Busca por:
- razón social;
- nombre de fantasía;
- RUT con formato;
- RUT sin puntos ni guion.

El endpoint siempre limita resultados a la empresa del usuario autenticado.

## Responsive

- Escritorio: menú desplegable bajo el campo.
- Tablet: ancho completo del control.
- Teléfono: lista de resultados limitada a 42vh y acciones del Paso 2 apiladas.
