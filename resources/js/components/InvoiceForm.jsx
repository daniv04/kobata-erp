import { useState } from 'react';
import ClientSearch from './invoice/ClientSearch';
import CreateClientModal from './invoice/CreateClientModal';
import LineItems, { calcLine } from './invoice/LineItems';
import InvoiceSummary from './invoice/InvoiceSummary';
import PaymentSection from './invoice/PaymentSection';
import { ToastContainer } from './Toast';
import { useToast } from '../hooks/useToast';

export default function InvoiceForm() {
  const [selectedClient, setSelectedClient] = useState(null);
  const [exoneracion, setExoneracion] = useState(null);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [items, setItems] = useState([]);
  const [currency, setCurrency] = useState('CRC');
  const [paymentMethods, setPaymentMethods] = useState([{ type: '01', amount: '', othersDescription: '' }]);
  const [submitting, setSubmitting] = useState(false);
  const { toasts, notify, dismiss } = useToast();

  const invoiceTotal = items.reduce((sum, item) => sum + calcLine(item, exoneracion).total, 0);

  async function handleClientSelect(client) {
    setSelectedClient(client);
    setExoneracion(null);

    if (!client) return;

    try {
      const res = await fetch(`/panel/clientes/${client.id}/exoneracion-activa`, {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      });
      if (res.status === 204) {
        setExoneracion(null);
      } else if (res.ok) {
        const data = await res.json();
        setExoneracion(data);
      }
    } catch {
      // Sin exoneración si falla la consulta
    }
  }

  function handleClientCreated(client) {
    handleClientSelect(client);
    setShowCreateModal(false);
  }

  async function handleSubmit(e) {
    e.preventDefault();

    if (!selectedClient) {
      notify({ type: 'warning', title: 'Cliente requerido', body: 'Selecciona o crea un cliente antes de enviar.' });
      return;
    }

    if (items.length === 0) {
      notify({ type: 'warning', title: 'Sin productos', body: 'Agrega al menos un producto a la factura.' });
      return;
    }

    setSubmitting(true);

    const itemsPayload = items.map(item => ({
      ...item,
      exoneracion: exoneracion && item.tax_percentage > 0 ? exoneracion : null,
    }));

    try {
      const res = await fetch('/panel/facturacion', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          client_id: selectedClient.id,
          items: itemsPayload,
          currency,
          payment_methods: paymentMethods.length === 1
            ? [{ ...paymentMethods[0], amount: invoiceTotal }]
            : paymentMethods.map(m => ({ ...m, amount: parseFloat(m.amount) || 0 })),
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        const errorBody = data.errors
          ? Object.values(data.errors).flat().join('\n')
          : data.message || 'Intente de nuevo.';
        notify({ type: 'danger', title: 'Error al enviar factura', body: errorBody });
      } else {
        notify({ type: 'success', title: 'Factura enviada', body: 'La factura fue enviada exitosamente.' });
        setSelectedClient(null);
        setExoneracion(null);
        setItems([]);
        setCurrency('CRC');
        setPaymentMethods([{ type: '01', amount: '', othersDescription: '' }]);
      }
    } catch {
      notify({ type: 'danger', title: 'Error de conexión', body: 'No se pudo contactar el servidor.' });
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-6">
      <form onSubmit={handleSubmit} className="space-y-6">

        {/* ── Cliente ── */}
        <FormSection
          number="1"
          title="Cliente"
          description="Selecciona el receptor de la factura"
        >
          <ClientSearch
            selectedClient={selectedClient}
            onSelect={handleClientSelect}
            onCreateClick={() => setShowCreateModal(true)}
          />
          {exoneracion && (
            <ExoneracionBanner exoneracion={exoneracion} />
          )}
        </FormSection>

        {/* ── Productos ── */}
        <FormSection
          number="2"
          title="Productos"
          description="Busca y agrega los productos o servicios a facturar"
        >
          <LineItems items={items} onChange={setItems} exoneracion={exoneracion} />
        </FormSection>

        {/* ── Moneda y pago ── */}
        <FormSection
          number="3"
          title="Moneda y pago"
          description="Moneda de la factura y métodos de pago utilizados"
        >
          <PaymentSection
            currency={currency}
            onCurrencyChange={setCurrency}
            paymentMethods={paymentMethods}
            onPaymentMethodsChange={setPaymentMethods}
            invoiceTotal={invoiceTotal}
          />
        </FormSection>

        {/* ── Resumen ── */}
        <FormSection
          number="4"
          title="Resumen"
          description="Totales de la factura"
        >
          <InvoiceSummary items={items} currency={currency} exoneracion={exoneracion} />
        </FormSection>

        <div className="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
          <button
            type="submit"
            disabled={submitting || !selectedClient || items.length === 0}
            className="rounded-lg bg-amber-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50 transition-colors"
          >
            {submitting ? 'Enviando...' : 'Enviar Factura'}
          </button>
        </div>

      </form>

      {showCreateModal && (
        <CreateClientModal
          onCreated={handleClientCreated}
          onClose={() => setShowCreateModal(false)}
        />
      )}

      <ToastContainer toasts={toasts} onDismiss={dismiss} />
    </div>
  );
}

function ExoneracionBanner({ exoneracion }) {
  return (
    <div className="mt-3 flex items-start gap-3 rounded-lg bg-blue-50 px-4 py-3 ring-1 ring-blue-200 dark:bg-blue-900/20 dark:ring-blue-700">
      <svg className="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
        <path fillRule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clipRule="evenodd" />
      </svg>
      <div>
        <p className="text-sm font-medium text-blue-800 dark:text-blue-300">
          Exoneración activa — Tipo {exoneracion.tipo_documento} · {exoneracion.tarifa_exonerada}% exonerado
        </p>
        <p className="text-xs text-blue-600 dark:text-blue-400">
          N° {exoneracion.numero_documento} · Se aplicará a todas las líneas con IVA
        </p>
      </div>
    </div>
  );
}

function FormSection({ number, title, description, children }) {
  return (
    <div className="rounded-xl bg-gray-50 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
      <div className="flex items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-white/10">
        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">
          {number}
        </span>
        <div>
          <h2 className="text-sm font-semibold text-gray-950 dark:text-white">{title}</h2>
          {description && <p className="text-xs text-gray-500 dark:text-gray-400">{description}</p>}
        </div>
      </div>
      <div className="p-6">
        {children}
      </div>
    </div>
  );
}
