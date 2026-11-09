# Código legado (no usar)

Esta carpeta conserva la **primera versión del proyecto**, construida con una
arquitectura hexagonal / clean architecture:

- `src/Application`, `src/Domain`, `src/Infrastructure`, `src/Presentation`
- `views/` (vistas de la tienda)
- `public/index.php` (front controller único)
- `config/app.php`, `config/database.php`

## ¿Por qué está aquí?

El proyecto se reestructuró en **dos aplicaciones desacopladas**:

- `backend/` → API REST + motor de reportes
- `frontend/` → tienda web que consume la API

El `composer.json` de la raíz solo autoloada `App\Backend\` y `App\Frontend\`,
y `start.bat` levanta `backend/public` y `frontend/public`. Por eso este código
**ya no se ejecuta** y se movió aquí solo como referencia histórica.

## Importante

- No agregues código nuevo en esta carpeta.
- `config/env.php` de la raíz **sí se usa** (lo comparten backend y frontend);
  no debe moverse a `legacy/`.
- El código de `legacy/` no está incluido en la verificación de sintaxis de
  `build.bat`.
