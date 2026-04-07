# Kobata ERP — Arquitectura del Sistema

> **Estado:** En desarrollo  
> **Última actualización:** Abril 2026  
> **Cliente:** Kobata — Accesorios para vehículos 4x4 (Costa Rica)  
> **Desarrollador:** Daniel

---

## 1. Visión General

Kobata ERP es un sistema de gestión empresarial a medida para Kobata, empresa costarricense dedicada a la venta de accesorios para vehículos 4x4.

### Características del negocio

- **Múltiples bodegas/sucursales**
- **1–5 usuarios simultáneos**
- **Facturación electrónica** obligatoria según normativa Hacienda CR
- **Impresión**: térmica ESC/POS (tickets) y A4 (facturas formales)
- **Consignaciones y créditos** como flujos de negocio propios

### Stack tecnológico

| Componente | Tecnología |
|---|---|
| Framework | Laravel 11+ con Filament v3 |
| Panel admin | Filament + Shield + Spatie Permissions |
| Base de datos | PostgreSQL (actualmente MySQL en documentación original) |
| Cache/Colas | Redis |
| Facturación | Paquete Laravel propio (Hacienda CR) |
| Impresión | ESC/POS (mike42/escpos-php) + DOMPDF |

---

## 2. Módulos del Sistema

### 2.1 Autenticación y Usuarios
- Login, registro (controlado), recuperación de contraseña
- CRUD usuarios con roles
- Perfil editable

### 2.2 Roles y Permisos
- Filament Shield + Spatie Laravel Permission
- Roles configurables desde el panel
- Permisos granulares por recurso

### 2.3 Inventario y Bodegas

#### Productos
- CRUD completo
- Campos: SKU, nombre, descripción, categoría, marca, precios
- Código CABYS (requerido para facturación)
- Porcentaje de impuesto por producto
- Código de barras
- Compatibilidad de vehículo (JSON)
- Stock mínimo con alerta

#### Bodegas
- CRUD de bodegas con ubicación
- Stock por bodega en tiempo real
- Ubicaciones: pasillo, estante, nivel
- Traslados entre bodegas (flujo: solicitud → despacho → recepción)

#### Movimientos de inventario
| Tipo | Descripción |
|---|---|
| purchase | Recepción de compra |
| sale | Factura emitida |
| adjustment | Ajuste manual |
| transfer_in | Recepción de traslado |
| transfer_out | Despacho de traslado |
| consignment_out | Salida por consignación |
| consignment_return | Devolución de consignación |
| credit_out | Salida por crédito |
| credit_return | Devolución de crédito |
| sale_return | Devolución por nota de crédito |

### 2.4 Clientes
- CRUD con validación de cédulas costarricenses
- Exoneraciones: tipo, porcentaje, documento, vigencia
- Límite de crédito
- Historial de facturas, consignaciones y créditos

### 2.5 Facturación Electrónica

#### Documentos soportados
| Código | Tipo |
|---|---|
| FE | Factura Electrónica |
| TE | Tiquete Electrónico |
| NCE | Nota de Crédito Electrónica |
| NDE | Nota de Débito Electrónica |
| Recibo | Interno (sin envío a Hacienda) |

#### Flujo de emisión
1. Crear documento → Seleccionar cliente → Agregar productos
2. Validar CABYS y tarifas (producto + exoneración cliente)
3. Generar XML → Firmar digitalmente
4. Enviar a Hacienda (async via Queue)
5. Recibir confirmación → Generar PDF → Enviar email

#### Estados
`draft` → `queued` → `sent` → `accepted` / `rejected`

### 2.6 Consignaciones y Créditos

Sistema unificado con campo `type`:

#### Consignaciones
- Crear: cliente, productos, cantidades, fecha vencimiento
- Estados: `active` → `partially_invoiced` → `fully_invoiced` / `returned` / `expired`
- Facturar desde consignación
- Devolver ítems (regresa al inventario)
- Alertas de vencimiento

#### Créditos a Clientes
- Crear: cliente, productos, monto, fecha vencimiento
- Validar límite de crédito del cliente
- Estados: `active` → `partially_paid` → `fully_paid` / `overdue`
- Facturar desde crédito

### 2.7 Contabilidad
- Plan de cuentas (COA) configurable
- Asientos automáticos al facturar (Observers)
- Cuentas por cobrar / pagar
- Reportes: Mayor General, Balance de Comprobación, Estado de Resultados, Balance General

### 2.8 Reportes y Dashboard

#### Dashboard
- Ventas del día/semana/mes
- Stock bajo/agotado
- Facturas pendientes de Hacienda
- Consignaciones próximas a vencer
- Créditos vencidos
- Top 5 productos más vendidos

#### Reportes
- Ventas por período, cliente, producto, bodega, usuario
- Inventario: existencias, valorizado, historial de movimientos
- Consignaciones y créditos
- Exportación PDF y Excel

---

## 3. Estructura de Base de Datos

### Tablas principales

```
users, roles, permissions
products, product_categories, brands, cabys_codes
warehouses, warehouse_locations, warehouse_stock, stock_movements
transfers, transfer_items
customers, customer_exemptions
invoices, invoice_items, hacienda_logs
consignments, consignment_items
accounts, journal_entries, journal_lines
suppliers, purchase_orders, purchase_items
company_settings, exchange_rates
```

---

## 4. Servicios del Proyecto

| Servicio | Responsabilidad |
|---|---|
| InvoiceService | Creación y emisión de facturas |
| InventoryService | Movimientos de stock |
| ConsignmentService | Lógica de consignaciones/créditos |
| TaxCalculatorService | IVA + exoneraciones por cliente |
| PrintService | Abstracción ESC/POS y PDF |
| HaciendaService | Wrapper del paquete Hacienda |

---

## 5. Reglas de Negocio Importantes

### CABYS
- Código obligatorio para facturación electrónica
- Se carga via comando artisan desde catálogo Hacienda

### Exoneraciones
- `TaxCalculatorService` centraliza el cálculo:
  - Tarifa del producto → aplica exoneración del cliente → resultado final

### Consignaciones — Integridad de Stock
- Crear: `quantity` baja, `reserved_quantity` sube
- Facturar: `reserved_quantity` baja
- Devolver: `quantity` sube, `reserved_quantity` baja

### Cola de Hacienda
- Siempre async (nunca síncrono)
- Reintento con backoff exponencial
- Si falla persistente: estado `contingency`

---

## 6. Notas de Implementación

- Monolito Laravel bien organizado (no microservicios)
- Filament como base del panel
- Código Laravel convencional, sin over-engineering
- Todo movimiento de stock via Services, nunca directamente en Resources

---

*Este documento se actualiza conforme evoluciona el proyecto.*