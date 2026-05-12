import FormSelect from '../FormSelect';
import FormInput from '../FormInput';

export const CURRENCIES = [
  { value: 'CRC', label: 'Colón costarricense (CRC)', symbol: '₡', exchangeRate: 1 },
  { value: 'USD', label: 'Dólar estadounidense (USD)', symbol: '$', exchangeRate: 500 },
  { value: 'EUR', label: 'Euro (EUR)', symbol: '€', exchangeRate: 600 },
];

const PAYMENT_METHODS = [
  { value: '01', label: 'Efectivo' },
  { value: '02', label: 'Tarjeta' },
  { value: '03', label: 'Cheque' },
  { value: '04', label: 'Transferencia / depósito bancario' },
  { value: '05', label: 'Recaudado por terceros' },
  { value: '06', label: 'SINPE Móvil' },
  { value: '07', label: 'Plataforma digital' },
  { value: '99', label: 'Otros' },
];

export default function PaymentSection({ currency, onCurrencyChange, paymentMethods, onPaymentMethodsChange, invoiceTotal }) {
  const selectedCurrency = CURRENCIES.find(c => c.value === currency) ?? CURRENCIES[0];
  const isMultiple = paymentMethods.length > 1;
  const canAddMore = paymentMethods.length < 4;

  const allocatedTotal = paymentMethods.reduce((sum, m) => sum + (parseFloat(m.amount) || 0), 0);
  const isBalanced = Math.abs(allocatedTotal - invoiceTotal) < 0.01;

  function addMethod() {
    onPaymentMethodsChange([...paymentMethods, { type: '01', amount: '', othersDescription: '' }]);
  }

  function removeMethod(index) {
    onPaymentMethodsChange(paymentMethods.filter((_, i) => i !== index));
  }

  function updateMethod(index, field, value) {
    onPaymentMethodsChange(paymentMethods.map((m, i) => i === index ? { ...m, [field]: value } : m));
  }

  const fmtAmount = n => `${selectedCurrency.symbol}${n.toLocaleString('es-CR', { minimumFractionDigits: 2 })}`;

  return (
    <div className="space-y-6">

      {/* Moneda */}
      <div className="grid grid-cols-2 gap-4">
        <FormSelect
          label="Moneda"
          id="currency"
          value={currency}
          onChange={onCurrencyChange}
          options={CURRENCIES.map(c => ({ value: c.value, label: c.label }))}
          required
        />
        <div>
          <label className="block mb-2 text-sm font-medium text-gray-950 dark:text-white">
            Tipo de cambio
          </label>
          <div className="flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-500 ring-1 ring-black/10 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
            {selectedCurrency.exchangeRate === 1
              ? 'Moneda local — sin conversión'
              : `1 ${selectedCurrency.value} = ₡${selectedCurrency.exchangeRate.toLocaleString('es-CR')}.00`}
          </div>
        </div>
      </div>

      {/* Métodos de pago */}
      <div className="space-y-3">
        <div className="flex items-center justify-between">
          <span className="text-sm font-medium text-gray-950 dark:text-white">Métodos de pago</span>
          {canAddMore && (
            <button
              type="button"
              onClick={addMethod}
              className="text-xs font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
            >
              + Agregar método
            </button>
          )}
        </div>

        {paymentMethods.map((method, index) => (
          <div key={index} className="flex items-end gap-3">

            <div className={method.type === '99' ? 'w-40 shrink-0' : 'flex-1'}>
              <FormSelect
                label={index === 0 ? 'Tipo' : undefined}
                id={`payment-type-${index}`}
                value={method.type}
                onChange={(val) => updateMethod(index, 'type', val)}
                options={PAYMENT_METHODS}
              />
            </div>

            {method.type === '99' && (
              <div className="flex-1">
                <FormInput
                  label={index === 0 ? 'Descripción' : undefined}
                  id={`payment-others-${index}`}
                  value={method.othersDescription}
                  onChange={(val) => updateMethod(index, 'othersDescription', val)}
                  placeholder="Mínimo 3 caracteres"
                />
              </div>
            )}

            <div className="w-40 shrink-0">
              {isMultiple ? (
                <FormInput
                  label={index === 0 ? `Monto (${selectedCurrency.symbol})` : undefined}
                  id={`payment-amount-${index}`}
                  type="number"
                  value={method.amount}
                  onChange={(val) => updateMethod(index, 'amount', val)}
                  placeholder="0.00"
                />
              ) : (
                <div>
                  {index === 0 && (
                    <label className="block mb-2 text-sm font-medium text-gray-950 dark:text-white">
                      Monto
                    </label>
                  )}
                  <div className="flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-500 ring-1 ring-black/10 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                    {invoiceTotal > 0 ? fmtAmount(invoiceTotal) : '—'}
                  </div>
                </div>
              )}
            </div>

            {paymentMethods.length > 1 && (
              <button
                type="button"
                onClick={() => removeMethod(index)}
                className="mb-0.5 shrink-0 rounded-lg p-2 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                aria-label="Eliminar método"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </button>
            )}
          </div>
        ))}

        {isMultiple && invoiceTotal > 0 && (
          <div className={`flex items-center justify-between rounded-lg px-3 py-2 text-xs ring-1 ${isBalanced ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-800' : 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-800'}`}>
            <span>{isBalanced ? 'Métodos balanceados' : 'Los métodos deben sumar el total de la factura'}</span>
            <span className="font-medium tabular-nums">
              {fmtAmount(allocatedTotal)} / {fmtAmount(invoiceTotal)}
            </span>
          </div>
        )}
      </div>
    </div>
  );
}
