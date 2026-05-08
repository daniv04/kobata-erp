import { useState } from 'react';
import ClientSearch from './invoice/ClientSearch';
import CreateClientModal from './invoice/CreateClientModal';

export default function InvoiceForm() {
  const [selectedClient, setSelectedClient] = useState(null);
  const [showCreateModal, setShowCreateModal] = useState(false);
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

    setSubmitting(true);

    try {
      const res = await fetch('/panel/facturacion', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ client_id: selectedClient.id }),
      });

      const data = await res.json();

      if (!res.ok) {
        alert(`Error: ${data.message || 'Error al enviar factura'}`);
      } else {
        alert('Factura enviada exitosamente');
        setSelectedClient(null);
      }
    } catch {
      alert('Error de conexión al enviar la factura');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="max-w-4xl mx-auto p-6">
      <form onSubmit={handleSubmit} className="space-y-8">

        <ClientSearch
          selectedClient={selectedClient}
          onSelect={setSelectedClient}
          onCreateClick={() => setShowCreateModal(true)}
        />

        <div className="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
          <button
            type="submit"
            disabled={submitting || !selectedClient}
            className="rounded-lg bg-amber-600 px-6 py-2 text-sm font-medium text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50 transition-colors"
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
