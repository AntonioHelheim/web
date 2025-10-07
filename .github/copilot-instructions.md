# Copilot Instructions for CFT de Magallanes Web Projects

## Overview
This workspace contains multiple versions and backups of web projects for CFT de Magallanes, primarily using HTML, CSS, JavaScript, and PHP. The structure is modular, with each version (e.g., `BKP V1.1`, `BKP V2.0`, etc.) containing its own assets and code. The main active development appears in the `Proyectos/CFTdeMagallanes/Informes/pages` directory.

## Key Patterns & Architecture
- **Versioned Backups:** Each major release is stored in a separate folder (`BKP Vx.x`). Always check the latest version for current logic and assets.
- **Modular Assets:** CSS, JS, and images are organized by version and reused across projects. Global assets are in top-level `css/`, `js/`, and `images/`.
- **HTML/JS Integration:** Most interactive features (e.g., signature generator in `firmas.html`) use vanilla JS and HTML5 APIs (Canvas, DOM manipulation). Bootstrap is used for UI components.
- **PHP Backend:** Some pages (e.g., `index_01_isdb_live.php`) use PHP for server-side logic. Data flow is typically file-based or via simple DB connections (see `db.php`).
- **No Build System:** There is no automated build or test workflow. All code is run directly in the browser or via PHP server.

## Developer Workflow
- **Editing:** Directly edit HTML, JS, CSS, or PHP files. Use the latest backup/version folder for new features.
- **Testing:** Open HTML files in a browser. For PHP, use a local server (e.g., `php -S localhost:8000`).
- **Debugging:** Use browser dev tools for JS/CSS. For PHP, check `error_log` files in relevant directories.
- **Assets:** Place new images in the appropriate `images/` folder for the current version. Update references in HTML/JS as needed.

## Conventions & Patterns
- **Spanish Naming:** Most variables, comments, and UI text are in Spanish. Maintain this for consistency.
- **Bootstrap UI:** Use Bootstrap 5 for layout and components. Custom styles go in versioned `css/styles.css` or global `css/`.
- **Canvas Usage:** For dynamic image generation (e.g., signatures), use `<canvas>` and JS APIs. See `firmas.html` for example.
- **Minimal External Dependencies:** Most JS/CSS libraries are included via CDN or local copies. Avoid adding new build tools unless necessary.

## Integration Points
- **Signature Generator:** `firmas.html` uses a local image (`firmas-2025.png`) and overlays user input via Canvas. JS logic is inline in the HTML file.
- **Gmail Instructions:** UI includes accordion with step-by-step guides for users to configure generated assets in Gmail.
- **PHP Data Access:** For dynamic pages, check for `db.php` and related PHP scripts for DB/file access patterns.

## Example: Adding a New Feature
1. Identify the latest version folder (e.g., `BKP V4.0DEN`).
2. Add new HTML/JS/CSS files or update existing ones.
3. For interactive features, use vanilla JS and Bootstrap for UI.
4. Test in browser; debug using dev tools and `error_log`.
5. Document any new workflow or pattern in this file.

## Key Files & Directories
- `BKP Vx.x/` — Versioned backups, each with its own assets and code
- `Proyectos/CFTdeMagallanes/Informes/pages/` — Main active development
- `css/`, `js/`, `images/` — Global assets
- `firmas.html` — Signature generator example
- `db.php` — Database connection logic (if present)

---
For questions or unclear conventions, review the latest version folder and inline comments, or update this file with new patterns as they emerge.
