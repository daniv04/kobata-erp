import { calcLine } from './LineItems';
import { CURRENCIES } from './PaymentSection';

export default function InvoiceSummary({ items, currency = 'CRC' }) {
  const selectedCurrency = CURRENCIES.find(c => c.value === currency) ?? CURRENCIES[0];
  if (items.length === 0) {
    return (
      <p className="py-4 text-center text-sm text-gray-400 dark:text-gray-500">
        Agrega productos para ver el resumen.
      </p>
    );
  }

  const lines         = items.map(calcLine);
  const subtotal      = lines.reduce((sum, l) => sum + l.subtotal, 0);
  const totalDiscount = lines.reduce((sum, l) => sum + l.discountAmount, 0);
  const netSubtotal   = lines.reduce((sum, l) => sum + l.netSubtotal, 0);
  const totalTax      = lines.reduce((sum, l) => sum + l.taxAmount, 0);
  const total         = lines.reduce((sum, l) => sum + l.total, 0);

  const fmt = n => `${selectedCurrency.symbol}${n.toLocaleString('es-CR', { minimumFractionDigits: 2 })}`;

  return (
    <div className="ml-auto max-w-xs space-y-2">
      <Row label="Subtotal" value={fmt(subtotal)} />

      {totalDiscount > 0 && (
        <Row
          label="Descuentos"
          value={`-${fmt(totalDiscount)}`}
          className="text-emerald-600 dark:text-emerald-400"
        />
      )}

      {totalDiscount > 0 && (
        <Row label="Subtotal con descuento" value={fmt(netSubtotal)} muted />
      )}

      <Row label="IVA" value={fmt(totalTax)} />

      <div className="border-t border-gray-200 pt-2 dark:border-gray-700">
        <Row label="Total" value={fmt(total)} bold />
      </div>
    </div>
  );
}

function Row({ label, value, bold, muted, className }) {
  return (
    <div className="flex items-center justify-between gap-8">
      <span className={`text-sm ${bold ? 'font-semibold text-gray-950 dark:text-white' : muted ? 'text-gray-400 dark:text-gray-500' : 'text-gray-500 dark:text-gray-400'}`}>
        {label}
      </span>
      <span className={`text-sm ${bold ? 'font-semibold text-gray-950 dark:text-white' : className ?? 'text-gray-700 dark:text-gray-300'}`}>
        {value}
      </span>
    </div>
  );
}
