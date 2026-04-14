# TASK: Módulo de Inventario y Bodegas

> Implementar el módulo completo de inventario y bodegas para Kobata ERP.
> Este documento es la guía de referencia para el agente de desarrollo.

---

## Antes de empezar

1. Leer `docs/ARQUITECTURA.md` para entender el sistema completo.
2. Revisar los resources existentes en `app/Filament/Resources/` para respetar las convenciones del proyecto.
3. Usar `search-docs` del MCP laravel-boost antes de implementar cualquier componente de Filament.

### Convenciones del proyecto

- Cada Resource vive en su propia carpeta: `app/Filament/Resources/{Nombre}/`
- Dentro de esa carpeta: `Schemas/`, `Tables/`, `Pages/`, y el archivo `{Nombre}Resource.php`
- Labels del panel siempre en español
- Todo movimiento de stock pasa por `StockService` — nunca modificar `warehouse_stocks` directamente desde Resources o Actions
- Correr `vendor/bin/pint --dirty --format agent` después de cada bloque de cambios PHP

### Lo que ya existe (no recrear)

**Migraciones ya ejecutadas:**
- `warehouses` — bodegas
- `warehouse_stocks` — stock por producto/bodega
- `product_locations` — ubicación de producto en bodega (pasillo, estante, nivel)
- `transfers` — traslados entre bodegas
- `transfer_items` — ítems de traslado
- `stock_movements` — bitácora de movimientos

**Modelos ya creados:**
`Warehouse`, `WarehouseStock`, `ProductLocation`, `Transfer`, `TransferItem`, `StockMovement`, `Products`

**Resources ya creados:**
`ProductsResource` (básico, con errores a corregir), `CategoriesResource`, `BrandsResource`, `SuppliersResource`

---

## Tarea 1 — Corregir ProductsResource

El resource existe pero tiene errores y está incompleto.

### `app/Filament/Resources/Products/Tables/ProductsTable.php`

- Reemplazar columnas `category_id`, `supplier_id`, `brand_id` por sus relaciones: `category.name`, `brand.name`, `supplier.name`
- Columnas visibles por defecto: SKU, nombre, marca, categoría, precio de venta, stock mínimo, activo
- Columnas ocultas por defecto (`toggleable(isToggledHiddenByDefault: true)`): descripción, cabys_code, purchase_price, cost_price, distributor_price
- Agregar `->searchable()` en: nombre, SKU, barcode
- Agregar filtros: `SelectFilter` por `category_id`, `brand_id`, `is_active`

### `app/Filament/Resources/Products/Schemas/ProductsForm.php`

- Corregir `sale_price`: cambiar `->suffix('%')` por `->prefix('₡')`
- Agregar campo `tax_percentage` como `Select` con opciones `[0, 1, 2, 4, 13]`, label "IVA (%)", required
- Agregar campo `vehicle_compatibility` como `Repeater` con tres `TextInput` dentro: `year` (Año), `make` (Marca vehículo), `model` (Modelo). Se guarda como JSON.
- Organizar el formulario con `Section`:
  - Sección "Información general": sku, barcode, name, description, category_id, supplier_id, brand_id
  - Sección "Precios e impuestos": purchase_price, cost_price, distributor_price, sale_price, tax_percentage
  - Sección "Control de inventario": min_stock, is_active
  - Sección "Compatibilidad de vehículo": vehicle_compatibility (Repeater)

### Agregar página View a ProductsResource

- Crear `app/Filament/Resources/Products/Pages/ViewProducts.php`
- Registrar en `getPages()`: `'view' => ViewProducts::route('/{record}')`
- La página debe mostrar los datos del producto en un `Infolist`
- Incluir sección "Stock por bodega": tabla que muestra bodegas con stock del producto, consultando `warehouse_stocks` donde `product_id = record->id`. Columnas: nombre de bodega, cantidad disponible, cantidad reservada, última actualización.

---

## Tarea 2 — WarehouseResource

Crear resource completo para gestión de bodegas.

**Ubicación:** `app/Filament/Resources/Warehouses/`

### `WarehouseResource.php`

```
Modelo:           Warehouse
Label:            'Bodega' / plural 'Bodegas'
Navigation group: 'Inventario'
Navigation sort:  4
Navigation icon:  Heroicon::OutlinedBuildingStorefront
Record title:     'name'
Páginas:          List, Create, Edit, View
```

### `Schemas/WarehouseForm.php`

Sección "Información de la bodega":
- `name` — TextInput, required
- `responsible_user_id` — Select con usuarios activos (`User::where('is_active', true)`), searchable, nullable, label "Responsable"
- `phone` — TextInput, nullable
- `notes` — Textarea, nullable

Sección "Ubicación":
- `address` — TextInput, nullable, `columnSpanFull`
- Grid de 3 columnas: `province` (Provincia), `canton` (Cantón), `district` (Distrito), todos nullable

Sección "Estado":
- `is_active` — Toggle, default true

### `Tables/WarehousesTable.php`

Columnas: nombre, responsable (`responsibleUser.name`, label "Responsable"), provincia, teléfono, activo (IconColumn boolean).
Búsqueda por nombre.
Filtro por `is_active`.
Acciones por fila: `ViewAction`, `EditAction`.
Bulk action: `DeleteBulkAction`.

### `Pages/ViewWarehouse.php`

- Infolist con todos los campos de la bodega
- Sección "Stock actual" al final: tabla de productos con stock en esa bodega.
  - Query: `WarehouseStock::with('product')->where('warehouse_id', $record->id)->where('quantity', '>', 0)`
  - Columnas: SKU del producto, nombre del producto, cantidad disponible, cantidad reservada
  - Sin acciones (solo lectura en esta vista)
- Botón de acción en el header: "Ajustar stock" (ver Tarea 4)

---

## Tarea 3 — StockService

Crear `app/Services/StockService.php`. Es el servicio central de inventario.

### Métodos requeridos

**`adjust()`** — aplica un movimiento de stock y registra la bitácora:

```php
public function adjust(
    int $productId,
    int $warehouseId,
    float $quantity,        // positivo = entrada, negativo = salida
    string $type,           // 'adjustment' | 'purchase' | 'transfer_in' | 'transfer_out' | etc.
    string $referenceType,  // 'transfer' | 'adjustment' | null
    ?int $referenceId,
    float $unitCost,
    ?string $notes,
    int $userId
): StockMovement
```

Comportamiento:
1. Dentro de una transacción de base de datos (`DB::transaction`)
2. Hacer `upsert` en `warehouse_stocks` para asegurarse que exista el registro
3. Leer `quantity_before` del registro actual
4. Calcular `quantity_after = quantity_before + $quantity`
5. Lanzar excepción si `quantity_after < 0` (no permitir stock negativo)
6. Actualizar `warehouse_stocks.quantity = quantity_after` y `updated_at = now()`
7. Insertar en `stock_movements` con todos los datos incluyendo `quantity_before`, `quantity_after`, `created_at = now()`
8. Retornar el `StockMovement` creado

**`reserve()`** — incrementa `reserved_quantity` (no toca `quantity`):

```php
public function reserve(int $productId, int $warehouseId, float $quantity): void
```

**`releaseReservation()`** — decrementa `reserved_quantity`:

```php
public function releaseReservation(int $productId, int $warehouseId, float $quantity): void
```

Ambos métodos deben:
- Hacer `upsert` si no existe el registro en `warehouse_stocks`
- No permitir `reserved_quantity` negativa

---

## Tarea 4 — Ajuste manual de stock

Agregar acción "Ajustar stock" en la página `ViewWarehouse`.

### Modal de ajuste

Campos del formulario dentro del modal:
- `product_id` — Select de productos activos, searchable por nombre y SKU, required, label "Producto"
- `movement_type` — Select con opciones: `entrada` (label "Entrada de inventario"), `salida` (label "Salida / ajuste"). Este campo no se guarda directamente.
- `quantity` — TextInput numérico, required, min 0.0001, label "Cantidad"
- `unit_cost` — TextInput numérico, required, prefix '₡', label "Costo unitario"
- `notes` — Textarea, required, label "Motivo del ajuste"

### Al confirmar

- Si `movement_type == 'entrada'`: llamar `StockService::adjust()` con `quantity` positiva, `type = 'adjustment'`
- Si `movement_type == 'salida'`: llamar `StockService::adjust()` con `quantity` negativa, `type = 'adjustment'`
- En ambos casos: `referenceType = null`, `referenceId = null`, `userId = auth()->id()`
- Mostrar notificación de éxito indicando el nuevo stock resultante
- Refrescar la página para actualizar la tabla de stock

---

## Tarea 5 — TransferResource

Resource completo para traslados entre bodegas. Es el flujo más complejo.

**Ubicación:** `app/Filament/Resources/Transfers/`

### `TransferResource.php`

```
Modelo:           Transfer
Label:            'Traslado' / plural 'Traslados'
Navigation group: 'Inventario'
Navigation sort:  5
Navigation icon:  Heroicon::OutlinedArrowsRightLeft
Record title:     'reference_number'
Páginas:          List, Create, View  (SIN Edit — los traslados se gestionan por acciones)
```

### `Schemas/TransferForm.php`

- `from_warehouse_id` — Select de bodegas activas, required, label "Bodega origen"
- `to_warehouse_id` — Select de bodegas activas, required, label "Bodega destino"
  - Agregar validación: no puede ser igual a `from_warehouse_id`
- `notes` — Textarea, nullable
- `items` — Repeater (mínimo 1 ítem), label "Productos a trasladar", con:
  - `product_id` — Select de productos activos, searchable, required, label "Producto"
  - `quantity_requested` — TextInput numérico, required, min 0.0001, label "Cantidad"
  - `unit_cost` — TextInput numérico, required, prefix '₡', label "Costo unitario"
  - `notes` — TextInput, nullable, label "Observaciones"

### `Pages/CreateTransfer.php`

Override de `handleRecordCreation()`:
1. Generar `reference_number` automático: `TRF-{AÑO}-{NNNN}` (ej: `TRF-2026-0001`). El número secuencial se obtiene contando los transfers existentes del año actual + 1, con padding de 4 dígitos.
2. Asignar: `requested_by_user_id = auth()->id()`, `requested_at = now()`, `status = pending`
3. Crear el `Transfer`
4. Crear los `TransferItem` relacionados desde el Repeater
5. Por cada ítem: llamar `StockService::reserve($productId, $fromWarehouseId, $quantityRequested)`
6. Todo en una transacción de base de datos

### `Tables/TransfersTable.php`

Columnas:
- `reference_number` — searchable
- `fromWarehouse.name` — label "Bodega origen"
- `toWarehouse.name` — label "Bodega destino"
- `requestedByUser.name` — label "Solicitado por"
- `requested_at` — fecha, sortable, label "Fecha solicitud"
- `status` — Badge con colores:
  - `pending` → gris, label "Pendiente"
  - `dispatched` → amarillo/warning, label "Despachado"
  - `received` → verde/success, label "Recibido"
  - `cancelled` → rojo/danger, label "Cancelado"

Filtros: por `status`, por `from_warehouse_id`, por `to_warehouse_id`.
Ordenar por `created_at` descendente por defecto.
Acción por fila: `ViewAction`.

### `Pages/ViewTransfer.php`

Infolist con: reference_number, bodega origen, bodega destino, solicitado por, despachado por, recibido por, estado, notas, fechas de cada etapa.

Tabla de ítems del traslado con columnas: producto, cantidad solicitada, cantidad despachada, cantidad recibida, costo unitario, observaciones.

**Acciones en el header (condicionales por estado):**

#### Acción "Despachar" — visible solo si `status == pending`

Modal con tabla editable de ítems. Por cada ítem:
- Mostrar (readonly): nombre del producto, cantidad solicitada
- Campo editable: `quantity_dispatched` (por defecto igual a `quantity_requested`), numérico, required

Al confirmar (dentro de `DB::transaction`):
1. Actualizar `quantity_dispatched` en cada `TransferItem`
2. Por cada ítem: `StockService::adjust(productId, fromWarehouseId, -quantityDispatched, 'transfer_out', 'transfer', transferId, unitCost, notes, userId)`
3. Por cada ítem: `StockService::releaseReservation(productId, fromWarehouseId, quantityRequested)`
4. Actualizar Transfer: `status = dispatched`, `dispatched_by_user_id = auth()->id()`, `dispatched_at = now()`

#### Acción "Confirmar recepción" — visible solo si `status == dispatched`

Modal con tabla editable de ítems. Por cada ítem:
- Mostrar (readonly): nombre del producto, cantidad despachada
- Campo editable: `quantity_received` (por defecto igual a `quantity_dispatched`), numérico, required

Al confirmar (dentro de `DB::transaction`):
1. Actualizar `quantity_received` en cada `TransferItem`
2. Por cada ítem: `StockService::adjust(productId, toWarehouseId, +quantityReceived, 'transfer_in', 'transfer', transferId, unitCost, notes, userId)`
3. Actualizar Transfer: `status = received`, `received_by_user_id = auth()->id()`, `received_at = now()`

#### Acción "Cancelar" — visible solo si `status == pending`

- Confirmación simple con mensaje de advertencia
- Por cada ítem: `StockService::releaseReservation(productId, fromWarehouseId, quantityRequested)`
- Actualizar Transfer: `status = cancelled`

---

## Tarea 6 — StockMovementResource (historial de movimientos)

Resource de solo lectura para auditoría.

**Ubicación:** `app/Filament/Resources/StockMovements/`

### `StockMovementResource.php`

```
Modelo:           StockMovement
Label:            'Movimiento' / plural 'Movimientos de stock'
Navigation group: 'Inventario'
Navigation sort:  6
Navigation icon:  Heroicon::OutlinedClipboardDocumentList
Páginas:          Solo List (sin Create, Edit, Delete)
```

### `Tables/StockMovementsTable.php`

Columnas:
- `created_at` — fecha y hora, sortable, label "Fecha"
- `product.name` — label "Producto", searchable
- `product.sku` — label "SKU"
- `warehouse.name` — label "Bodega"
- `type` — Badge con colores por tipo:
  - `purchase` → success, "Compra"
  - `transfer_in` → info, "Entrada traslado"
  - `transfer_out` → warning, "Salida traslado"
  - `adjustment` → gris, "Ajuste"
  - `sale_out` → danger, "Venta"
  - `consignment_out` → purple, "Consignación"
  - `sale_return` → success, "Devolución"
- `quantity` — numérico con signo, color verde si positivo, rojo si negativo
- `quantity_before` — label "Stock anterior", toggleable oculto por defecto
- `quantity_after` — label "Stock resultante"
- `unit_cost` — monetario, toggleable oculto por defecto
- `user.name` — label "Usuario"
- `reference_type` + `reference_id` — combinar en una columna "Referencia", toggleable oculto por defecto

Filtros:
- `SelectFilter` por `type`
- `SelectFilter` por `warehouse_id`
- `SelectFilter` por `product_id` (con búsqueda)
- `DateRangeFilter` por `created_at` (o dos DateFilter: "Desde" y "Hasta")

Ordenar por `created_at` descendente.
Sin acciones de fila (solo lectura).

---

## Orden de implementación sugerido

1. **Tarea 3** — StockService (base que todo lo demás necesita)
2. **Tarea 1** — Corregir ProductsResource
3. **Tarea 2** — WarehouseResource
4. **Tarea 4** — Ajuste manual (depende de StockService y WarehouseResource)
5. **Tarea 5** — TransferResource (el más complejo, depende de StockService)
6. **Tarea 6** — StockMovementResource

---

## Verificación final

Al terminar todas las tareas, verificar manualmente que:

- [ ] Se puede crear un producto con sus precios y compatibilidad de vehículo
- [ ] La vista de producto muestra el stock por bodega
- [ ] Se puede crear una bodega y ver su stock actual
- [ ] Se puede hacer un ajuste manual de entrada y salida; el stock cambia y queda registrado en movimientos
- [ ] Se puede crear un traslado con múltiples productos
- [ ] Al crear el traslado, `reserved_quantity` sube en bodega origen
- [ ] Al despachar, `quantity` baja en bodega origen y se registra `transfer_out`
- [ ] Al recibir, `quantity` sube en bodega destino y se registra `transfer_in`
- [ ] Al cancelar un traslado pendiente, `reserved_quantity` baja
- [ ] El historial de movimientos muestra todos los eventos correctamente
