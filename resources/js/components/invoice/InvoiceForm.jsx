import { useState } from 'react';
import SectionCard from '../SectionCard';
import ReceptorSection from './ReceptorSection';
import EncabezadoSection from './EncabezadoSection';
import DetalleServicioSection from './DetalleServicioSection';
import ResumenFacturaSection from './ResumenFacturaSection';

/**
 * CONCEPTO: Lifting State Up (Elevar estado)
 *
 * En React, el estado vive en el componente más arriba que lo necesita.
 * Como TODOS los campos (Receptor, Encabezado, DetalleServicio, Resumen)
 * necesitan actualizarse entre sí, el estado vive aquí en InvoiceForm.
 *
 * Los componentes hijos (ReceptorSection, etc.) NO tienen estado — son
 * "dumb" o "presentational". Solo reciben datos (props) y reportan cambios
 * via callbacks. Esto hace que el flujo de datos sea predecible y fácil de debuggear.
 */

// Función auxiliar: actualizar un valor anidado en un objeto usando dot notation
// Ejemplo: setIn({ a: { b: 1 } }, 'a.b', 2) → { a: { b: 2 } }
// Devuelve un NUEVO objeto (no mutabilidad) — React necesita esto para detectar cambios
function setIn(obj, path, value) {
    const keys = path.split('.');
    const result = { ...obj }; // Copia superficial
    let current = result;

    // Navega hasta la penúltima key, clonando cada nivel
    for (let i = 0; i < keys.length - 1; i++) {
        const key = keys[i];
        // Si el valor en esta key es un array, clónalo. Si es un objeto, clónalo. Si no existe, crea un objeto.
        if (Array.isArray(current[key])) {
            current[key] = [...current[key]];
        } else if (typeof current[key] === 'object' && current[key] !== null) {
            current[key] = { ...current[key] };
        } else {
            current[key] = {};
        }
        current = current[key];
    }

    // Asigna el valor final
    current[keys[keys.length - 1]] = value;
    return result;
}

// Estructura inicial del formulario
const INITIAL_FORM = {
    Receptor: {
        Nombre: '',
        Identificacion: { Tipo: '01', Numero: '' },
        CorreoElectronico: '',
        Telefono: { CodigoPais: '', NumTelefono: '' },
        Ubicacion: { Provincia: '', Canton: '', Distrito: '', OtrasSenas: '' },
    },
    Encabezado: {
        FechaEmision: new Date().toISOString().slice(0, 16), // datetime-local format
        CodigoActividadEmisor: '',
        CondicionVenta: '01',
        CondicionVentaOtros: '',
        PlazoCredito: '',
        SituacionComprobante: '1',
    },
    Clave: {
        Sucursal: '',
        Terminal: '',
        TipoComprobante: '01',
    },
    DetalleServicio: {
        LineaDetalle: [],
    },
    ResumenFactura: {
        CodigoTipoMoneda: { CodigoMoneda: 'CRC', TipoCambio: '1' },
        MedioPago: [{ TipoMedioPago: '01', MedioPagoOtros: '', TotalMedioPago: '' }],
        TotalIVADevuelto: '0',
        TotalOtrosCargos: '0',
    },
};

export default function InvoiceForm() {
    // Estado principal del formulario
    const [form, setForm] = useState(INITIAL_FORM);

    // Errores devueltos por el backend (mapa: dot.path → array de mensajes)
    const [errors, setErrors] = useState({});

    // Control del botón submit (deshabilitado mientras se envía)
    const [submitting, setSubmitting] = useState(false);

    // Indica si el envío fue exitoso
    const [success, setSuccess] = useState(false);

    // Respuesta cruda del servidor (para debug o mostrar al usuario)
    const [response, setResponse] = useState(null);

    /**
     * handleChange: actualiza un campo del formulario
     * @param {string} path - dot notation path (ej: 'Receptor.Nombre')
     * @param {*} value - nuevo valor
     *
     * FLUJO:
     * 1. Actualiza el form usando setIn
     * 2. Limpia el error de ese campo si existía
     */
    function handleChange(path, value) {
        setForm((prev) => setIn(prev, path, value));

        if (errors[path]) {
            setErrors((prev) => {
                const updated = { ...prev };
                delete updated[path];
                return updated;
            });
        }
    }

    /**
     * handleSubmit: envía el formulario al backend
     */
    async function handleSubmit(e) {
        e.preventDefault();
        setSubmitting(true);
        setErrors({});
        setSuccess(false);
        setResponse(null);

        try {
            // POST a /panel/facturacion (la ruta del controller)
            const { data } = await window.axios.post('/panel/facturacion', form);

            setResponse(data);
            setSuccess(true);

            // Opcional: limpiar formulario después de éxito
            // setForm(INITIAL_FORM);
        } catch (err) {
            const errData = err.response?.data ?? { message: err.message };
            setResponse(errData);

            // Si el servidor devolvió errores en formato Filament:
            // { errors: { 'dot.path': ['message'] } }
            if (errData.errors) {
                setErrors(errData.errors);
            }
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <div className="max-w-4xl mx-auto p-6">
            <h1 className="text-3xl font-bold text-gray-950 dark:text-white mb-8">Nueva Factura Electrónica</h1>

            {/* Banner de éxito */}
            {success && (
                <div className="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-700 dark:text-green-400">
                    ✓ Factura enviada correctamente.
                </div>
            )}

            {/* Banner de error */}
            {Object.keys(errors).length > 0 && !success && (
                <div className="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
                    ✕ Hay errores en el formulario. Revisá los campos marcados.
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-8" noValidate>
                {/* Sección Receptor */}
                <ReceptorSection
                    data={form.Receptor}
                    errors={errors}
                    onChange={handleChange}
                />

                {/* Sección Encabezado */}
                <EncabezadoSection
                    data={form.Encabezado}
                    clave={form.Clave}
                    errors={errors}
                    onChange={handleChange}
                />

                {/* Sección Detalle de Servicio */}
                <DetalleServicioSection
                    data={form.DetalleServicio}
                    errors={errors}
                    onChange={handleChange}
                />

                {/* Sección Resumen */}
                <ResumenFacturaSection
                    data={form.ResumenFactura}
                    lines={form.DetalleServicio.LineaDetalle}
                    errors={errors}
                    onChange={handleChange}
                />

                {/* Botón de envío */}
                <div className="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="submit"
                        disabled={submitting}
                        className="px-6 py-2 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {submitting ? 'Enviando...' : 'Enviar Factura'}
                    </button>
                </div>
            </form>

            {/* Debug: mostrar respuesta del servidor */}
            {response && (
                <details className="mt-8 rounded-lg bg-gray-100 dark:bg-gray-800 p-4">
                    <summary className="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300">
                        Respuesta del servidor (debug)
                    </summary>
                    <pre className="mt-3 overflow-auto text-xs text-gray-600 dark:text-gray-400">
                        {JSON.stringify(response, null, 2)}
                    </pre>
                </details>
            )}
        </div>
    );
}
