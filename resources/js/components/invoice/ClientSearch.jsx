import { useState, useEffect, useRef } from 'react';

/**
 * ClientSearch — Búsqueda de clientes con debounce
 *
 * Props:
 *   - onSelect: fn(clientData) ← se ejecuta cuando el usuario selecciona un cliente
 *   - onClear?: fn() ← opcional, se ejecuta cuando borra la búsqueda
 *
 * CONCEPTOS:
 * - useState: estado de query (búsqueda), results (resultados), loading
 * - useEffect: ejecuta búsqueda cuando query cambia (con debounce)
 * - useRef: almacena el timeout ID del debounce
 *
 * DEBOUNCE: espera 300ms sin que el usuario escriba antes de hacer la búsqueda.
 * Sin debounce, haríamos GET /api/clientes/search en cada tecla — mala experiencia.
 */
export default function ClientSearch({ onSelect, onClear }) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    const [showResults, setShowResults] = useState(false);
    const debounceRef = useRef(null);

    /**
     * useEffect: ejecuta búsqueda cuando `query` cambia
     * La dependencia [query] hace que el effect se re-ejecute cada vez que query cambia
     */
    useEffect(() => {
        // Limpia el timeout anterior (si el usuario sigue escribiendo, cancelamos la búsqueda anterior)
        clearTimeout(debounceRef.current);

        // Si query es muy corta, no buscar
        if (query.length < 2) {
            setResults([]);
            setShowResults(false);
            return;
        }

        // Espera 300ms antes de buscar
        debounceRef.current = setTimeout(async () => {
            setLoading(true);
            try {
                const { data } = await window.axios.get('/api/clientes/search', {
                    params: { q: query },
                });
                setResults(data);
                setShowResults(true);
            } catch (error) {
                console.error('Error buscando clientes:', error);
                setResults([]);
            } finally {
                setLoading(false);
            }
        }, 300);

        // Cleanup: si el componente se desmonta, cancela la búsqueda pendiente
        return () => clearTimeout(debounceRef.current);
    }, [query]); // Dependencia: solo ejecuta cuando query cambia

    function handleSelectClient(cliente) {
        onSelect(cliente);
        setQuery('');
        setResults([]);
        setShowResults(false);
    }

    function handleClear() {
        setQuery('');
        setResults([]);
        setShowResults(false);
        if (onClear) onClear();
    }

    return (
        <div className="relative">
            {/* Input de búsqueda */}
            <div className="flex rounded-lg bg-white shadow-sm ring-1 ring-black/10 transition duration-75 focus-within:ring-2 focus-within:ring-amber-600 dark:bg-white/5 dark:ring-white/20 dark:focus-within:ring-amber-500">
                <input
                    type="text"
                    placeholder="Buscar cliente por nombre o cédula..."
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    className="block w-full border-none bg-transparent px-3 py-2 text-sm leading-6 text-gray-950 placeholder:text-gray-400 focus:ring-0 focus:outline-none dark:text-white dark:placeholder:text-gray-500"
                />
                {query && (
                    <button
                        type="button"
                        onClick={handleClear}
                        className="px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        ✕
                    </button>
                )}
            </div>

            {/* Lista de resultados */}
            {showResults && results.length > 0 && (
                <div className="absolute top-full mt-1 w-full z-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black/10 dark:ring-white/10 border border-gray-200 dark:border-gray-700">
                    <ul className="max-h-64 overflow-y-auto">
                        {results.map((cliente) => (
                            <li key={cliente.id}>
                                <button
                                    type="button"
                                    onClick={() => handleSelectClient(cliente)}
                                    className="w-full text-left px-4 py-2.5 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition flex flex-col gap-0.5 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                >
                                    <span className="font-medium text-gray-950 dark:text-white">{cliente.hacienda_name}</span>
                                    <span className="text-xs text-gray-500 dark:text-gray-400">
                                        {cliente.id_number_type && `Tipo: ${cliente.id_number_type} · `}
                                        ID: {cliente.id_number}
                                    </span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {/* Loading indicator */}
            {loading && (
                <div className="absolute top-full mt-1 w-full z-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black/10 dark:ring-white/10 p-3">
                    <p className="text-sm text-gray-500 dark:text-gray-400">Buscando...</p>
                </div>
            )}

            {/* Sin resultados */}
            {showResults && results.length === 0 && !loading && (
                <div className="absolute top-full mt-1 w-full z-10 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black/10 dark:ring-white/10 p-3">
                    <p className="text-sm text-gray-500 dark:text-gray-400">No se encontraron clientes.</p>
                </div>
            )}
        </div>
    );
}
