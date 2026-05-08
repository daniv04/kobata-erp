import { useState, useEffect } from 'react';

const TIPOS_ID = [
  { value: '01', label: 'Cédula Física' },
  { value: '02', label: 'Cédula Jurídica' },
  { value: '03', label: 'DIMEX' },
  { value: '04', label: 'NITE' },
];

const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content;

async function apiFetch(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error('Error cargando datos');
  return res.json();
}

export default function CreateClientModal({ onCreated, onClose }) {
  const [form, setForm] = useState({
    id_number_type: '02',
    id_number: '',
    hacienda_name: '',
    email: '',
    phone: '',
    province_id: '',
    canton_id: '',
    district_id: '',
    neighborhood_id: '',
    address: '',
  });

  const [provinces, setProvinces] = useState([]);
  const [cantons, setCantons] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [neighborhoods, setNeighborhoods] = useState([]);
  const [errors, setErrors] = useState({});
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    apiFetch('/panel/ubicacion/provincias').then(setProvinces).catch(() => {});
  }, []);

  useEffect(() => {
    if (!form.province_id) { setCantons([]); return; }
    apiFetch(`/panel/ubicacion/cantones?province_id=${form.province_id}`).then(setCantons).catch(() => {});
  }, [form.province_id]);

  useEffect(() => {
    if (!form.canton_id) { setDistricts([]); return; }
    apiFetch(`/panel/ubicacion/distritos?canton_id=${form.canton_id}`).then(setDistricts).catch(() => {});
  }, [form.canton_id]);

  useEffect(() => {
    if (!form.district_id) { setNeighborhoods([]); return; }
    apiFetch(`/panel/ubicacion/barrios?district_id=${form.district_id}`).then(setNeighborhoods).catch(() => {});
  }, [form.district_id]);

  function set(field, value) {
    setForm(prev => {
      const next = { ...prev, [field]: value };
      if (field === 'province_id') { next.canton_id = ''; next.district_id = ''; next.neighborhood_id = ''; }
      if (field === 'canton_id')   { next.district_id = ''; next.neighborhood_id = ''; }
      if (field === 'district_id') { next.neighborhood_id = ''; }
      return next;
    });
    setErrors(prev => ({ ...prev, [field]: null }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});

    try {
      const res = await fetch('/panel/clientes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (!res.ok) {
        setErrors(data.errors ?? { general: data.message ?? 'Error al crear cliente' });
        return;
      }

      onCreated(data);
    } catch {
      setErrors({ general: 'Error de conexión' });
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div className="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-xl dark:bg-gray-900">

        {/* Header */}
        <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <h2 className="text-base font-semibold text-gray-950 dark:text-white">Crear cliente</h2>
          <button type="button" onClick={onClose} className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
            </svg>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="space-y-5 p-6">
          {errors.general && (
            <p className="rounded-lg bg-red-50 px-4 py-2 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
              {errors.general}
            </p>
          )}

          {/* Identificación */}
          <div className="grid grid-cols-2 gap-4">
            <Field label="Tipo de identificación" error={errors.id_number_type} required>
              <select
                value={form.id_number_type}
                onChange={e => set('id_number_type', e.target.value)}
                className={selectCls(errors.id_number_type)}
              >
                {TIPOS_ID.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
              </select>
            </Field>

            <Field label="Número de identificación" error={errors.id_number} required>
              <input
                type="text"
                value={form.id_number}
                onChange={e => set('id_number', e.target.value)}
                className={inputCls(errors.id_number)}
              />
            </Field>
          </div>

          <Field label="Nombre registrado en Hacienda" error={errors.hacienda_name} required>
            <input
              type="text"
              value={form.hacienda_name}
              onChange={e => set('hacienda_name', e.target.value)}
              className={inputCls(errors.hacienda_name)}
            />
          </Field>

          <div className="grid grid-cols-2 gap-4">
            <Field label="Correo electrónico" error={errors.email} required>
              <input
                type="email"
                value={form.email}
                onChange={e => set('email', e.target.value)}
                className={inputCls(errors.email)}
              />
            </Field>

            <Field label="Teléfono" error={errors.phone} required>
              <input
                type="text"
                value={form.phone}
                onChange={e => set('phone', e.target.value)}
                className={inputCls(errors.phone)}
              />
            </Field>
          </div>

          {/* Ubicación */}
          <div className="grid grid-cols-2 gap-4">
            <Field label="Provincia" error={errors.province_id} required>
              <select value={form.province_id} onChange={e => set('province_id', e.target.value)} className={selectCls(errors.province_id)}>
                <option value="">Seleccionar...</option>
                {provinces.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
              </select>
            </Field>

            <Field label="Cantón" error={errors.canton_id} required>
              <select value={form.canton_id} onChange={e => set('canton_id', e.target.value)} disabled={!form.province_id} className={selectCls(errors.canton_id)}>
                <option value="">Seleccionar...</option>
                {cantons.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </Field>

            <Field label="Distrito" error={errors.district_id} required>
              <select value={form.district_id} onChange={e => set('district_id', e.target.value)} disabled={!form.canton_id} className={selectCls(errors.district_id)}>
                <option value="">Seleccionar...</option>
                {districts.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
              </select>
            </Field>

            <Field label="Barrio" error={errors.neighborhood_id}>
              <select value={form.neighborhood_id} onChange={e => set('neighborhood_id', e.target.value)} disabled={!form.district_id} className={selectCls(errors.neighborhood_id)}>
                <option value="">Seleccionar...</option>
                {neighborhoods.map(n => <option key={n.id} value={n.id}>{n.name}</option>)}
              </select>
            </Field>
          </div>

          <Field label="Dirección exacta" error={errors.address} required>
            <input
              type="text"
              value={form.address}
              onChange={e => set('address', e.target.value)}
              className={inputCls(errors.address)}
            />
          </Field>

          {/* Actions */}
          <div className="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
            <button type="button" onClick={onClose} className="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-800">
              Cancelar
            </button>
            <button type="submit" disabled={submitting} className="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 disabled:opacity-50">
              {submitting ? 'Guardando...' : 'Crear cliente'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function Field({ label, error, required, children }) {
  return (
    <div className="flex flex-col gap-1">
      <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
        {label}{required && <span className="ml-0.5 text-red-500">*</span>}
      </label>
      {children}
      {error && <p className="text-xs text-red-500">{error[0] ?? error}</p>}
    </div>
  );
}

function inputCls(error) {
  return `block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ${error ? 'ring-red-400' : 'ring-black/10'} focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20`;
}

function selectCls(error) {
  return `block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ${error ? 'ring-red-400' : 'ring-black/10'} focus:outline-none focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20 disabled:opacity-50`;
}
