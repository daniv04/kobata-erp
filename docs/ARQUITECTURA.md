# Kobata ERP — Documento de Arquitectura del Sistema

> **Estado:** En planificación
> **Última actualización:** Abril 2026
> **Cliente:** Kobata — Accesorios para vehículos 4x4
> **Desarrollador:** Daniel

---

## 1. Visión General

Kobata ERP es un sistema de gestión empresarial a medida para Kobata, empresa costarricense dedicada a la venta de accesorios para vehículos 4x4. El sistema centralizará facturación electrónica integrada con Hacienda, inventario multi-bodega, contabilidad, clientes, consignaciones y reportería.

### Contexto del negocio

- Empresa con múltiples bodegas/sucursales
- Equipo pequeño: 1–5 usuarios simultáneos
- Requiere facturación electrónica según normativa Hacienda CR
- Necesita impresión desde impresoras portátiles (térmica ESC/POS y carta/A4)
- Maneja consignaciones y créditos a clientes como flujos de negocio propios
- Conectividad online requerida; offline es deseable pero no crítico

### Principios de diseño

- **Pragmatismo sobre perfección** — sistema útil rápido, iterable
- **Código Laravel convencional** — sin over-engineering
- **Un solo repositorio** — monolito bien organizado, no microservicios
- **Filament como base del panel** — máxima velocidad de desarrollo

---

## 2. Stack Tecnológico

### Backend

| Componente | Tecnología | Justificación |
|---|---|---|
| Framework | Laravel 13 | Estable, equipo familiarizado |
| Panel admin / UI | Filament v5 | ERP-ready, PHP puro, elegante out-of-the-box |
| Base de datos | PostgreSQL | Robusta, soporte excelente en Laravel |
| Cache | Redis | Caché de reportes, sesiones, datos frecuentes |
| Colas | Laravel Queues + Redis | Emisión async de XML a Hacienda, emails, alertas vencimiento |
| Servidor web | Nginx + PHP-FPM | Estándar producción Laravel |
| PHP | 8.5 | Última versión estable |

### Frontend (incluido en Filament)

| Componente | Tecnología |
|---|---|
| UI Components | Filament v5 (Tailwind CSS v4 + Alpine.js) |
| Reactividad | Livewire 4 |
| Gráficos | Filament Widgets + ApexCharts |
| Iconos | Heroicons |
| Páginas custom | Livewire Components (consignaciones, flujos complejos) |

### Integraciones

| Integración | Detalle |
|---|---|
| Hacienda CR | Paquete Laravel propio (XML, firma digital, emisión/recepción) |
| Impresora térmica | ESC/POS via PHP (mike42/escpos-php) — tickets de venta |
| Impresora A4 portátil | PDF generado con Laravel (barryvdh/laravel-dompdf) — facturas formales |
| Email | Laravel Mail (SMTP/Mailgun) — envío de facturas a clientes |
| Códigos de barra | picqer/php-barcode-generator — generación de etiquetas |
| CABYS | Tabla local sincronizada desde Hacienda CR |

---

## 3. Arquitectura General

El sistema es un monolito Laravel con Filament como panel principal. Para un equipo de 1-5 usuarios, un monolito bien organizado es más rápido de desarrollar, más fácil de mantener y más que suficiente en rendimiento.

```
┌─────────────────────────────────────────────────────────┐
│                    NAVEGADOR / CLIENTE                   │
│              (Filament Panel — HTTPS)                    │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│                  SERVIDOR WEB (Nginx)                    │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │              Laravel 13 + Filament v5            │   │
│  │                                                  │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────────┐  │   │
│  │  │ Filament │  │  Queue   │  │  Scheduler   │  │   │
│  │  │  Panel   │  │ Workers  │  │  (cron jobs) │  │   │
│  │  └──────────┘  └────┬─────┘  └──────────────┘  │   │
│  └─────────────────────┼────────────────────────────┘   │
│                        │                                 │
│  ┌──────────┐  ┌───────▼───────┐  ┌──────────────────┐  │
│  │PostgreSQL│  │     Redis     │  │  Storage/Discos  │  │
│  │ (datos)  │  │ (cache+queue) │  │  (XML, PDFs)     │  │
│  └──────────┘  └───────────────┘  └──────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
           ┌───────────────┼───────────────┐
           │               │               │
    ┌──────▼─────┐  ┌──────▼─────┐  ┌────▼────────┐
    │  Hacienda  │  │ Impresoras │  │   Email     │
    │  CR (API)  │  │ ESC/POS+A4 │  │   (SMTP)    │
    └────────────┘  └────────────┘  └─────────────┘
```

---

## 4. Módulos del Sistema — MVP v1

### 4.1 Autenticación

- Login con email y contraseña
- Registro (controlado — solo admin puede crear usuarios)
- Recuperación de contraseña via email
- Perfil de usuario: nombre, email, cambio de contraseña, foto

### 4.2 Usuarios y Roles

Usando Filament Shield + Spatie Laravel Permission.

- CRUD de usuarios con asignación de rol
- CRUD de roles con permisos granulares por módulo o por vista
- Perfil del usuario editable
- Activar/desactivar usuarios sin eliminarlos

| Rol | Acceso |
|---|---|
| Administrador | Todo el sistema |
| Facturador | Facturación, clientes, consulta de inventario |
| Bodeguero | Inventario, bodegas, traslados, alistamientos |
| Contador | Contabilidad, reportes financieros, solo lectura en resto |
| Vendedor | Facturación, clientes, consulta de stock |

Los roles son configurables desde el panel — no están hardcodeados.

### 4.3 Inventario y Bodegas

#### Productos

- CRUD completo de productos
- Campos: SKU, nombre, descripción, categoría, marca, precio de venta, precio de costo
- Código CABYS — asignado por producto (requerido por Hacienda para facturación)
- Monto y porcentaje de impuesto — configurable por producto (13%, 4%, 2%, 1%, exento)
- Código de barras (generación automática o manual)
- Compatibilidad de vehículo: año, marca, modelo (campo JSON)
- Stock mínimo con alerta configurable
- Estado activo/inactivo

#### Bodegas

- CRUD de bodegas (nombre, dirección, responsable)
- Stock por bodega en tiempo real
- Ubicación de producto dentro de la bodega:
  - Pasillo (aisle)
  - Estante (shelf)
  - Altura / nivel (level)
- Traslados entre bodegas con documento trazable
  - Flujo: Solicitud → Despacho → Recepción confirmada
  - Genera movimientos de inventario automáticamente

#### Flujo de alistamiento y retiro (Picking & Withdrawal)

Cuando se genera una transacción que requiere sacar productos físicamente de una bodega (venta, factura, etc.), el sistema genera una orden de alistamiento para el bodeguero. Este la confirma con una guía de retiro, que es cuando el stock se descuenta definitivamente.

- Flujo: Solicitud de alistamiento → Confirmación de retiro
- Actores: sistema genera la solicitud, bodeguero confirma el retiro
- El stock **no se descuenta** hasta que el bodeguero confirma la guía de retiro
- Todo queda trazado en `stock_movements` con `type = sale_out`

#### Tipos de movimiento de inventario

| Tipo | Origen |
|---|---|
| purchase | Recepción de compras |
| sale_out | Salida confirmada por guía de retiro |
| adjustment | Ajuste manual (bodeguero/admin) |
| transfer_in | Recepción de traslado |
| transfer_out | Despacho de traslado |
| consignment_out | Salida por consignación |
| consignment_return | Devolución de consignación |
| credit_out | Salida por crédito |
| credit_return | Devolución de crédito |
| sale_return | Devolución por nota de crédito |

### 4.4 Clientes

- CRUD completo: cédula física, jurídica, DIMEX, NITE
- Validación de formato de cédula costarricense
- Exoneración o tarifa reducida por cliente
- Límite de crédito configurable
- Saldo de crédito actual (calculado desde módulo de créditos)
- Historial de facturas, consignaciones y créditos
- Búsqueda rápida por cédula o nombre (usada en facturación)

### 4.5 Facturación Electrónica

Módulo core. Integrado con el paquete Hacienda ya desarrollado.

#### Documentos soportados en MVP

| Documento | Código Hacienda |
|---|---|
| Factura Electrónica | FE |
| Tiquete Electrónico | TE |
| Recibo (interno, sin envío a Hacienda) | — |
| Nota de Crédito Electrónica | NCE |
| Nota de Débito Electrónica | NDE |

#### Flujo de emisión

```
Crear documento → Seleccionar cliente → Agregar líneas de producto
  → Validar CABYS y tarifas → Generar XML → Firmar digitalmente
  → Enviar a Hacienda (async via Queue) → Recibir confirmación
  → Generar PDF → Enviar email cliente → Disponible para impresión
```

#### Relación con inventario

Al crear una factura, el sistema genera automáticamente una **solicitud de alistamiento** para la bodega. El stock no se descuenta hasta que el bodeguero confirma la guía de retiro correspondiente.

#### Estados

`draft` → `queued` → `sent` → `accepted` / `rejected`

### 4.6 Consignaciones y Créditos

Módulo de negocio especial. Tanto consignaciones como créditos representan productos que salen del inventario hacia un cliente pero pueden volver. Se modelan de forma unificada con un campo `type`.

#### Concepto de "Ambiente Separado"

```
INVENTARIO BODEGA
      │
      ▼ consignment_out / credit_out
┌─────────────────────┐
│  AMBIENTE PENDIENTE │  ← productos "en el aire"
│  (consignación o    │
│   crédito activo)   │
└──────────┬──────────┘
           │
     ┌─────┴──────┐
     ▼            ▼
  Facturar     Devolver
  (venta       (regresa al
  definitiva)   inventario)
```

#### Consignaciones

- Crear consignación: cliente, productos, cantidades, fecha de vencimiento
- Estado: `active` → `partially_invoiced` → `fully_invoiced` / `returned` / `expired`
- Facturar desde la consignación
- Devolver ítems: regresan al inventario automáticamente
- Alerta de consignaciones próximas a vencer

#### Créditos a Clientes

- Crear crédito: cliente, productos, monto, fecha de vencimiento
- Validar que el cliente no exceda su límite de crédito
- Estado: `active` → `partially_paid` → `fully_paid` / `overdue`
- Facturar desde el crédito

### 4.7 Contabilidad

- Plan de cuentas (COA) configurable
- Asientos contables automáticos al facturar (via Observers)
- Cuentas por cobrar (derivado de facturas y créditos pendientes)
- Cuentas por pagar (derivado de compras pendientes)
- Reportes financieros básicos: Mayor General, Balance de Comprobación, Estado de Resultados, Balance General

> **Nota de alcance:** Contabilidad básica operacional. Para declaraciones de impuestos formales, el sistema exportará datos en formatos compatibles con el contador externo.

### 4.8 Reportes y Dashboard

#### Dashboard ejecutivo

- Ventas del día / semana / mes
- Productos con stock bajo o agotado
- Facturas pendientes de confirmar por Hacienda
- Consignaciones próximas a vencer
- Créditos vencidos
- Top 5 productos más vendidos
- Solicitudes de alistamiento pendientes (para bodeguero)

#### Reportes

- Ventas por período, cliente, producto, bodega, usuario
- Inventario: existencias actuales, valorizado por bodega, historial de movimientos
- Consignaciones activas, vencidas, por cliente
- Créditos: aging (0-30, 31-60, 61-90, +90 días)
- Exportación a PDF y Excel (.xlsx)

---

## 5. Base de Datos — Diseño de Tablas

### Mapa completo de tablas

```
AUTENTICACIÓN        INVENTARIO                  FACTURACIÓN
─────────────        ──────────────────────       ─────────────────
users                products                    invoices
roles                product_categories          invoice_items
permissions          brands                      hacienda_logs
                     cabys_codes                 credit_notes
CLIENTES             warehouses                  debit_notes
─────────────        warehouse_stocks
customers            warehouse_locations         CONSIGNACIONES
customer_exemptions  stock_movements             ──────────────────
                     transfers                   consignments
ALISTAMIENTO         transfer_items              consignment_items
─────────────
picking_orders       CONTABILIDAD                COMPRAS
picking_order_items  ─────────────               ──────────────
withdrawal_guides    accounts                    suppliers
withdrawal_guide_    journal_entries             purchase_orders
  items              journal_lines               purchase_items
                     fiscal_periods              purchase_receipts

CONFIG
──────────────────
company_settings
exchange_rates
```

---

### Tablas clave — estructura detallada

#### `products`

```sql
id, sku, barcode,
name, description,
category_id, brand_id,
cabys_code,              -- código CABYS de Hacienda CR (requerido para FE)
tax_percentage,          -- 0, 1, 2, 4, 13
sale_price, cost_price,
min_stock,
is_active,
vehicle_compatibility,   -- JSON: [{year, make, model}]
created_at, updated_at
```

#### `cabys_codes`

```sql
id, code,               -- código CABYS (ej: 4310601010000)
description,            -- descripción oficial
tax_percentage,         -- tarifa de IVA que sugiere Hacienda
category,
is_active,
synced_at               -- última sincronización con Hacienda
```

#### `warehouses`

```sql
id,
name,                    -- nombre de la bodega (ej: "Bodega Central", "Sucursal Norte")
address,                 -- dirección física
province, canton, district,
responsible_user_id,     -- FK a users — encargado de la bodega
phone,
notes,
is_active,
created_at, updated_at
```

#### `warehouse_locations`

```sql
id, warehouse_id, product_id,
aisle,                  -- pasillo (ej: "A", "B", "C")
shelf,                  -- estante (ej: "1", "2", "3")
level,                  -- altura/nivel (ej: "alto", "medio", "bajo")
created_at, updated_at

UNIQUE(warehouse_id, product_id)
```

#### `warehouse_stocks`

```sql
id, product_id, warehouse_id,
quantity,                -- stock disponible real
reserved_quantity,       -- reservado en consignación/crédito activo
updated_at
```

#### `stock_movements`

```sql
id, product_id, warehouse_id,
type,                    -- ver tabla de tipos en sección 4.3
quantity,                -- positivo=entrada, negativo=salida
quantity_before,         -- stock antes del movimiento
quantity_after,          -- stock después del movimiento
unit_cost,
reference_type,          -- 'invoice' | 'transfer' | 'consignment' | 'withdrawal_guide' | 'adjustment'
reference_id,            -- ID del documento origen
notes, user_id,
created_at

-- Un traslado genera SIEMPRE dos registros:
--   registro 1 → type=transfer_out, warehouse_id=bodega_origen,  quantity=-N
--   registro 2 → type=transfer_in,  warehouse_id=bodega_destino, quantity=+N
-- Una guía de retiro genera un registro:
--   registro → type=sale_out, warehouse_id=bodega_origen, quantity=-N, reference_type='withdrawal_guide'
```

#### `transfers`

```sql
id,
reference_number,        -- número legible (ej: TRF-2026-001)
from_warehouse_id,       -- FK a warehouses — bodega origen
to_warehouse_id,         -- FK a warehouses — bodega destino
requested_by_user_id,
dispatched_by_user_id,
received_by_user_id,
status,                  -- pending | dispatched | received | cancelled
notes,
requested_at,
dispatched_at,           -- aquí se registra transfer_out en stock_movements
received_at,             -- aquí se registra transfer_in en stock_movements
created_at, updated_at
```

#### `transfer_items`

```sql
id, transfer_id, product_id,
quantity_requested,
quantity_dispatched,
quantity_received,       -- puede diferir por daños en tránsito
unit_cost,
notes,
created_at, updated_at
```

#### `picking_orders` — Solicitudes de alistamiento

Generadas automáticamente al crear una factura/venta. Representan la tarea que el bodeguero debe realizar para alistar los productos.

```sql
id,
reference_number,        -- número legible (ej: ALI-2026-001)
warehouse_id,            -- FK a warehouses — bodega donde se alista
source_type,             -- tipo de origen: 'invoice' | 'manual'
source_id,               -- ID del documento origen (invoice_id, etc.)
requested_by_user_id,    -- usuario que originó la solicitud (o sistema)
assigned_to_user_id,     -- bodeguero asignado para alistar
status,                  -- pending | in_progress | ready | cancelled
notes,
requested_at,
completed_at,            -- cuando el bodeguero marca el alistamiento como listo
created_at, updated_at
```

#### `picking_order_items` — Ítems de la solicitud de alistamiento

```sql
id, picking_order_id, product_id,
quantity_requested,      -- cantidad que pide la factura/venta
quantity_picked,         -- cantidad que el bodeguero efectivamente alistó
notes,                   -- observaciones del bodeguero por ítem
created_at, updated_at
```

#### `withdrawal_guides` — Guías de retiro

Documento físico que confirma la salida real de productos de la bodega. **Aquí es donde el stock se descuenta definitivamente.**

```sql
id,
reference_number,        -- número legible (ej: GR-2026-001)
picking_order_id,        -- FK a picking_orders
warehouse_id,            -- FK a warehouses
dispatched_by_user_id,   -- bodeguero que confirma la salida física
received_by,             -- nombre de quien retira (puede ser externo — cliente, repartidor)
status,                  -- pending | dispatched | cancelled
notes,
dispatched_at,           -- momento en que se confirma la salida → descuenta stock_movements
created_at, updated_at
```

#### `withdrawal_guide_items` — Ítems de la guía de retiro

```sql
id, withdrawal_guide_id, product_id,
quantity_dispatched,     -- cantidad físicamente entregada
unit_cost,
notes,
created_at, updated_at
```

#### `customers`

```sql
id, id_type (cedula_fisica|cedula_juridica|dimex|nite|passport),
id_number,
name, trade_name,
email, phone,
address, province, canton, district,
credit_limit,
current_credit_balance,
is_active,
created_at, updated_at
```

#### `customer_exemptions`

```sql
id, customer_id,
exemption_type,
tax_percentage,          -- porcentaje exonerado (0-100)
document_number,
document_path,
valid_from, valid_until,
is_active,
created_at, updated_at
```

#### `invoices`

```sql
id, uuid,
type (FE|TE|recibo|NCE|NDE),
customer_id, warehouse_id, user_id,
consecutive_number,
hacienda_key,            -- clave numérica 50 dígitos
subtotal, discount_total, tax_amount, total,
currency (CRC|USD), exchange_rate,
status (draft|queued|sent|accepted|rejected|contingency),
xml_path, pdf_path,
hacienda_response,
hacienda_message,
sent_at, accepted_at, issued_at,
created_at, updated_at
```

#### `consignments`

```sql
id, type (consignment|credit),
reference_number,
customer_id, warehouse_id, user_id,
status (active|partially_invoiced|fully_invoiced|returned|expired|overdue),
credit_limit,            -- solo para type=credit
due_date,
notes,
created_at, updated_at
```

#### `consignment_items`

```sql
id, consignment_id, product_id,
quantity_sent,
quantity_invoiced,
quantity_returned,
unit_price, unit_cost,
status (pending|partially_invoiced|invoiced|returned),
created_at, updated_at
```

---

### Flujo de alistamiento y retiro — impacto en base de datos

```
FACTURA CREADA
  invoices (INSERT: draft)
  picking_orders (INSERT: pending) ← generado automáticamente
  picking_order_items (INSERT: quantity_requested)
  warehouse_stocks origen (UPDATE: +reserved_quantity) ← reserva preventiva

BODEGUERO ALISTA
  picking_orders (UPDATE: in_progress → ready)
  picking_order_items (UPDATE: quantity_picked)

BODEGUERO CONFIRMA RETIRO FÍSICO
  withdrawal_guides (INSERT: dispatched)
  withdrawal_guide_items (INSERT: quantity_dispatched)
  warehouse_stocks origen (UPDATE: -quantity, -reserved_quantity) ← descuento real
  stock_movements (INSERT: type=sale_out, reference_type='withdrawal_guide')
```

---

## 6. Servicios del Proyecto

| Servicio | Responsabilidad |
|---|---|
| InvoiceService | Creación y emisión de facturas |
| StockService | Movimientos de stock (compartido por todos los flujos) |
| PickingService | Generación y gestión de solicitudes de alistamiento |
| ConsignmentService | Lógica de consignaciones/créditos |
| TaxCalculatorService | IVA + exoneraciones por cliente |
| PrintService | Abstracción ESC/POS y PDF |
| HaciendaService | Wrapper del paquete Hacienda |

> **Regla clave:** Todo movimiento de `warehouse_stocks` y `stock_movements` pasa por `StockService`. Nunca modificar stock directamente desde Resources o Controllers.

---

## 7. Estructura del Proyecto

```
kobata-erp/
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── UserResource.php
│   │   │   ├── ProductResource.php
│   │   │   ├── WarehouseResource.php
│   │   │   ├── CustomerResource.php
│   │   │   ├── InvoiceResource.php
│   │   │   ├── ConsignmentResource.php
│   │   │   ├── PickingOrderResource.php
│   │   │   ├── WithdrawalGuideResource.php
│   │   │   └── TransferResource.php
│   │   ├── Pages/
│   │   │   ├── Dashboard.php
│   │   │   ├── CreateInvoice.php
│   │   │   └── ConsignmentDetail.php
│   │   └── Widgets/
│   │       ├── SalesOverviewWidget.php
│   │       ├── LowStockWidget.php
│   │       ├── PendingInvoicesWidget.php
│   │       ├── PendingPickingOrdersWidget.php
│   │       └── ExpiringConsignmentsWidget.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Warehouse.php
│   │   ├── WarehouseLocation.php
│   │   ├── WarehouseStock.php
│   │   ├── StockMovement.php
│   │   ├── Transfer.php
│   │   ├── TransferItem.php
│   │   ├── PickingOrder.php
│   │   ├── PickingOrderItem.php
│   │   ├── WithdrawalGuide.php
│   │   ├── WithdrawalGuideItem.php
│   │   ├── Customer.php
│   │   ├── CustomerExemption.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Consignment.php
│   │   ├── ConsignmentItem.php
│   │   └── CabysCode.php
│   ├── Services/
│   │   ├── InvoiceService.php
│   │   ├── StockService.php
│   │   ├── PickingService.php
│   │   ├── ConsignmentService.php
│   │   ├── TaxCalculatorService.php
│   │   ├── PrintService.php
│   │   └── HaciendaService.php
│   ├── Jobs/
│   │   ├── SendInvoiceToHacienda.php
│   │   ├── RetryFailedInvoice.php
│   │   └── CheckExpiringConsignments.php
│   └── Observers/
│       ├── InvoiceObserver.php
│       └── ConsignmentObserver.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── CabysSeeder.php
├── resources/
│   └── views/
│       ├── pdf/
│       │   ├── invoice.blade.php
│       │   ├── withdrawal-guide.blade.php
│       │   └── consignment.blade.php
│       └── thermal/
│           ├── ticket.blade.php
│           └── label.blade.php
├── routes/
│   └── web.php
└── config/
    └── kobata.php
```

---

## 8. Reglas de Negocio Importantes

### CABYS
- Hacienda publica el catálogo CABYS en su portal. Se descarga con un comando artisan.
- El código CABYS es obligatorio para emitir factura electrónica válida.

### Exoneraciones
- `TaxCalculatorService` centraliza todo el cálculo: tarifa del producto → aplica exoneración del cliente → resultado final.

### Traslados — Integridad de Stock
- Al crear: `reserved_quantity` sube en bodega origen.
- Al despachar: `quantity` y `reserved_quantity` bajan en bodega origen → se genera `transfer_out`.
- Al recibir: `quantity` sube en bodega destino → se genera `transfer_in`.

### Alistamiento y Retiro — Integridad de Stock
- Al crear factura: `reserved_quantity` sube en bodega, se genera `picking_order`.
- Al confirmar alistamiento: `picking_order` pasa a `ready`.
- Al confirmar retiro físico (guía de retiro): `quantity` y `reserved_quantity` bajan → se genera `sale_out` en `stock_movements`.

### Consignaciones — Integridad de Stock
- Crear: `quantity` baja, `reserved_quantity` sube.
- Facturar: `reserved_quantity` baja.
- Devolver: `quantity` sube, `reserved_quantity` baja.

### Cola de Hacienda
- Los envíos a Hacienda van siempre a la cola (nunca síncronos).
- Reintento con backoff exponencial (1min → 5min → 15min → 1h).
- Si falla persistente: estado `contingency`, alerta visible en dashboard.

---

## 9. Seguridad y Roles

Usando Filament Shield + Spatie Laravel Permission.

- Permisos granulares por recurso: `view`, `create`, `edit`, `delete`
- Permisos especiales: `approve_transfers`, `manage_consignments`, `view_financials`, `confirm_withdrawals`
- Roles configurables desde el panel por el administrador
- Log de acciones críticas (quién creó/modificó una factura, consignación o guía de retiro)

---

## 10. Despliegue

> ⚠️ **Estado:** Pendiente de definir.

### Opciones en evaluación

| Opción | Pros | Contras |
|---|---|---|
| VPS Hetzner + Laravel Forge | Económico (~€5-10/mes + $12 Forge), SSL automático | Vos gestionás backups |
| DigitalOcean + Forge | Datacenter en USA más cercano a CR | Algo más caro |
| Servidor local en Kobata | Sin costo mensual, funciona offline | UPS, backups externos, mantenimiento físico |

**Recomendación preliminar:** Para múltiples bodegas con acceso desde distintas ubicaciones: VPS en Hetzner + Laravel Forge.

### Stack de producción

- OS: Ubuntu 24 LTS
- Web: Nginx + PHP 8.5 + PHP-FPM
- DB: PostgreSQL
- SSL: Let's Encrypt (automático)
- Backups: Diarios automáticos a S3 o Backblaze B2
- Queue workers: Supervisor gestionando workers Redis

---

## 11. Plan de Desarrollo — Fases MVP

### Fase 1 — Fundación (Semanas 1-2)
- [ ] Proyecto Laravel 13 + Filament v5 + Filament Shield
- [ ] Migraciones core: users, roles, company_settings
- [ ] Auth: login, forgot password, perfil de usuario
- [ ] CRUD usuarios y roles con permisos
- [ ] CRUD datos de empresa y bodegas

### Fase 2 — Inventario (Semanas 3-5)
- [ ] CRUD productos con CABYS y tarifas de impuesto
- [ ] Comando artisan para carga de tabla CABYS
- [ ] Stock por bodega + ubicaciones (pasillo/estante/nivel)
- [ ] Movimientos manuales de inventario
- [ ] Traslados entre bodegas con flujo completo
- [ ] Solicitudes de alistamiento y guías de retiro

### Fase 3 — Clientes y Facturación (Semanas 6-9) ⭐
- [ ] CRUD clientes con exoneraciones
- [ ] TaxCalculatorService
- [ ] Integración paquete Hacienda + flujo async con Queue
- [ ] FE, TE, Recibo, NCE, NDE
- [ ] Generación automática de picking_order al crear factura
- [ ] Generación PDF + envío email
- [ ] Impresión ESC/POS (ticket) y A4 (factura + guía de retiro)
- [ ] Vista de documentos con filtros

### Fase 4 — Consignaciones y Créditos (Semanas 10-12)
- [ ] Modelo unificado consignments + consignment_items
- [ ] Flujo completo de consignación (crear → facturar / devolver)
- [ ] Flujo completo de crédito con límite por cliente
- [ ] Alertas de vencimiento vía Scheduler
- [ ] Página custom Filament para gestión visual

### Fase 5 — Contabilidad y Reportes (Semanas 13-16)
- [ ] Plan de cuentas y asientos automáticos (InvoiceObserver)
- [ ] Cuentas por cobrar / pagar
- [ ] Dashboard ejecutivo con widgets
- [ ] Reportes de ventas, inventario, consignaciones, créditos
- [ ] Exportación Excel y PDF

### Fase 6 — Despliegue y Go-Live (Semana 17+)
- [ ] Configurar servidor de producción
- [ ] Pruebas en sandbox Hacienda con datos reales
- [ ] Capacitación equipo Kobata
- [ ] Go-live

---

## 12. Decisiones Pendientes

| Decisión | Estado | Notas |
|---|---|---|
| Servidor de despliegue | ⏳ Pendiente | VPS vs local |
| Módulo POS táctil dedicado | ⏳ Pendiente | ¿Pantalla POS separada o Filament responsive? |
| App móvil bodegueros | ⏳ Pendiente | ¿Filament responsive es suficiente? |
| Tipo de cambio automático BCCR | ⏳ Pendiente | API BCCR pública y gratuita |
| Multi-moneda en facturas CRC/USD | ⏳ Pendiente | Hacienda lo soporta |
| Sincronización CABYS automática | ⏳ Pendiente | ¿Cron job o manual? |
| Módulo de compras / proveedores | ⏳ Fase 2 | No incluido en MVP v1 |
| Integración picking con módulo de ventas | ⏳ Pendiente | Depende de cuándo se implemente facturación |

---

## 13. Paquetes y Dependencias

```json
{
  "require": {
    "laravel/framework": "^13.0",
    "filament/filament": "^5.0",
    "filament/spatie-laravel-permissions-plugin": "^5.0",
    "bezhansalleh/filament-shield": "^5.0",
    "spatie/laravel-permission": "^6.0",
    "barryvdh/laravel-dompdf": "^2.0",
    "mike42/escpos-php": "^0.4",
    "picqer/php-barcode-generator": "^2.0",
    "maatwebsite/excel": "^3.1",
    "[paquete-hacienda-propio]": "*"
  }
}
```

---

*Documento vivo — se actualiza con cada sesión de diseño.*
