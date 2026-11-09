# 🛍️ AURA Atelier — Tienda de Ropa en Línea (Frontend & Backend Desacoplados en PHP)

Plataforma de comercio electrónico de moda de alta gama estructurada en **dos aplicaciones desacopladas en PHP 8.1+** con base de datos **PostgreSQL** y una **arquitectura MVC desacoplada**: separación de **Controladores**, **Vistas** y **Modelo**, con el Modelo del backend organizado en capas de **Dominio** (entidades y contratos) e **Infraestructura** (repositorios PostgreSQL y pasarelas de pago).

---

## 🏛️ 1. Arquitectura del Proyecto

> **Resumen:** el proyecto aplica el patrón **MVC de forma desacoplada**, no un MVC monolítico de framework. Hay dos aplicaciones PHP independientes que se comunican por HTTP: un **backend** (API REST + motor de reportes) y un **frontend** (tienda web). Cada una separa Controladores, Vistas y Modelo, y el backend además organiza el Modelo en capas de Dominio e Infraestructura.

```text
proyecto_php/
├── config/env.php                    # Configuración compartida (.env)
├── vendor/                           # Autoload raíz (App\Backend + App\Frontend)
├── tests/                            # Verificación funcional de la arquitectura
├── scripts/windows/                  # Scripts de arranque, build y setup de BD
├── legacy/                           # ⚠️ Código de la versión anterior (no se ejecuta)
│
├── backend/                          # ⚙️ BACKEND (API REST, Negocio & Motor de Reportes)
│   ├── config/                       # database.php, app.php
│   ├── database/                     # migrations.sql, seed.sql
│   ├── public/
│   │   ├── index.php                 # Front Controller API y Reportes
│   │   └── uploads/                  # Fotografías de prendas
│   └── src/
│       ├── Endpoints/                # 🌐 Rutas y Controladores API REST (Salida JSON)
│       │   ├── CatalogEndpoints.php  # /api/v1/products, /api/v1/categories
│       │   ├── AuthEndpoints.php     # /api/v1/auth/login, register, user
│       │   ├── CheckoutEndpoints.php # /api/v1/checkout/process, orders
│       │   ├── SellerEndpoints.php   # /api/v1/seller/products, sales
│       │   ├── AdminEndpoints.php    # /api/v1/admin/users, roles, categories
│       │   └── ReportEndpoints.php   # /api/v1/reports/sales/data (JSON)
│       ├── Controllers/              # 🎮 Controladores de Orquestación y Reglas de Negocio
│       │   ├── ProductController.php
│       │   ├── OrderController.php
│       │   ├── AuthController.php
│       │   ├── AdminController.php
│       │   └── ReportController.php
│       ├── Views/                    # 📄 Vistas del Backend (Motor de Reportes Diseñados & CSV)
│       │   ├── Engine/ViewRenderer.php
│       │   ├── Engine/ReportViewDispatcher.php
│       │   └── Reports/
│       │       ├── sales_report.php      # Reporte con KPIs, barras, print CSS y PDF
│       │       ├── inventory_report.php  # Reporte de stock crítico
│       │       └── seller_report.php     # Rendimiento de vendedores
│       ├── Domain/                   # 🧠 MODELO: Entidades, Puertos y Value Objects
│       │   ├── Entities/             # Product, Order, Variant, Payment, User...
│       │   ├── Ports/                # Contratos de repositorios y pasarelas
│       │   ├── ValueObjects/         # OrderStatus, Role
│       │   └── Exceptions/
│       └── Infrastructure/           # 🔌 MODELO: Implementaciones concretas
│           ├── Database/             # Conexión PDO a PostgreSQL
│           ├── Repositories/         # Repositorios PostgreSQL
│           ├── Payment/              # Pasarela simulada / Stripe
│           └── Storage/              # Subida de imágenes
│
└── frontend/                         # 💻 FRONTEND (Aplicación Web Tienda en PHP)
    ├── config/api.php                # URL del Backend API
    ├── public/
    │   ├── index.php                 # Front Controller de la tienda + rutas
    │   └── assets/                   # CSS y scripts
    └── src/
        ├── Services/                 # 🔌 MODELO remoto y estado de sesión
        │   ├── BackendClient.php     # Conector HTTP/cURL que consume los Endpoints del Backend
        │   ├── CartSession.php       # Bolsa de compras interactiva
        │   └── Session.php           # Autenticación y mensajes flash
        ├── Controllers/              # 🎮 Controladores del Frontend
        │   ├── StoreController.php   # Catálogo con filtros y detalle de prenda
        │   ├── CartController.php    # Agregar, quitar y actualizar prendas
        │   ├── CheckoutController.php# Checkout con pasarela y pedidos
        │   ├── AuthController.php    # Login con 1-clic y Registro
        │   ├── SellerController.php  # Panel de vendedor y publicación
        │   └── AdminController.php   # Panel de control y redirección a reportes
        └── Views/                    # 📄 Vistas de la tienda con Tailwind CSS
            ├── layouts/main.php, admin.php
            ├── store/ (catalog, product_detail, cart, checkout, order_success, orders)
            ├── auth/ (login, register)
            ├── seller/ (dashboard, product_form, sales)
            └── admin/ (dashboard, users, categories)
```

### 1.1 ¿Cómo se aplica MVC?

**Backend** — MVC + capas de dominio:

| Rol MVC | Carpeta | Responsabilidad |
| :--- | :--- | :--- |
| **Controlador** | `backend/src/Endpoints/` | Recibe la petición HTTP y responde JSON |
| **Controlador** | `backend/src/Controllers/` | Orquesta casos de uso y reglas de negocio |
| **Modelo** | `backend/src/Domain/` | Entidades, contratos (puertos) y reglas del negocio |
| **Modelo** | `backend/src/Infrastructure/` | Repositorios PostgreSQL, pasarelas y almacenamiento |
| **Vista** | `backend/src/Views/` | Plantillas HTML de reportes + `ViewRenderer` |

**Frontend** — MVC con modelo remoto:

| Rol MVC | Carpeta | Responsabilidad |
| :--- | :--- | :--- |
| **Controlador** | `frontend/src/Controllers/` | Atiende rutas, valida y prepara datos |
| **Vista** | `frontend/src/Views/` | Plantillas PHP y layouts (Tailwind CSS) |
| **Modelo** | `frontend/src/Services/` | `BackendClient` obtiene los datos desde la API |

> El frontend **no tiene modelos locales**: su "modelo" es el backend consumido vía HTTP. Es el patrón habitual de *backend-for-frontend*.

### 1.2 Flujo de una petición

**Tienda (frontend):**
```text
Navegador → frontend/public/index.php → Controller → Services/BackendClient
          → API del backend → JSON → Controller → View + layout → HTML
```

**API (backend):**
```text
Cliente HTTP → backend/public/index.php → Endpoint → Controller
             → Domain/Ports → Infrastructure/Repositories → PostgreSQL → JSON
```

### 1.3 Código legado

La carpeta `legacy/` contiene la **primera versión hexagonal** del proyecto (`src/`, `views/`, `public/` y `config/`). Ya **no se ejecuta**: el autoload solo registra `App\Backend\` y `App\Frontend\`, y los scripts de arranque usan `backend/public` y `frontend/public`. Se conserva únicamente como referencia histórica; no agregues código ahí. Ver `legacy/README.md`.

---

## 👥 2. Roles del Sistema (RBAC)

| Rol | Correo | Contraseña | Capacidades |
| :--- | :--- | :--- | :--- |
| **👑 Administrador** | `admin@tiendaropa.com` | `admin123` | Control general, roles de usuarios, categorías y **reportes ejecutivos diseñados (HTML/PDF/A4/CSV)**. |
| **🛍️ Vendedor** | `vendedor@tiendaropa.com` | `seller123` | Publicar prendas con tallas/colores/stock, ver pedidos de sus productos y métricas. |
| **👤 Cliente** | `cliente@tiendaropa.com` | `client123` | Catálogo, filtros, bolsa de compras, checkout con pasarela de pago y seguimiento de pedidos. |

> La pantalla `/login` cuenta con botones de **Acceso Rápido con 1 Clic** para probar cualquiera de los 3 roles.

### 2.1 Sesión única por cuenta

El frontend aplica un bloqueo de **una sola sesión activa por cuenta**:

- Si la cuenta ya está abierta en un navegador, un segundo navegador **no puede iniciar sesión** con esa misma cuenta; recibe el aviso *"Esta cuenta ya tiene una sesión activa en otro navegador"*.
- Al cerrar sesión (`/logout`) la cuenta queda libre inmediatamente.
- Si el navegador se cierra sin cerrar sesión, la reserva se libera automáticamente tras `SESSION_LOCK_TTL` segundos sin actividad (**30 minutos por defecto**).
- El navegador activo renueva la reserva cada `SESSION_TOUCH_INTERVAL` segundos (**5 minutos por defecto**), por lo que una sesión en uso no expira.
- El registro se guarda en `frontend/storage/active_sessions.json` (ignorado por Git). Usa `flock` para evitar que dos inicios de sesión simultáneos reclamen la cuenta a la vez.
- En un despliegue con varios servidores frontend, ese archivo debe compartirse o sustituirse por un almacén central (Redis/PostgreSQL).

---

## 💳 3. Pasarela de Pago

- **Modo Sandbox / Simulado**: Validación de tarjetas de crédito/débito en tiempo real con códigos de autorización bancaria y referencia única.
  - Tarjeta Aprobada Visa: `4242 4242 4242 4242`
  - Tarjeta Aprobada Mastercard: `5555 5555 5555 5555`
  - Tarjeta Declinada: `4000 0000 0000 0000`
- **Stripe**: Adaptador listo para producción con `STRIPE_KEY` y `STRIPE_SECRET`.
- **Integridad Transaccional**: El pedido, las líneas de compra, la transacción y el decremento atómico del stock se ejecutan dentro de una transacción PostgreSQL.

---

## 📊 4. Reportes Ejecutivos Diseñados (Backend Views)

Ubicados en el Backend (`backend/src/Views/Reports/`), accesibles desde el panel de administración:
1. **Reporte de Ventas e Ingresos (`/reports/sales` o `/admin/reports/sales`)**:
   - Selector de fechas y filtro por categoría.
   - Tarjetas KPI: Ventas Netas Totales, Órdenes Liquidadas, Ticket Promedio y Unidades Físicas.
   - Participación porcentual por categoría y métodos de pago.
   - Top 10 prendas con mayor facturación y bitácora de transacciones.
   - **Botón "Descargar CSV"** y **Botón "Imprimir / PDF A4"**.
2. **Reporte de Inventario y Stock Crítico (`/reports/inventory`)**:
   - Umbral configurable de stock bajo (por defecto &le; 10 piezas).
   - Alerta visual destacada en rojo/ámbar para resurtido urgente.
   - Valuación monetaria en bodega a precio catálogo.
3. **Reporte de Desempeño por Vendedor (`/reports/sellers`)**:
   - Ventas brutas y volumen de órdenes por boutique o vendedor.

---

## 🚀 5. Puesta en Marcha desde Windows

Requisitos:
- **PHP 8.1+** (probado con PHP 8.2.12 de XAMPP).
- **PostgreSQL 13+** (probado con PostgreSQL 17).
- Los scripts detectan automáticamente `php.exe` y `psql.exe` en XAMPP/PostgreSQL. Si están en otra ruta, se configuran en `.env`.

La configuración local vive en **`.env`** (se crea automáticamente desde `.env.example` si no existe). Ahí se definen credenciales de PostgreSQL y puertos:

```ini
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=tienda_ropa_db
DB_USER=postgres
DB_PASS=postgres

BACKEND_HOST=127.0.0.1
BACKEND_PORT=8081
FRONTEND_HOST=127.0.0.1
FRONTEND_PORT=8000
```

> En esta máquina el puerto `8080` lo ocupa Apache de XAMPP, por eso el backend usa `8081`. Si liberas el `8080`, cambia `BACKEND_PORT=8080` en `.env`.

### 1. Preparar la base de datos

```bat
setup-db.bat
```

Crea la base de datos si no existe, aplica `backend/database/migrations.sql` y carga `backend/database/seed.sql`.

### 2. Compilar / verificar

```bat
build.bat
```

Revisa la sintaxis de todos los archivos PHP y ejecuta la verificación funcional de la arquitectura.

Para regenerar además el autoload optimizado de Composer:

```bat
build.bat -OptimizeAutoload
```

### 3. Iniciar la aplicación

```bat
start.bat
```

Esto abre dos ventanas de PowerShell (backend y frontend). Luego abre en el navegador:

**`http://localhost:8000`**

También pueden iniciarse por separado:

```bat
start.bat -Role backend
start.bat -Role frontend
```

### Alternativa manual (PowerShell)

Terminal 1 — Backend:
```powershell
Set-Location '\\wsl.localhost\Ubuntu\home\egomez\proyecto\proyecto-php'
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8081 -t backend/public backend/public/index.php
```

Terminal 2 — Frontend:
```powershell
Set-Location '\\wsl.localhost\Ubuntu\home\egomez\proyecto\proyecto-php'
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t frontend/public frontend/public/index.php
```

Las credenciales de PostgreSQL y los puertos se leen automáticamente de `.env`.

---

## 🧪 6. Pruebas Automatizadas de la Nueva Arquitectura

Para validar todos los controladores, endpoints JSON, vistas de reportes y el flujo de compra:

```bat
build.bat
```

O directamente:

```bash
php tests/verify_architecture.php
```

---

## 🔑 7. Credenciales de Prueba

| Rol | Correo | Contraseña |
| :--- | :--- | :--- |
| Administrador | `admin@tiendaropa.com` | `admin123` |
| Vendedor | `vendedor@tiendaropa.com` | `seller123` |
| Cliente | `cliente@tiendaropa.com` | `client123` |

Base de datos local por defecto:

| Dato | Valor |
| :--- | :--- |
| Host | `127.0.0.1` |
| Puerto | `5432` |
| Base | `tienda_ropa_db` |
| Usuario | `postgres` |
| Contraseña | `postgres` |
