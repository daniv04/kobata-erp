# Levantamiento de Requerimientos

Funcionalidades pendientes por implementar en Kobata ERP.

---

## 1. Actualización manual de precios al recibir mercadería

### Descripción
Cuando entra nueva mercadería mediante una compra, el usuario debe poder revisar y modificar el precio de costo y/o venta de los productos desde el formulario de aceptación de compra. Si los precios cambian, se aplican a **todas las unidades** del producto (tanto las existentes como las nuevas), no solo a las del lote que entra.

### Motivación
El negocio no trabajará con lotes ni FIFO. Los precios se manejan de forma unificada por producto: si el proveedor sube el precio, se actualiza el precio de todo el stock.

### Comportamiento esperado
- En el formulario de aceptación/recepción de compra, por cada ítem de la compra mostrar:
  - Precio de costo actual del producto (solo lectura o editable).
  - Precio de venta actual del producto (solo lectura o editable).
  - Campo para ingresar el nuevo precio de costo (prellenado con el actual).
  - Campo para ingresar el nuevo precio de venta (prellenado con el actual).
- Si el usuario modifica los campos, al confirmar la compra se actualiza el precio en el registro del producto.
- Si no los modifica, los precios quedan igual.
- El cambio aplica a todo el inventario del producto, sin distinguir por lote.

### Archivos probables involucrados
- `app/Filament/Resources/Purchases/Schemas/PurchaseForm.php`
- `app/Filament/Resources/Purchases/Pages/CreatePurchase.php` o similar donde se confirma la recepción.
- `app/Models/Product.php` (actualización de precios).

### Pendiente definir
- ¿El cambio de precio debe quedar registrado en un historial/auditoría?
- ¿Qué rol puede modificar los precios al recibir compra?

---

## 2. Código CABYS por producto

### Descripción
Cada producto debe tener asociado un código CABYS (Catálogo de Bienes y Servicios de Hacienda, Costa Rica). En el formulario de creación/edición de producto, el usuario busca por código o por nombre/descripción, ve una lista de resultados y selecciona uno. Al seleccionarlo, la tarifa de impuesto del producto se autocompleta con la tarifa asociada al código CABYS.

### Motivación
Requerimiento fiscal costarricense. Además elimina la necesidad de ingresar manualmente la tarifa de impuesto, reduciendo errores.

### Comportamiento esperado
- En `ProductForm`, agregar un campo `Select` buscable (`searchable`) que consulte la tabla `cabys_codes`.
- La búsqueda debe funcionar tanto por `code` como por `description`.
- Cada opción debe mostrar: `{código} — {descripción}`.
- Al seleccionar un CABYS, el campo `tax_rate` del producto se prellena automáticamente con el `tax_rate` del código CABYS (el usuario puede ajustarlo si fuera necesario, o dejarlo de solo lectura — definir).
- El código CABYS es **obligatorio** para crear un producto (validar en reglas).

### Enfoque técnico (decidido)
- Importar el catálogo CABYS desde el JSON existente a una tabla dedicada `cabys_codes` en la base de datos.
- **Razón**: el catálogo tiene ~25,000 entradas; cargarlo en memoria en cada request es inviable. Una tabla indexada permite búsqueda rápida y se integra naturalmente con `Select::searchable()` de Filament.

#### Estructura de la tabla `cabys_codes`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `code` | string | Único, indexado |
| `description` | text | Índice FULLTEXT para búsqueda por palabras |
| `tax_rate` | decimal(5,2) | Tarifa de impuesto (ej: 13.00, 4.00, 0.00) |
| `timestamps` | | |

#### Relación
- `products.cabys_code_id` → FK a `cabys_codes.id`.
- Ventaja de FK vs. guardar el código como string: si Hacienda actualiza la tarifa de un código, se refleja en todos los productos relacionados sin migración de datos.

#### Importación
- Crear un comando artisan (ej. `php artisan cabys:import`) que lea el JSON y haga `upsert` por `code`.
- Insertar en chunks de 1000 para rendimiento.
- Idempotente: re-ejecutable cuando Hacienda publique una versión nueva del catálogo.

### Archivos probables involucrados
- Nueva migración: `create_cabys_codes_table`.
- Nueva migración: `add_cabys_code_id_to_products_table`.
- Nuevo modelo: `app/Models/CabysCode.php`.
- Nuevo comando: `app/Console/Commands/ImportCabysCodes.php`.
- `app/Models/Product.php` (relación `cabysCode()`).
- `app/Filament/Resources/Products/Schemas/ProductForm.php` (campo Select + autocompletado de `tax_rate`).

### Pendiente definir
- ¿El campo `tax_rate` del producto queda de solo lectura tras seleccionar CABYS, o editable?
- ¿Ruta/ubicación actual del archivo JSON con los códigos CABYS?
- ¿Se migran productos existentes a códigos CABYS o solo aplica a productos nuevos?

---

## 3. Guías de retiro (alistamiento y confirmación)

### Descripción
Cuando se registra una venta, el sistema genera automáticamente una **solicitud de alistamiento** (picking order) para el bodeguero asignado a la bodega correspondiente. El bodeguero revisa la lista, va marcando cada ítem con checkbox a medida que lo alista físicamente, y al terminar confirma el retiro. Esa confirmación genera la **guía de retiro** (documento con consecutivo) y aquí es cuando el stock se descuenta definitivamente del inventario.

### Motivación
- Separar la tarea operativa del bodeguero ("qué alistar") del evento contable/de inventario ("qué salió realmente").
- Evitar que el stock se descuente al momento de la venta y que luego haya discrepancias si el producto no se alistó.
- Trazabilidad completa: quién alistó, cuándo, qué cantidad real se entregó.

### Flujo de dos etapas

```
VENTA CREADA
   ↓
1) SOLICITUD DE ALISTAMIENTO (picking_order)
   - Estado: pending → in_progress → ready
   - El bodeguero asignado a la bodega la ve en su panel
   - Marca checkbox por cada ítem conforme lo alista
   - Stock queda RESERVADO (warehouse_stocks.reserved_quantity +N) pero NO descontado
   ↓
2) GUÍA DE RETIRO (withdrawal_guide)
   - Estado: pending → dispatched
   - Consecutivo automático (ej: GR-2026-001)
   - Al confirmar: se descuenta el stock definitivamente
     (warehouse_stocks.quantity -N, reserved_quantity -N)
   - Se genera stock_movement tipo sale_out
```

### Decisiones tomadas

| Aspecto | Decisión |
|---|---|
| Interacción del bodeguero | Checkbox por ítem (no escaneo) |
| Alistamiento parcial | Permitido — si falta stock físico, se alista lo disponible y el resto se marca como **backorder** (modalidad **parcial con aviso**: se confirma lo entregado, se registra lo pendiente y se notifica al vendedor/admin; la venta no se traba esperando reposición) |
| Impresión PDF | No requerida (por ahora) |
| Consecutivo | Sí, formato tipo `GR-YYYY-NNN` |
| Quién confirma retiro | Solo bodegueros asignados a la bodega correspondiente |
| Firma del cliente | No se captura |
| Cancelación de venta | Devuelve automáticamente el stock reservado (resta `reserved_quantity` sin afectar `quantity`) |

### Manejo de backorder (modalidad: parcial con aviso)
Cuando el bodeguero alista menos de lo solicitado:
- Se registra `quantity_picked < quantity_requested` en `picking_order_items`.
- El sobrante queda en estado `backorder` — pendiente de entregar.
- **El flujo NO se traba**: el bodeguero confirma la guía con lo que tenía y la venta avanza con esa entrega parcial.
- Se **notifica automáticamente** al vendedor y al admin del faltante (notificación in-app + opcional email).
- Cuando llegue nueva mercadería, el sistema permite generar una **segunda guía de retiro** contra la misma venta para completar la entrega pendiente.
- La venta/factura original queda marcada como "entrega parcial" hasta completar el 100%, o hasta que el vendedor decida cancelar el remanente.

### Reserva de stock
- Se usa el campo existente `warehouse_stocks.reserved_quantity` (el mismo que usan traslados y consignaciones).
- La semántica es idéntica: "stock comprometido pero aún no salido físicamente".
- El desglose por tipo de reserva (venta vs. consignación vs. traslado) se calcula dinámicamente sumando desde las tablas origen (`picking_orders`, `consignments`, `transfers` activos). No se duplica en columnas separadas.

### Estructura de datos (ya delineada en ARQUITECTURA.md)

#### `picking_orders`
```
id, reference_number, source_type, source_id,
requested_by_user_id, assigned_to_user_id,
status (pending | in_progress | ready | cancelled),
notes, requested_at, completed_at, timestamps
```

#### `picking_order_items`
```
id, picking_order_id, product_id,
quantity_requested, quantity_picked,
status (pending | picked | backorder),
notes, timestamps
```

#### `withdrawal_guides`
```
id, reference_number (GR-YYYY-NNN),
picking_order_id, warehouse_id,
dispatched_by_user_id, received_by (nombre texto libre),
status (pending | dispatched | cancelled),
dispatched_at, timestamps
```

#### `withdrawal_guide_items`
```
id, withdrawal_guide_id, product_id, quantity_dispatched, timestamps
```

### Archivos probables involucrados
- Nuevas migraciones: `picking_orders`, `picking_order_items`, `withdrawal_guides`, `withdrawal_guide_items`.
- Nuevos modelos con mismos nombres.
- Nuevo servicio: `app/Services/PickingService.php` (crear picking order al registrar venta, confirmar alistamiento, generar withdrawal guide, manejar backorder).
- Nuevos Filament resources: `PickingOrderResource`, `WithdrawalGuideResource`.
- Widget: `PendingPickingOrdersWidget` para el dashboard del bodeguero.
- Hook en el servicio de ventas para generar el picking order automáticamente.
- Hook en cancelación de venta para liberar `reserved_quantity`.

### Pendiente definir
- ¿Se notifica al bodeguero (email/in-app) cuando se crea una nueva solicitud de alistamiento?
- ¿El vendedor puede ver el estado del alistamiento desde la venta?
- ¿Política exacta cuando una venta tiene backorder y pasa mucho tiempo sin reposición?

---

## 4. CRUD de Clientes con integración a API de Hacienda

### Descripción
Crear el módulo de clientes (resource de Filament) donde el formulario se integra con la API pública de Hacienda de Costa Rica. Al ingresar la cédula del cliente, el sistema consulta la API y autocompleta nombre y el/los código(s) de actividad económica. Si la API falla o no responde, el usuario puede ingresar los datos manualmente.

### Motivación
- Reducir errores de digitación en datos fiscales críticos (nombre, código de actividad).
- Agilizar el alta de clientes.
- La actividad económica es obligatoria para facturación electrónica ante Hacienda.

### Comportamiento esperado

#### Flujo normal (API responde OK)
1. Usuario ingresa tipo de identificación (física, jurídica, DIMEX, NITE) y número de cédula.
2. Al perder el foco del campo cédula (o al hacer click en un botón "Consultar Hacienda"), el sistema llama a la API.
3. La respuesta autocompleta:
   - `nombre` (solo lectura, proveniente de Hacienda).
   - `actividades_economicas` (lista con código + descripción + estado).
4. El usuario completa el resto (email, teléfono, dirección, etc.) y guarda.

#### Flujo de fallback (API falla o cédula no existe)
1. Se muestra un aviso claro: *"No se pudo consultar Hacienda. Puede ingresar los datos manualmente."*
2. El campo `nombre` pasa a ser editable.
3. El usuario puede ingresar actividad económica manualmente (con un Select buscable sobre un catálogo local de actividades económicas, o texto libre).
4. Se guarda una marca `manually_entered = true` para auditoría.

#### Cache
- Cachear respuestas de Hacienda por cédula durante cierto tiempo (ej: 24h o 7 días) para evitar llamadas redundantes y tener fallback si el API cae justo después.
- El cache se puede invalidar manualmente con un botón "Refrescar desde Hacienda".

### API de Hacienda
- **Endpoint público**: `https://api.hacienda.go.cr/fe/ae?identificacion={CEDULA}`
- **Método**: GET
- **Auth**: no requiere
- **Respuesta esperada** (resumen):
  ```json
  {
    "nombre": "...",
    "tipoIdentificacion": "01",
    "situacion": { "estado": "Inscrito", "moroso": "NO", "omiso": "NO" },
    "actividades": [
      { "estado": "A", "codigo": "552001", "tipo": "P", "descripcion": "..." }
    ]
  }
  ```
- **Consideraciones**:
  - Timeout corto (ej. 5 segundos) para no bloquear la UI.
  - Manejar 404 (cédula no registrada) distinto de 500/timeout.
  - Registrar fallos en `log` para monitoreo.

### Estructura de datos

#### Tabla `clients`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `tipo_identificacion` | enum/string | `fisica`, `juridica`, `dimex`, `nite` |
| `identificacion` | string, unique indexed | Cédula/identificación |
| `nombre` | string | Nombre o razón social |
| `email` | string nullable | |
| `telefono` | string nullable | |
| `direccion` | text nullable | |
| `provincia`, `canton`, `distrito`, `barrio` | string nullable | Para facturación electrónica |
| `situacion_tributaria` | string nullable | Inscrito / No inscrito (snapshot al momento de consulta) |
| `manually_entered` | boolean | true si datos no vinieron de Hacienda |
| `hacienda_synced_at` | timestamp nullable | Última sincronización exitosa con Hacienda |
| `timestamps` | | |

#### Tabla `client_economic_activities`
Relación N:N entre clientes y actividades económicas (un cliente puede tener varias).
| Columna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `client_id` | FK | |
| `codigo` | string | Código de actividad económica |
| `descripcion` | string | |
| `estado` | string | `A` (activa), etc. |
| `es_principal` | boolean | Marcar la actividad principal del cliente |
| `timestamps` | | |

> Nota: alternativamente, si se decide mantener un catálogo maestro de actividades económicas (similar al de CABYS), se normaliza con una tabla `economic_activities` y la tabla `client_economic_activities` solo guarda el FK. Decidir según si se necesita usar el catálogo en otros lados.

### Archivos probables involucrados
- Nuevas migraciones: `create_clients_table`, `create_client_economic_activities_table`.
- Nuevos modelos: `app/Models/Client.php`, `app/Models/ClientEconomicActivity.php`.
- Nuevo servicio: `app/Services/HaciendaApiService.php` (encapsula la llamada + cache + manejo de errores).
- Nuevo resource Filament: `app/Filament/Resources/Clients/`.
- Schema del formulario con lógica `live()` + `afterStateUpdated()` en el campo de cédula para disparar la consulta.

### Decisiones tomadas
- Fallback manual si la API falla.
- Cache de respuestas para evitar llamadas redundantes.

### Pendiente definir
- ¿Tiempo de vida del cache de respuestas (24h, 7 días)?
- ¿Se guarda un catálogo maestro de actividades económicas o solo se registran las que vienen con cada cliente?
- ¿Se sincroniza automáticamente cada cierto tiempo la info del cliente (por si cambia en Hacienda) o solo bajo demanda?
- Campos fiscales adicionales para factura electrónica (ubicación geográfica detallada): ¿entran ahora o en módulo de facturación?
