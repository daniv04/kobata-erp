import { useState } from 'react';
import ClientSearch from './invoice/ClientSearch';
import CreateClientModal from './invoice/CreateClientModal';
import LineItems from './invoice/LineItems';
import InvoiceSummary from './invoice/InvoiceSummary';

export default function InvoiceForm() {
  const [selectedClient, setSelectedClient] = useState(null);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [items, setItems] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  function handleClientCreated(client) {
    setSelectedClient(client);
    setShowCreateModal(false);
  }

  async function handleSubmit(e) {
    e.preventDefault();

    if (!selectedClient) {
      alert('Selecciona o crea un cliente antes de enviar la factura.');
      return;
    }

    if (items.length === 0) {
      alert('Agrega al menos un producto a la factura.');
      return;
    }

    setSubmitting(true);

    try {
      const res = await fetch('/panel/facturacion', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ client_id: selectedClient.id, items }),
      });

      const data = await res.json();

      if (!res.ok) {
        alert(`Error: ${data.message || 'Error al enviar factura'}`);
      } else {
        alert('Factura enviada exitosamente');
        setSelectedClient(null);
        setItems([]);
      }
    } catch {
      alert('Error de conexión al enviar la factura');
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
            onSelect={setSelectedClient}
            onCreateClick={() => setShowCreateModal(true)}
          />
        </FormSection>

        {/* ── Productos ── */}
        <FormSection
          number="2"
          title="Productos"
          description="Busca y agrega los productos o servicios a facturar"
        >
          <LineItems items={items} onChange={setItems} />
        </FormSection>

        {/* ── Resumen ── */}
        <FormSection
          number="3"
          title="Resumen"
          description="Totales de la factura"
        >
          <InvoiceSummary items={items} />
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
    </div>
  );
}

function FormSection({ number, title, description, children }) {
  return (
    <div className="overflow-hidden rounded-xl bg-gray-50 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
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
