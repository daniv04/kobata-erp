import { useState, useEffect, useRef } from 'react';

async function apiFetch(url) {
  const res = await fetch(url);
  return res.json();
}

export default function ProductSearch({ onAdd }) {
  const [query, setQuery]           = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [categories, setCategories] = useState([]);
  const [results, setResults]       = useState([]);
  const [loading, setLoading]       = useState(false);
  const [open, setOpen]             = useState(false);
  const timeoutRef                  = useRef(null);
  const containerRef                = useRef(null);

  useEffect(() => {
    apiFetch('/panel/categorias').then(setCategories).catch(() => {});
  }, []);

  useEffect(() => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);

    const hasQuery    = query.length >= 2;
    const hasCategory = !!categoryId;

    if (!hasQuery && !hasCategory) {
      setResults([]);
      setOpen(false);
      return;
    }

    setLoading(true);
    timeoutRef.current = setTimeout(async () => {
      try {
        const params = new URLSearchParams();
        if (hasQuery)    params.set('q', query);
        if (hasCategory) params.set('category_id', categoryId);

        const data = await apiFetch(`/panel/productos/search?${params}`);
        setResults(data);
        setOpen(true);
      } catch {
        setResults([]);
      } finally {
        setLoading(false);
      }
    }, 300);
  }, [query, categoryId]);

  useEffect(() => {
    function handleClickOutside(e) {
      if (containerRef.current && !containerRef.current.contains(e.target)) setOpen(false);
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  function handleAdd(product) {
    onAdd(product);
    setQuery('');
    setOpen(false);
  }

  return (
    <div ref={containerRef} className="space-y-3">
      <div className="flex gap-3">
        {/* Category filter */}
        <select
          value={categoryId}
          onChange={e => setCategoryId(e.target.value)}
          className="w-44 shrink-0 rounded-lg bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
        >
          <option value="">Todas las categorías</option>
          {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
        </select>

        {/* Search input */}
        <div className="relative flex-1">
          <input
            type="text"
            value={query}
            onChange={e => setQuery(e.target.value)}
            onKeyDown={e => e.key === 'Escape' && setOpen(false)}
            placeholder="Buscar producto por nombre o SKU..."
            className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-black/10 focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
          />

          {open && (
            <div className="absolute left-0 right-0 top-full z-10 mt-1 max-h-72 overflow-y-auto rounded-lg bg-white shadow-lg ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/20">
              {loading && (
                <p className="px-4 py-3 text-sm text-gray-500">Buscando...</p>
              )}

              {!loading && results.length === 0 && (
                <p className="px-4 py-3 text-sm text-gray-500">No se encontraron productos.</p>
              )}

              {!loading && results.map(product => (
                <button
                  key={product.id}
                  type="button"
                  onClick={() => handleAdd(product)}
                  className="flex w-full items-center justify-between border-b border-gray-100 px-4 py-2.5 text-left text-sm transition-colors last:border-0 hover:bg-amber-50 dark:border-white/10 dark:hover:bg-amber-900/20"
                >
                  <div>
                    <p className="font-medium text-gray-950 dark:text-white">{product.name}</p>
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                      {product.sku && <span className="mr-2">SKU: {product.sku}</span>}
                      {product.category && <span>{product.category}</span>}
                    </p>
                  </div>
                  <div className="ml-4 text-right shrink-0">
                    <p className="font-medium text-gray-950 dark:text-white">
                      ₡{Number(product.sale_price).toLocaleString('es-CR', { minimumFractionDigits: 2 })}
                    </p>
                    <p className="text-xs text-gray-500">IVA {product.tax_percentage}%</p>
                  </div>
                </button>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
