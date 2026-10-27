# 🛍️ AURA Atelier — Tienda de Ropa en Línea (Frontend & Backend Desacoplados en PHP)

Plataforma de comercio electrónico de moda de alta gama estructurada en **dos aplicaciones desacopladas en PHP 8.5** con base de datos **PostgreSQL**, con separación estricta dentro del backend entre **Vistas**, **Controladores** y **Endpoints**.

---

## 🏛️ 1. Arquitectura del Proyecto (Opción A)

```text
proyecto_php/
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
│       ├── Domain/                   # Entidades (Product, Order, Variant, Payment)
│       └── Infrastructure/           # Repositorios PostgreSQL (PDO) y Pasarelas de Pago
│
└── frontend/                         # 💻 FRONTEND (Aplicación Web Tienda en PHP)
    ├── config/api.php                # URL del Backend API (http://localhost:8080)
    ├── public/
    │   ├── index.php                 # Front Controller de la tienda
    │   └── assets/                   # CSS y scripts
    └── src/
        ├── Services/
        │   ├── BackendClient.php     # Conector HTTP/cURL que consume los Endpoints del Backend
        │   ├── CartSession.php       # Bolsa de compras interactiva
        │   └── Session.php           # Autenticación y mensajes flash
        ├── Controllers/              # Controladores del Frontend
        │   ├── StoreController.php   # Catálogo con filtros y detalle de prenda
        │   ├── CartController.php    # Agregar, quitar y actualizar prendas
        │   ├── CheckoutController.php# Checkout con pasarela y pedidos
        │   ├── AuthController.php    # Login con 1-clic y Registro
        │   ├── SellerController.php  # Panel de vendedor y publicación
        │   └── AdminController.php   # Panel de control y redirección a reportes
        └── Views/                    # Vistas de la tienda con Tailwind CSS
            ├── layouts/main.php, admin.php
            ├── store/ (catalog, product_detail, cart, checkout, order_success, orders)
            ├── auth/ (login, register)
            ├── seller/ (dashboard, product_form, sales)
            └── admin/ (dashboard, users, categories)
```

---

## 👥 2. Roles del Sistema (RBAC)

| Rol | Correo | Contraseña | Capacidades |
| :--- | :--- | :--- | :--- |
| **👑 Administrador** | `admin@tiendaropa.com` | `admin123` | Control general, roles de usuarios, categorías y **reportes ejecutivos diseñados (HTML/PDF/A4/CSV)**. |
| **🛍️ Vendedor** | `vendedor@tiendaropa.com` | `seller123` | Publicar prendas con tallas/colores/stock, ver pedidos de sus productos y métricas. |
| **👤 Cliente** | `cliente@tiendaropa.com` | `client123` | Catálogo, filtros, bolsa de compras, checkout con pasarela de pago y seguimiento de pedidos. |

> La pantalla `/login` cuenta con botones de **Acceso Rápido con 1 Clic** para probar cualquiera de los 3 roles.

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

## 🚀 5. Puesta en Marcha

### Iniciar el Backend (API REST & Motor de Reportes)
En una terminal:
```bash
php -S 127.0.0.1:8080 -t backend/public backend/public/index.php
```

### Iniciar el Frontend (Tienda Web)
En otra terminal:
```bash
php -S 127.0.0.1:8000 -t frontend/public frontend/public/index.php
```
Abre en tu navegador: **`http://localhost:8000`**

---

## 🧪 6. Pruebas Automatizadas de la Nueva Arquitectura

Para validar todos los controladores, endpoints JSON, vistas de reportes y el flujo de compra:
```bash
php tests/verify_architecture.php
```
