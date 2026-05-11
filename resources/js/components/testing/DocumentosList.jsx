import { useState, useEffect } from 'react';

const STATUS_LABELS = {
    pending:  { label: 'Pendiente',  color: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' },
    sent:     { label: 'Enviado',    color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
    accepted: { label: 'Aceptado',   color: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
    rejected: { label: 'Rechazado',  color: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
};

const TYPE_LABELS = {
    FE: 'Factura Electrónica',
    FEE: 'FE Exportación',
    FEC: 'FE Compra',
    TE: 'Tiquete',
    NC: 'Nota Crédito',
    ND: 'Nota Débito',
    REP: 'Recibo Pago',
};

function Badge({ value, map }) {
    const entry = map[value] ?? { label: value, color: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' };
    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${entry.color}`}>
            {entry.label}
        </span>
    );
}

export default function DocumentosList() {
    const [receipts, setReceipts] = useState([]);
    const [loading, setLoading]   = useState(true);
    const [error, setError]       = useState(null);

    async function load() {
        setLoading(true);
        setError(null);
        try {
            const res  = await fetch('/panel/testing/receipts');
            const data = await res.json();
            setReceipts(data);
        } catch {
            setError('No se pudieron cargar los documentos.');
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => { load(); }, []);

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <p className="text-sm text-gray-500 dark:text-gray-400">
                    {loading ? 'Cargando...' : `${receipts.length} documentos`}
                </p>
                <button
                    type="button"
                    onClick={load}
                    disabled={loading}
                    className="rounded-lg px-3 py-1.5 text-xs font-medium ring-1 ring-black/10 hover:bg-gray-50 disabled:opacity-50 dark:ring-white/20 dark:hover:bg-white/5 dark:text-gray-300"
                >
                    {loading ? 'Actualizando...' : 'Actualizar'}
                </button>
            </div>

            {error && (
                <p className="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    {error}
                </p>
            )}

            {!loading && receipts.length === 0 && !error && (
                <p className="py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                    No hay documentos enviados aún.
                </p>
            )}

            {receipts.length > 0 && (
                <div className="overflow-hidden rounded-xl ring-1 ring-black/10 dark:ring-white/10">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-gray-50 text-left text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                <th className="px-4 py-3">Consecutivo</th>
                                <th className="px-4 py-3">Tipo</th>
                                <th className="px-4 py-3">Fecha emisión</th>
                                <th className="px-4 py-3">Emisor</th>
                                <th className="px-4 py-3">Receptor</th>
                                <th className="px-4 py-3 text-right">Total</th>
                                <th className="px-4 py-3">Estado envío</th>
                                <th className="px-4 py-3">Estado Hacienda</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-white/10">
                            {receipts.map(r => (
                                <tr key={r.id} className="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td className="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">
                                        {r.consecutive_number}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge value={r.receipt_type} map={Object.fromEntries(
                                            Object.entries(TYPE_LABELS).map(([k, v]) => [k, { label: v, color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }])
                                        )} />
                                    </td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-300">{r.emission_date}</td>
                                    <td className="px-4 py-3 text-gray-950 dark:text-white">{r.emissor_name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-300">{r.receiver_name ?? '—'}</td>
                                    <td className="px-4 py-3 text-right font-medium text-gray-950 dark:text-white">
                                        {r.currency} {Number(r.total_voucher).toLocaleString('es-CR', { minimumFractionDigits: 2 })}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge value={r.receipt_status} map={STATUS_LABELS} />
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge value={r.hacienda_status} map={STATUS_LABELS} />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
