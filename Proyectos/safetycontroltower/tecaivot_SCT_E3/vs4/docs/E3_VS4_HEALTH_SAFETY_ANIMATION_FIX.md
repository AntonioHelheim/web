# E3 VS4 — Health & Safety Parte 1 — Animation Fidelity Fix

## Objetivo
Preservar la secuencia visual de las primeras 25 láminas del PowerPoint ENGIE dentro de `api/actividades/health-safety.php`, manteniendo navbar, multimedia y navegación SCT.

## Implementación
- Se analizaron los nodos OOXML de timing del PowerPoint.
- Se detectaron 107 efectos internos distribuidos en 17 láminas.
- Los objetos animados se separaron en capas individuales transparentes.
- La imagen base de cada slide excluye los objetos que deben aparecer posteriormente, evitando contenido superpuesto.
- `Siguiente`, flecha derecha, PageDown o espacio avanzan primero la animación interna y luego la siguiente lámina.
- `Anterior`, flecha izquierda o PageUp retroceden primero la secuencia interna.
- Se reproducen entradas tipo fade/appear, entradas direccionales, barn reveal y motion paths del material fuente con aproximación web equivalente.
- Audios, videos y GIF se mantienen integrados desde el fix multimedia anterior.
- Para las seis láminas con GIF, el frame estático fue retirado de la imagen base para evitar doble renderizado.

## Transiciones entre diapositivas
Las primeras 25 diapositivas no contienen nodos OOXML `p:transition`. Se aplica un fade neutro y breve al cambiar de slide para mejorar continuidad sin atribuir al archivo una transición que no existe en su metadata.

## Archivos principales
- `api/actividades/health-safety.php`
- `app/Induction/HealthSafetyContent.php`
- `app/Induction/data/health-safety-part-01-animations.json`
- `js/health-safety-induction.js`
- `css/health-safety-induction.css`
- `assets/inductions/health-safety/part-01/base/`
- `assets/inductions/health-safety/part-01/layers/`
- `assets/inductions/health-safety/part-01/final/`
- `assets/inductions/health-safety/part-01/media/`
