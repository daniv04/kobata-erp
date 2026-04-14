# Kobata ERP — Funcionalidades del Sistema

**Versión actual:** Módulo de Inventario + Administración de Usuarios  
**Fecha:** Abril 2026

---

## Módulo de Inventario

### Productos
- Registro completo de productos con: SKU, código de barras, nombre, descripción y código CABYS.
- Gestión de precios: precio de compra, precio de costo, precio distribuidor y precio de venta.
- Configuración de IVA por producto (0%, 1%, 2%, 4%, 13%).
- Clasificación por categoría, proveedor y marca.
- Control de stock mínimo por producto con estado activo/inactivo.
- **Compatibilidad de vehículo:** registro de año, marca y modelo de vehículo por producto (diseñado para repuestos automotrices).
- Vista de detalle con el stock actual por bodega (desde la ficha del producto).

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

### Bodegas
- Registro de bodegas con nombre, dirección, provincia, cantón, distrito, teléfono y notas.
- Asignación de un usuario responsable por bodega.
- Activación / desactivación de bodegas.
- Vista de inventario por bodega: detalle del stock de todos los productos en esa bodega.
- **Ajuste manual de stock:** acción para registrar entradas o salidas de inventario en una bodega, con motivo del ajuste, costo unitario y trazabilidad del usuario que realizó el movimiento.

---

### Traslados entre Bodegas
Flujo completo de traslado de mercancía entre dos bodegas:

| Estado | Descripción |
|--------|-------------|
| **Pendiente** | Traslado creado y en espera de despacho. El stock queda reservado en la bodega origen. |
| **Despachado** | El encargado confirma las cantidades despachadas; el sistema descuenta el stock de la bodega origen. |
| **Recibido** | El encargado de la bodega destino confirma las cantidades recibidas; el stock ingresa a la bodega destino. |
| **Cancelado** | El traslado se cancela antes del despacho y se liberan las reservas automáticamente. |

- Cada traslado registra: bodega origen, bodega destino, productos, cantidades y notas.
- Soporte para despachar cantidades parciales por ítem.
- Trazabilidad completa: quién solicitó, quién despachó, quién recibió y las fechas correspondientes.
- Número de referencia único por traslado.

---

### Movimientos de Stock (Auditoría)
- Registro automático e inmutable de todos los movimientos de inventario.
- Tipos de movimiento registrados: compra, entrada por traslado, salida por traslado, ajuste, venta, consignación y devolución.
- Información por movimiento: fecha, producto, SKU, bodega, tipo, cantidad, stock anterior, stock resultante, costo unitario y usuario responsable.
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
