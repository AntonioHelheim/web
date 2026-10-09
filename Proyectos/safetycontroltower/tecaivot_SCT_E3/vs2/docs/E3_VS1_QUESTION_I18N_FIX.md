# E3-VS1 — Banco SST multidioma

## Estructura

Las entidades lógicas siguen siendo:
- `onboarding_questions`
- `onboarding_question_options`

Los textos traducibles se normalizan en:
- `onboarding_question_translations`
- `onboarding_question_option_translations`

Esto evita duplicar preguntas o respuestas correctas por idioma.

## Cobertura

Cada idioma contiene:
- 70 preguntas
- 280 alternativas

Idiomas:
`es`, `en`, `pt`, `fr`, `zh`.

## Evaluación inicial

`SctOnboardingRepository::questions()` recibe el idioma persistido del usuario
y obtiene las traducciones correspondientes. Si una traducción puntual falta,
usa español y finalmente el texto legado.

Los ID de pregunta y alternativa no cambian por idioma; por tanto, el borrador
de respuestas continúa siendo válido al cambiar de idioma.

## Administración

Ruta:
`api/preguntas/gestion-preguntas.php`

Capability:
`questions.manage`

El editor exige completar los cinco idiomas al guardar una pregunta, mantiene
cuatro alternativas y exactamente una respuesta correcta.

## Cambio de idioma

En Paso 1:
- se persiste inmediatamente;
- se actualiza la interfaz sin descartar campos ya escritos.

En Paso 2:
- se fuerza el guardado del borrador;
- se recarga el mismo intento;
- las preguntas y alternativas aparecen en el nuevo idioma.
