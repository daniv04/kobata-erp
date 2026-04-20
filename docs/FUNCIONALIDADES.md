# Kobata ERP — Funcionalidades del Sistema

**Versión actual:** Inventario, Compras, Administración de Usuarios  
**Fecha:** Abril 2026

---

## Módulo de Catálogo

### Productos
- Registro completo de productos con: SKU, código de barras, nombre, descripción y código CABYS.
- Gestión de precios: precio de compra, precio de costo, precio distribuidor y precio de venta.
- Configuración de IVA por producto (0%, 1%, 2%, 4%, 13%).
- Clasificación por categoría, proveedor y marca.
- Control de stock mínimo por producto con estado activo/inactivo.
- **Compatibilidad de vehículo:** registro de año, marca y modelo de vehículo por producto (diseñado para repuestos automotrices).
- **Variantes de producto:** cada producto puede tener múltiples variantes con nombre, SKU y código de barras propios. El inventario se controla a nivel de variante cuando existen.
- Vista de detalle con el stock actual por bodega (desde la ficha del producto).
- **Carga de stock inicial:** acción desde la lista de productos para registrar el inventario inicial por bodega (y por variante si aplica). El botón se oculta automáticamente cuando todas las bodegas ya tienen stock cargado.

### Categorías
- Administración de categorías para clasificar el catálogo de productos.
- Activación / desactivación de categorías.

### Marcas
- Administración de marcas de productos.
- Activación / desactivación de marcas.

### Proveedores
- Registro de proveedores vinculados a los productos.
- Activación / desactivación de proveedores.

---

## Módulo de Bodegas e Inventario

### Bodegas
- Registro de bodegas con nombre, dirección, provincia, cantón, distrito, teléfono y notas.
- Asignación de un usuario responsable por bodega.
- Activación / desactivación de bodegas.
- Vista de inventario por bodega: detalle del stock de todos los productos en esa bodega.

### Página de Inventario
- Vista consolidada del inventario de todas las bodegas con pestañas por bodega.
- Columnas: SKU, producto, variante, categoría, bodega, cantidad total, cantidad reservada y cantidad disponible.
- La cantidad disponible se muestra con código de color: verde si hay stock, rojo si no hay.
- Filtro por categoría.
- **Ajuste manual de stock:** acción por registro para registrar entradas (+) o salidas (-) de inventario, con notas obligatorias y trazabilidad del usuario que realizó el ajuste.

---

### Compras
Flujo completo de órdenes de compra a proveedores:

| Estado | Descripción |
|--------|-------------|
| **Pendiente** | Compra registrada en espera de recepción de mercadería. |
| **Recibida** | Se confirma la recepción; el stock ingresa automáticamente a la bodega destino. |
| **Cancelada** | La compra se cancela. |

- Cada compra registra: proveedor, bodega destino, fecha de pedido, notas y número de referencia.
- Ítems con: producto, variante (si aplica), cantidad, costo unitario y notas.
- Soporte para editar compras en estado pendiente.
- Trazabilidad del usuario que creó la compra y las fechas correspondientes.

---

### Traslados entre Bodegas
Flujo completo de traslado de mercancía entre dos bodegas:

| Estado | Descripción |
|--------|-------------|
| **Pendiente** | Traslado creado y en espera de despacho. El stock queda reservado en la bodega origen. |
| **Despachado** | El encargado confirma las cantidades despachadas; el sistema descuenta el stock de la bodega origen. |
| **Recibido** | El encargado de la bodega destino confirma las cantidades recibidas; el stock ingresa a la bodega destino. |
| **Cancelado** | El traslado se cancela antes del despacho y se liberan las reservas automáticamente. |

- Cada traslado registra: bodega origen, bodega destino, productos (con variante si aplica), cantidades y notas.
- Soporte para despachar cantidades parciales por ítem.
- Trazabilidad completa: quién solicitó, quién despachó, quién recibió y las fechas correspondientes.
- Número de referencia único por traslado.

---

### Movimientos de Stock (Auditoría)
- Registro automático e inmutable de todos los movimientos de inventario.
- Tipos de movimiento registrados:
  - Recepción de compras
  - Salida confirmada por guía de retiro (venta)
  - Ajuste manual
  - Entrada por traslado
  - Salida por traslado
  - Salida por consignación
  - Devolución de consignación
  - Salida por crédito
  - Devolución de crédito
  - Devolución por nota de crédito
  - Stock inicial
- Información por movimiento: fecha, producto, variante, SKU, bodega, tipo, cantidad, stock anterior, stock resultante y usuario responsable.
- Filtros por: tipo de movimiento, bodega, producto y rango de fechas.
- Solo lectura: no se pueden crear ni editar movimientos manualmente (integridad del registro).

---

## Módulo de Usuarios y Roles

### Usuarios
- Creación y gestión de usuarios del sistema con nombre, correo y contraseña.
- Asignación de roles a cada usuario.

### Roles y Permisos
- Creación de roles personalizados (ej. Administrador, Bodeguero, Supervisor).
- Asignación granular de permisos por módulo y por acción (ver, crear, editar, eliminar).
- Control de acceso basado en roles: cada usuario solo puede acceder a las secciones y acciones que su rol permite.

---

## Características Generales del Sistema

- Interfaz web accesible desde cualquier navegador, sin necesidad de instalar software.
- Búsqueda y filtrado en todas las tablas del sistema.
- Paginación en listados con opción de ordenamiento por columna.
- Notificaciones en pantalla para confirmar acciones (guardado, errores, etc.).
- Diseño responsivo adaptado a diferentes tamaños de pantalla.
