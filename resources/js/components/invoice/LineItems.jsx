import ProductSearch from './ProductSearch';

const DISCOUNT_TYPES = [
  { value: '01', label: 'Regalía' },
  { value: '02', label: 'Regalía/Bonificación (IVA cobrado)' },
  { value: '03', label: 'Bonificación' },
  { value: '04', label: 'Volumen' },
  { value: '05', label: 'Temporada' },
  { value: '06', label: 'Promocional' },
  { value: '07', label: 'Comercial' },
  { value: '08', label: 'Frecuencia' },
  { value: '09', label: 'Sostenido' },
  { value: '99', label: 'Otros' },
];

export function calcLine(item) {
  const subtotal        = item.quantity * item.unit_price;
  const discountAmount  = item.discount_enabled
    ? subtotal * ((item.discount_percentage ?? 0) / 100)
    : 0;
  const netSubtotal     = subtotal - discountAmount;
  const taxAmount       = netSubtotal * (item.tax_percentage / 100);
  return { subtotal, discountAmount, netSubtotal, taxAmount, total: netSubtotal + taxAmount };
}

export default function LineItems({ items, onChange }) {
  function handleAdd(product) {
    const exists = items.find(i => i.product_id === product.id);
    if (exists) {
      update(exists.product_id, { quantity: exists.quantity + 1 });
      return;
    }
    onChange([...items, {
      product_id:          product.id,
      name:                product.name,
      cabys_code:          product.cabys_code ?? '',
      quantity:            1,
      unit_price:          Number(product.sale_price),
      tax_percentage:      Number(product.tax_percentage ?? 13),
      discount_enabled:    false,
      discount_type:       '07',
      discount_percentage: 0,
    }]);
  }

  function update(productId, patch) {
    onChange(items.map(i => i.product_id === productId ? { ...i, ...patch } : i));
  }

  function handleRemove(productId) {
    onChange(items.filter(i => i.product_id !== productId));
  }

  return (
    <div className="space-y-4">
      <ProductSearch onAdd={handleAdd} />

      {items.length > 0 && (
        <div className="rounded-lg ring-1 ring-black/10 dark:ring-white/10">
          {/* Header */}
          <div className="grid grid-cols-[1fr_72px_116px_64px_100px_28px_28px] gap-2 bg-gray-50 px-4 py-2 text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">
            <span>Producto</span>
            <span>Cant.</span>
            <span>Precio unit.</span>
            <span className="text-right">IVA</span>
            <span className="text-center">Total</span>
            <span className="text-center">Desc.</span>
            <span />
          </div>

          {items.map(item => {
            const { subtotal, discountAmount, netSubtotal, taxAmount, total } = calcLine(item);
            const fmt = n => `₡${n.toLocaleString('es-CR', { minimumFractionDigits: 2 })}`;

            return (
              <div key={item.product_id} className="border-t border-gray-100 dark:border-white/10">
                {/* Main row */}
                <div className="grid grid-cols-[1fr_72px_116px_64px_100px_28px_28px] items-center gap-2 px-4 py-2.5">
                  <div>
                    <p className="text-sm font-medium text-gray-950 dark:text-white">{item.name}</p>
                    {item.cabys_code && (
                      <p className="text-xs text-gray-400">CABYS: {item.cabys_code}</p>
                    )}
                  </div>

                  <input
                    type="number" min="1" value={item.quantity}
                    onChange={e => update(item.product_id, { quantity: Math.max(1, Number(e.target.value) || 1) })}
                    className="w-full rounded-md bg-white px-2 py-1 text-center text-sm ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
                  />

                  <div className="flex items-center rounded-md ring-1 ring-black/10 focus-within:ring-2 focus-within:ring-amber-600 dark:ring-white/20 bg-white dark:bg-white/5">
                    <span className="pl-2 text-xs text-gray-400 dark:text-gray-500 select-none">₡</span>
                    <input
                      type="number" min="0" step="0.01" value={item.unit_price}
                      onChange={e => update(item.product_id, { unit_price: Math.max(0, Number(e.target.value) || 0) })}
                      className="w-full rounded-md bg-transparent py-1 pr-2 text-right text-sm focus:outline-none dark:text-white"
                    />
                  </div>

                  <p className="text-right text-sm text-gray-500 dark:text-gray-400">
                    {item.tax_percentage}%
                  </p>

                  <div className="text-right">
                    <p className="text-sm font-medium text-gray-950 dark:text-white">{fmt(total)}</p>
                    {item.discount_enabled && discountAmount > 0 && (
                      <p className="text-xs text-emerald-600 dark:text-emerald-400">-{fmt(discountAmount)}</p>
                    )}
                  </div>

                  {/* Discount toggle */}
                  <button
                    type="button"
                    title="Agregar descuento"
                    onClick={() => update(item.product_id, { discount_enabled: !item.discount_enabled })}
                    className={`flex items-center justify-center rounded text-xs font-bold transition-colors ${
                      item.discount_enabled
                        ? 'text-emerald-600 dark:text-emerald-400'
                        : 'text-gray-300 hover:text-gray-500 dark:text-gray-600 dark:hover:text-gray-400'
                    }`}
                  >
                    %
                  </button>

                  {/* Delete */}
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

                {/* Discount row */}
                {item.discount_enabled && (
                  <div className="flex items-center gap-3 border-t border-dashed border-gray-100 bg-emerald-50/50 px-4 py-2 dark:border-white/5 dark:bg-emerald-900/10">
                    <span className="shrink-0 text-xs font-medium text-emerald-700 dark:text-emerald-400">Descuento</span>

                    <select
                      value={item.discount_type}
                      onChange={e => update(item.product_id, { discount_type: e.target.value })}
                      className="flex-1 rounded-md bg-white px-2 py-1 text-xs ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                    >
                      {DISCOUNT_TYPES.map(t => (
                        <option key={t.value} value={t.value}>{t.value} — {t.label}</option>
                      ))}
                    </select>

                    <div className="flex shrink-0 items-center gap-1">
                      <input
                        type="number" min="0" max="100" step="0.01"
                        value={item.discount_percentage}
                        onChange={e => update(item.product_id, {
                          discount_percentage: Math.min(100, Math.max(0, Number(e.target.value) || 0)),
                        })}
                        className="w-20 rounded-md bg-white px-2 py-1 text-right text-xs ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                      />
                      <span className="text-xs text-gray-500 dark:text-gray-400">%</span>
                    </div>

                    {discountAmount > 0 && (
                      <div className="shrink-0 text-right text-xs text-gray-500 dark:text-gray-400">
                        <span className="text-emerald-600 dark:text-emerald-400">-{`₡${discountAmount.toLocaleString('es-CR', { minimumFractionDigits: 2 })}`}</span>
                        <span className="ml-1">sobre {`₡${subtotal.toLocaleString('es-CR', { minimumFractionDigits: 2 })}`}</span>
                      </div>
                    )}
                  </div>
                )}
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
