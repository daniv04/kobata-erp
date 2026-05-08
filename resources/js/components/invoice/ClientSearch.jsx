import { useState, useEffect, useRef } from 'react';

export default function ClientSearch({ selectedClient, onSelect, onCreateClick }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);
  const [noResults, setNoResults] = useState(false);
  const timeoutRef = useRef(null);
  const containerRef = useRef(null);

  useEffect(() => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);

    if (query.length < 2) {
      setResults([]);
      setNoResults(false);
      setOpen(false);
      return;
    }

    setLoading(true);
    timeoutRef.current = setTimeout(async () => {
      try {
        const res = await fetch(`/panel/clientes/search?q=${encodeURIComponent(query)}`);
        const data = await res.json();
        setResults(data);
        setNoResults(data.length === 0);
        setOpen(true);
      } catch {
        setResults([]);
        setNoResults(false);
      } finally {
        setLoading(false);
      }
    }, 300);
  }, [query]);

  useEffect(() => {
    function handleClickOutside(e) {
      if (containerRef.current && !containerRef.current.contains(e.target)) setOpen(false);
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  function handleSelect(client) {
    onSelect(client);
    setQuery('');
    setResults([]);
    setOpen(false);
    setNoResults(false);
  }

  function handleClear() {
    onSelect(null);
  }

  return (
    <div className="mb-6">
      <label className="mb-2 block text-sm font-medium text-gray-950 dark:text-white">
        Cliente <span className="text-red-500">*</span>
      </label>

      {/* Selected client card */}
      {selectedClient ? (
        <div className="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-3 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:ring-amber-700">
          <div>
            <p className="text-sm font-medium text-gray-950 dark:text-white">{selectedClient.hacienda_name}</p>
            <p className="text-xs text-gray-500 dark:text-gray-400">
              {selectedClient.id_number_type}: {selectedClient.id_number}
              {selectedClient.province && ` · ${selectedClient.province}`}
            </p>
          </div>
          <button type="button" onClick={handleClear} className="ml-4 text-xs text-gray-500 hover:text-red-500 dark:text-gray-400">
            Cambiar
          </button>
        </div>
      ) : (
        <div ref={containerRef} className="relative">
          <input
            type="text"
            value={query}
            onChange={e => setQuery(e.target.value)}
            onKeyDown={e => e.key === 'Escape' && setOpen(false)}
            placeholder="Buscar por nombre o cédula..."
            className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-black/10 focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
          />

          {open && (
            <div className="absolute left-0 right-0 top-full z-10 mt-1 overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/20">
              {loading && (
                <p className="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Buscando...</p>
              )}

              {!loading && results.map(client => (
                <button
                  key={client.id}
                  type="button"
                  onClick={() => handleSelect(client)}
                  className="block w-full border-b border-gray-100 px-4 py-2.5 text-left text-sm transition-colors last:border-0 hover:bg-amber-50 dark:border-white/10 dark:hover:bg-amber-900/20"
                >
                  <p className="font-medium text-gray-950 dark:text-white">{client.hacienda_name}</p>
                  <p className="text-xs text-gray-500 dark:text-gray-400">{client.id_number_type}: {client.id_number}</p>
                </button>
              ))}

              {!loading && noResults && (
                <div className="px-4 py-3">
                  <p className="text-sm text-gray-500 dark:text-gray-400">No se encontró ningún cliente.</p>
                  <button
                    type="button"
                    onClick={() => { setOpen(false); onCreateClick(); }}
                    className="mt-2 text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
                  >
                    + Crear cliente
                  </button>
                </div>
              )}
            </div>
          )}
        </div>
      )}

      {/* Always-visible create button when not searching */}
      {!selectedClient && query.length === 0 && (
        <button
          type="button"
          onClick={onCreateClick}
          className="mt-2 text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400"
        >
          + Crear nuevo cliente
        </button>
      )}
    </div>
  );
}
