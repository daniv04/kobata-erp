export default function InvoiceSummary({ items }) {
  const lines = items.map(item => {
    const subtotal  = item.quantity * item.unit_price;
    const taxAmount = subtotal * (item.tax_percentage / 100);
    return { subtotal, taxAmount, total: subtotal + taxAmount };
  });

  const subtotal  = lines.reduce((sum, l) => sum + l.subtotal, 0);
  const totalTax  = lines.reduce((sum, l) => sum + l.taxAmount, 0);
  const total     = subtotal + totalTax;

  const fmt = n => `₡${n.toLocaleString('es-CR', { minimumFractionDigits: 2 })}`;

  if (items.length === 0) {
    return (
      <p className="py-4 text-center text-sm text-gray-400 dark:text-gray-500">
        Agrega productos para ver el resumen.
      </p>
    );
  }

  return (
    <div className="ml-auto max-w-xs space-y-2">
      <Row label="Subtotal" value={fmt(subtotal)} />
      <Row label="IVA" value={fmt(totalTax)} />
      <div className="border-t border-gray-200 pt-2 dark:border-gray-700">
        <Row label="Total" value={fmt(total)} bold />
      </div>
    </div>
  );
}

function Row({ label, value, bold }) {
  return (
    <div className="flex items-center justify-between gap-8">
      <span className={`text-sm ${bold ? 'font-semibold text-gray-950 dark:text-white' : 'text-gray-500 dark:text-gray-400'}`}>
        {label}
      </span>
      <span className={`text-sm ${bold ? 'font-semibold text-gray-950 dark:text-white' : 'text-gray-700 dark:text-gray-300'}`}>
        {value}
      </span>
    </div>
  );
}
