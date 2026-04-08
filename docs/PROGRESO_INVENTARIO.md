# Progreso: Módulo de Inventario y Bodegas

> Documento de seguimiento para la implementación del módulo de inventario.
> Cada tarea tiene su propio commit. Marcar con ✅ al completar.

---

## Orden de implementación

| # | Tarea | Commit | Estado |
|---|-------|--------|--------|
| 3 | StockService | `feat: add StockService with adjust, reserve and releaseReservation` | ✅ |
| 1 | Corregir ProductsResource | `feat: fix and complete ProductsResource with view page` | ✅ |
| 2 | WarehouseResource + 4 Ajuste stock | `feat: add WarehouseResource with stock adjustment action` | ✅ |
| 5 | TransferResource | `feat: add TransferResource with full transfer workflow` | ✅ |
| 6 | StockMovementResource | `feat: add StockMovementResource as read-only audit log` | ✅ |

---

## Tarea 3 — StockService ⬜

**Archivo:** `app/Services/StockService.php`

### Métodos

- `adjust(productId, warehouseId, quantity, type, referenceType, referenceId, unitCost, notes, userId): StockMovement`
  - DB::transaction
  - upsert en warehouse_stocks
  - leer quantity_before, calcular quantity_after
  - lanzar excepción si quantity_after < 0
  - actualizar warehouse_stocks.quantity y updated_at
  - insertar StockMovement
  - retornar StockMovement

- `reserve(productId, warehouseId, quantity): void`
  - upsert + incrementar reserved_quantity, no negativo

- `releaseReservation(productId, warehouseId, quantity): void`
  - decrementar reserved_quantity, no negativo

---

## Tarea 1 — Corregir ProductsResource ⬜

### ProductsTable.php
- [ ] Reemplazar category_id, brand_id, supplier_id → category.name, brand.name, supplier.name
- [ ] Columnas visibles: sku, name, brand.name, category.name, sale_price, min_stock, is_active
- [ ] Columnas ocultas: description, cabys_code, purchase_price, cost_price, distributor_price
- [ ] searchable en: name, sku, barcode
- [ ] SelectFilter por category_id, brand_id, is_active

### ProductsForm.php
- [ ] sale_price: suffix('%') → prefix('₡')
- [ ] Agregar tax_percentage (Select [0,1,2,4,13], label "IVA (%)", required)
- [ ] Agregar vehicle_compatibility (Repeater: year, make, model)
- [ ] Organizar en Sections: Información general / Precios e impuestos / Control de inventario / Compatibilidad

### ViewProducts.php (nuevo)
- [ ] Infolist con campos del producto
- [ ] Sección "Stock por bodega": tabla warehouse_stocks con warehouse.name, quantity, reserved_quantity, updated_at

### ProductsResource.php
- [ ] Agregar `'view' => ViewProducts::route('/{record}')`
- [ ] Agregar ViewAction en tabla

---

## Tarea 2 — WarehouseResource ⬜

**Directorio:** `app/Filament/Resources/Warehouses/`

### WarehouseResource.php
- Model: Warehouse, label: 'Bodega', group: 'Inventario', sort: 4
- icon: Heroicon::OutlinedBuildingStorefront
- Páginas: List, Create, Edit, View

### WarehouseForm.php
- Sección "Información de la bodega": name (req), responsible_user_id (Select usuarios activos), phone, notes
- Sección "Ubicación": address (columnSpanFull), Grid 3 cols: province, canton, district
- Sección "Estado": is_active (Toggle, default true)

### WarehousesTable.php
- Columnas: name, responsibleUser.name, province, phone, is_active (IconColumn)
- Búsqueda por name, filtro por is_active
- RecordActions: ViewAction, EditAction | BulkAction: DeleteBulkAction

### ViewWarehouse.php
- Infolist con todos los campos
- Sección "Stock actual": tabla WarehouseStock donde qty > 0, columnas: product.sku, product.name, quantity, reserved_quantity
- Header: botón "Ajustar stock" (Tarea 4)

---

## Tarea 4 — Ajuste manual de stock ⬜

**En:** `app/Filament/Resources/Warehouses/Pages/ViewWarehouse.php`

### Modal campos
- product_id (Select productos activos, searchable)
- movement_type (Select: entrada / salida)
- quantity (numérico, min 0.0001)
- unit_cost (numérico, prefix '₡')
- notes (Textarea, required, "Motivo del ajuste")

### Lógica
- entrada → StockService::adjust con +quantity, type='adjustment'
- salida → StockService::adjust con -quantity, type='adjustment'
- referenceType=null, referenceId=null, userId=auth()->id()
- Notificación con nuevo stock resultante
- Redirigir para refrescar la página

---

## Tarea 5 — TransferResource ⬜

**Directorio:** `app/Filament/Resources/Transfers/`

### TransferResource.php
- Model: Transfer, label: 'Traslado', group: 'Inventario', sort: 5
- icon: Heroicon::OutlinedArrowsRightLeft
- Páginas: List, Create, View (SIN Edit)

### TransferForm.php
- from_warehouse_id, to_warehouse_id (Select bodegas activas, validar no iguales)
- notes (Textarea, nullable)
- items Repeater (min 1): product_id, quantity_requested, unit_cost, notes

### CreateTransfer.php
- handleRecordCreation():
  1. reference_number: `TRF-{AÑO}-{NNNN}`
  2. requested_by_user_id=auth()->id(), requested_at=now(), status='pending'
  3. Crear Transfer + TransferItems
  4. StockService::reserve() por cada ítem
  5. Todo en DB::transaction

### TransfersTable.php
- Columnas: reference_number, fromWarehouse.name, toWarehouse.name, requestedByUser.name, requested_at, status (Badge)
  - pending=gris, dispatched=warning, received=success, cancelled=danger
- Filtros: status, from_warehouse_id, to_warehouse_id | Orden: created_at DESC

### ViewTransfer.php
- Infolist: todos los campos del traslado
- Tabla de ítems: producto, qty_requested, qty_dispatched, qty_received, unit_cost, notes

#### Acción "Despachar" (solo si status==pending)
- Modal editable con qty_dispatched por ítem (default = qty_requested)
- DB::transaction: actualizar items → adjust(transfer_out) → releaseReservation → Transfer.status=dispatched

#### Acción "Confirmar recepción" (solo si status==dispatched)
- Modal editable con qty_received por ítem (default = qty_dispatched)
- DB::transaction: actualizar items → adjust(transfer_in) → Transfer.status=received

#### Acción "Cancelar" (solo si status==pending)
- Confirmación simple
- releaseReservation por cada ítem → Transfer.status=cancelled

---

## Tarea 6 — StockMovementResource ⬜

**Directorio:** `app/Filament/Resources/StockMovements/`

### StockMovementResource.php
- Model: StockMovement, label: 'Movimiento'/'Movimientos de stock', group: 'Inventario', sort: 6
- icon: Heroicon::OutlinedClipboardDocumentList
- Solo página List (sin Create, Edit, Delete)

### StockMovementsTable.php
- Columnas: created_at, product.name (searchable), product.sku, warehouse.name, type (Badge), quantity (con signo), quantity_before (toggleable oculto), quantity_after, unit_cost (toggleable oculto), user.name, referencia (toggleable oculto)
- Badge colores: purchase=success, transfer_in=info, transfer_out=warning, adjustment=gris, sale_out=danger, consignment_out=purple, sale_return=success
- Filtros: type, warehouse_id, product_id, rango de fechas (desde/hasta)
- Orden: created_at DESC, sin acciones

---

## Checklist final de verificación

- [ ] Crear producto con compatibilidad de vehículo
- [ ] Vista de producto muestra stock por bodega
- [ ] Crear bodega y ver stock actual
- [ ] Ajuste manual entrada → stock sube, queda en movimientos
- [ ] Ajuste manual salida → stock baja, queda en movimientos
- [ ] Crear traslado con múltiples productos → reserved_quantity sube
- [ ] Despachar traslado → quantity baja en origen, registra transfer_out
- [ ] Recibir traslado → quantity sube en destino, registra transfer_in
- [ ] Cancelar traslado pendiente → reserved_quantity baja
- [ ] Historial de movimientos muestra todos los eventos
