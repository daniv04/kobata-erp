import ProductSearch from './ProductSearch';

export default function LineItems({ items, onChange }) {
  function handleAdd(product) {
    const exists = items.find(i => i.product_id === product.id);
    if (exists) {
      handleQtyChange(exists.product_id, exists.quantity + 1);
      return;
    }

    onChange([...items, {
      product_id:      product.id,
      name:            product.name,
      cabys_code:      product.cabys_code ?? '',
      quantity:        1,
      unit_price:      Number(product.sale_price),
      tax_percentage:  Number(product.tax_percentage ?? 13),
    }]);
  }

  function handleQtyChange(productId, qty) {
    const value = Math.max(1, Number(qty) || 1);
    onChange(items.map(i => i.product_id === productId ? { ...i, quantity: value } : i));
  }

  function handlePriceChange(productId, price) {
    const value = Math.max(0, Number(price) || 0);
    onChange(items.map(i => i.product_id === productId ? { ...i, unit_price: value } : i));
  }

  function handleRemove(productId) {
    onChange(items.filter(i => i.product_id !== productId));
  }

  return (
    <div className="space-y-4">
      <ProductSearch onAdd={handleAdd} />

      {items.length > 0 && (
        <div className="overflow-hidden rounded-lg ring-1 ring-black/10 dark:ring-white/10">
          {/* Table header */}
          <div className="grid grid-cols-[1fr_80px_120px_80px_100px_32px] gap-2 bg-gray-50 px-4 py-2 text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">
            <span>Producto</span>
            <span className="text-center">Cantidad</span>
            <span className="text-right">Precio unit.</span>
            <span className="text-right">IVA</span>
            <span className="text-right">Total</span>
            <span />
          </div>

          {/* Rows */}
          {items.map(item => {
            const subtotal  = item.quantity * item.unit_price;
            const taxAmount = subtotal * (item.tax_percentage / 100);
            const total     = subtotal + taxAmount;

            return (
              <div
                key={item.product_id}
                className="grid grid-cols-[1fr_80px_120px_80px_100px_32px] items-center gap-2 border-t border-gray-100 px-4 py-2.5 dark:border-white/10"
              >
                <div>
                  <p className="text-sm font-medium text-gray-950 dark:text-white">{item.name}</p>
                  {item.cabys_code && (
                    <p className="text-xs text-gray-400">CABYS: {item.cabys_code}</p>
                  )}
                </div>

                {/* Quantity */}
                <input
                  type="number"
                  min="1"
                  value={item.quantity}
                  onChange={e => handleQtyChange(item.product_id, e.target.value)}
                  className="w-full rounded-md bg-white px-2 py-1 text-center text-sm ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
                />

                {/* Unit price */}
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={item.unit_price}
                  onChange={e => handlePriceChange(item.product_id, e.target.value)}
                  className="w-full rounded-md bg-white px-2 py-1 text-right text-sm ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
                />

                <p className="text-right text-sm text-gray-500 dark:text-gray-400">
                  {item.tax_percentage}%
                </p>

                <p className="text-right text-sm font-medium text-gray-950 dark:text-white">
                  ₡{total.toLocaleString('es-CR', { minimumFractionDigits: 2 })}
                </p>

                <button
                  type="button"
                  onClick={() => handleRemove(item.product_id)}
                  className="flex items-center justify-center text-gray-300 hover:text-red-500 dark:text-gray-600 dark:hover:text-red-400"
                >
                  <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                  </svg>
                </button>
              </div>
            );
          })}
        </div>
      )}

      {items.length === 0 && (
        <p className="py-4 text-center text-sm text-gray-400 dark:text-gray-500">
          Busca y selecciona productos para agregar líneas a la factura.
        </p>
      )}
    </div>
  );
}
