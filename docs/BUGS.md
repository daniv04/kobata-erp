# Bugs conocidos

---

## Bug 1: Error al crear producto con SKU duplicado

**Estado:** Abierto
**Fecha:** 2026-04-10
**Severidad:** Media

### Descripción

Al intentar crear un producto usando un SKU que ya existe en la base de datos, la aplicación lanza un error SQL no controlado en lugar de mostrar un mensaje de validación amigable en el formulario.

### Comportamiento actual

Se muestra un error de servidor (`SQLSTATE[23000]: Integrity constraint violation: Duplicate entry`) cuando el usuario intenta guardar un producto con un SKU ya registrado.

### Comportamiento esperado

El formulario debería mostrar un mensaje de validación inline en el campo SKU indicando que ese valor ya existe, **antes** de intentar guardar en la base de datos.

### Pasos para reproducir

1. Crear un producto con cualquier SKU (ej. `SKU-001`).
2. Intentar crear otro producto con el mismo SKU (`SKU-001`).
3. Presionar "Guardar".
4. La aplicación falla con un error de base de datos en lugar de mostrar validación en el formulario.

### Causa raíz

El campo `sku` en `ProductsForm` no tiene la regla de validación `->unique()`. La restricción de unicidad solo existe a nivel de base de datos (`sku` definido como `unique()` en la migración), pero no está validada en el formulario de Filament.

**Archivo afectado:** `app/Filament/Resources/Products/Schemas/ProductsForm.php`

```php
// Actual (sin validación de unicidad):
TextInput::make('sku')
    ->label('SKU')
    ->required(),

// Corrección esperada:
TextInput::make('sku')
    ->label('SKU')
    ->required()
    ->unique('products', 'sku', ignoreRecord: true),
```

El parámetro `ignoreRecord: true` es necesario para que la validación ignore el registro actual al editar un producto existente.

### Archivos involucrados

- `app/Filament/Resources/Products/Schemas/ProductsForm.php` — falta regla `->unique()`
- `database/migrations/2026_03_30_205208_create_products_table.php` — restricción `unique()` ya existe en BD

---

## Bug 2: Botón "Cargar stock inicial" desaparece tras ingresar stock de una sola variante

**Estado:** Abierto
**Fecha:** 2026-04-14
**Severidad:** Media

### Descripción

Cuando un producto tiene variantes y se carga el stock inicial de **una** variante, el botón "Cargar stock inicial" desaparece de la tabla de productos. Debería permanecer visible hasta que **todas** las variantes activas tengan su stock inicial cargado.

### Comportamiento actual

El botón se oculta en cuanto existe **cualquier** `StockMovement` de tipo `initial_stock` para el `product_id`, sin importar si quedan variantes sin stock inicial.

### Comportamiento esperado

- **Producto sin variantes:** el botón desaparece tras cargar el stock inicial una vez (comportamiento actual correcto).
- **Producto con variantes:** el botón debe permanecer visible hasta que cada variante activa tenga al menos un movimiento de tipo `initial_stock`.

### Pasos para reproducir

1. Crear un producto con 3 variantes (ej. Rojo, Azul, Negro).
2. En la tabla de productos, hacer clic en "Cargar stock inicial".
3. Seleccionar la variante "Rojo", ingresar cantidad y guardar.
4. El botón "Cargar stock inicial" desaparece.
5. Las variantes "Azul" y "Negro" quedan sin stock inicial y no hay forma de cargarlo desde la tabla.

### Causa raíz

La condición `->hidden()` en la acción `initialStock` solo verifica si **existe algún** movimiento de tipo `initial_stock` para el producto, sin considerar variantes individuales:

```php
// Actual (incorrecto para productos con variantes):
->hidden(fn ($record): bool => StockMovement::where('product_id', $record->id)
    ->where('type', StockMovementType::InitialStock->value)
    ->exists())
```

Debería verificar:
- Si el producto **no tiene variantes**: que exista al menos un movimiento `initial_stock` con `variant_id = null`.
- Si el producto **tiene variantes**: que **todas** las variantes activas tengan al menos un movimiento `initial_stock`.

### Archivos involucrados

- `app/Filament/Resources/Products/Tables/ProductsTable.php` — condición `->hidden()` de la acción `initialStock`

### Entorno

- Laravel v13
- Filament v5
- PHP 8.5
