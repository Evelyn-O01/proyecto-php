# 🛍️ AURA Atelier — Tienda de Ropa en Línea

Plataforma de comercio electrónico de alta gama para moda y prendas de vestir desarrollada en **PHP 8.5** y **PostgreSQL** bajo **Arquitectura Hexagonal (Ports & Adapters)**.

---

## 🏛️ 1. Arquitectura Hexagonal

El sistema desacopla completamente el núcleo de negocio de la base de datos y la interfaz web:

```text
src/
├── Domain/                         # Núcleo de Dominio (Puro PHP)
│   ├── Entities/                   # Product, Order, User, Payment, Category, Variant, Image
│   ├── ValueObjects/               # Role, OrderStatus
│   ├── Ports/                      # Interfaces / Puertos de Salida:
│   │                               # ProductRepositoryPort, OrderRepositoryPort, 
│   │                               # PaymentGatewayPort, ReportRepositoryPort, FileStoragePort
│   └── Exceptions/                 # DomainException
│
├── Application/                    # Capa de Casos de Uso (Orquestación)
│   ├── Services/                   # CartService (Manejo de bolsa y existencias)
│   └── UseCases/                   # Catalog, Checkout, Seller, Reports, Auth
│
├── Infrastructure/                 # Adaptadores de Salida (Tecnologías Externas)
│   ├── Database/                   # PostgresConnection (Singleton PDO)
│   ├── Repositories/               # PostgresProductRepository, PostgresOrderRepository,
│   │                               # PostgresReportRepository, PostgresUserRepository
│   ├── Payment/                    # SimulatedPaymentGateway, StripePaymentGateway
│   └── Storage/                    # LocalStorageAdapter (Carga de fotografías)
│
└── Presentation/                   # Adaptadores de Entrada (Controladores y HTTP)
    ├── Router.php                  # Enrutador REST/MVC con parámetros dinámicos
    ├── Middlewares/                # SessionManager, AuthMiddleware, RoleMiddleware (RBAC)
    └── Controllers/                # Catalog, Product, Cart, Checkout, Auth, Seller, Admin, Reports
```

---

## 👥 2. Matriz de Roles y Accesos (RBAC)

La plataforma incluye 3 roles preconfigurados con credenciales de prueba listas para demostración:

| Rol | Correo | Contraseña | Capacidades Principales |
| :--- | :--- | :--- | :--- |
| **👑 Administrador** | `admin@tiendaropa.com` | `admin123` | Supervisión total, gestión de usuarios/roles, categorías y **generación de reportes ejecutivos con diseño**. |
| **🛍️ Vendedor** | `vendedor@tiendaropa.com` | `seller123` | Publicar prendas, fotos, variantes (tallas/colores), ajustar existencias y ver sus pedidos. |
| **👤 Cliente** | `cliente@tiendaropa.com` | `client123` | Explorar catálogo con filtros, ver detalles de producto, bolsa de compras, checkout con pasarela de pago. |

> **Nota:** La página de inicio de sesión (`/login`) cuenta con botones de **Acceso Rápido con 1 Clic** para alternar instantáneamente entre los 3 roles.

---

## 💳 3. Pasarela de Pago

El procesamiento de cobros implementa el puerto `PaymentGatewayPort`:
- **Simulación Bancaria**: Validación de tarjetas de crédito/débito en tiempo real (Visa, Mastercard, Amex), asignación de código de autorización y referencia bancaria única (`TXN-YYYYMMDD-...`).
  - Tarjeta Visa Aprobada: `4242 4242 4242 4242`
  - Tarjeta Mastercard Aprobada: `5555 5555 5555 5555`
  - Tarjeta Declinada: `4000 0000 0000 0000`
- **Stripe**: Adaptador listo para configurar con `STRIPE_KEY` y `STRIPE_SECRET`.
- **Integridad Transaccional**: El pedido, las líneas de compra, el cobro y el decremento atómico del stock se ejecutan dentro de una transacción PostgreSQL.

---

## 📊 4. Reportes Ejecutivos Diseñados

Ubicados en el panel de administración (`/admin/reports/...`), cuentan con maquetación de alta costura, tarjetas KPI, barras visuales de distribución y soporte nativo para impresión/exportación A4 y PDF (`window.print()`):

1. **Reporte de Ventas e Ingresos (`/admin/reports/sales`)**:
   - Selector de fechas y filtro por categoría.
   - Indicadores KPI: Total Facturado, Órdenes, Ticket Promedio y Unidades Físicas.
   - Participación porcentual por categoría de ropa.
   - Desglose por método de pago.
   - Top 10 prendas más vendidas.
   - Bitácora cronológica de transacciones.
   - Pie de firma y certificación de auditoría.
2. **Reporte de Inventario y Stock Crítico (`/admin/reports/inventory`)**:
   - Umbral configurable de stock bajo (por defecto &le; 10 piezas).
   - Bloque destacado de alertas rojas para reposición urgente por talla y color.
   - Valuación monetaria del inventario consolidado en almacén.
3. **Reporte de Desempeño por Vendedor (`/admin/reports/sellers`)**:
   - Facturación bruta, catálogo activo y volumen vendido por cada vendedor.

---

## 🚀 5. Puesta en Marcha Rápida

### Requisitos
- PHP 8.1 o superior (con extensiones `pdo_pgsql` y `pgsql`)
- PostgreSQL 14 o superior
- Composer

### 1. Base de Datos
El esquema y datos iniciales ya se encuentran aplicados en `tienda_ropa_db`. Para recrear o reinstalar manualmente:
```bash
psql -U postgres -d tienda_ropa_db -f database/migrations.sql
psql -U postgres -d tienda_ropa_db -f database/seed.sql
```

### 2. Iniciar el Servidor de Desarrollo
```bash
php -S localhost:8000 -t public
```

Visita `http://localhost:8000` en tu navegador.

---

## 🧪 6. Pruebas Automatizadas

Para validar todos los casos de uso, pasarela y consultas de reportes:
```bash
php tests/verify.php
```
