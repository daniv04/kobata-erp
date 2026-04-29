import { useState } from 'react';
import SectionCard from '../SectionCard';
import FormInput from '../FormInput';
import FormSelect from '../FormSelect';
import ClientSearch from './ClientSearch';

/**
 * ReceptorSection — Información del cliente/receptor de la factura
 *
 * Props:
 *   - data: { Nombre, Identificacion: {Tipo, Numero}, ... }
 *   - errors: mapa de errores
 *   - onChange: fn(path, value)
 *
 * CONCEPTO: Componentes con estado local
 * Este componente tiene su propio estado para controlar visibilidad de secciones
 * (como los toggles de Ubicación y Teléfono). El estado "global" del formulario
 * vive en InvoiceForm, pero estado UI local (como "¿está abierta la sección?")
 * puede vivir aquí. Es un balance entre simplicidad y escalabilidad.
 */
export default function ReceptorSection({ data, errors, onChange }) {
    const [showUbicacion, setShowUbicacion] = useState(false);
    const [showTelefono, setShowTelefono] = useState(false);
    const [receptorSelected, setReceptorSelected] = useState(false);

    /**
     * handleSelectClient: cuando el usuario selecciona un cliente
     * Mapea los campos del modelo Client al formato de la factura
     */
    function handleSelectClient(cliente) {
        // Rellena todos los campos del receptor
        onChange('Receptor.Nombre', cliente.hacienda_name);
        onChange('Receptor.Identificacion.Tipo', cliente.id_number_type || '01');
        onChange('Receptor.Identificacion.Numero', cliente.id_number);
        onChange('Receptor.CorreoElectronico', cliente.email || '');

        if (cliente.phone) {
            onChange('Receptor.Telefono.NumTelefono', cliente.phone);
            setShowTelefono(true);
        }

        // Para Ubicación, necesitaríamos el nombre de provincia/cantón
        // Por ahora lo dejamos en blanco — podrías hacer un segundo endpoint
        // para obtener los datos completos del cliente incluyendo relaciones

        setReceptorSelected(true);
    }

    function handleClearReceptor() {
        onChange('Receptor.Nombre', '');
        onChange('Receptor.Identificacion.Numero', '');
        onChange('Receptor.CorreoElectronico', '');
        onChange('Receptor.Telefono.NumTelefono', '');
        onChange('Receptor.Ubicacion.Provincia', '');
        onChange('Receptor.Ubicacion.Canton', '');
        onChange('Receptor.Ubicacion.Distrito', '');
        onChange('Receptor.Ubicacion.OtrasSenas', '');
        setReceptorSelected(false);
        setShowUbicacion(false);
        setShowTelefono(false);
    }

    return (
        <SectionCard
            title="Información del Cliente"
            description="Selecciona un cliente existente o completa los datos manualmente"
        >
            {/* Búsqueda de cliente */}
            <div className="md:col-span-2">
                <label className="block mb-2 text-sm font-medium text-gray-950 dark:text-white">
                    Buscar Cliente
                </label>
                <ClientSearch
                    onSelect={handleSelectClient}
                    onClear={handleClearReceptor}
                />
                {receptorSelected && (
                    <p className="mt-2 text-xs text-amber-600 dark:text-amber-400">
                        ✓ Cliente seleccionado. Puedes editar los campos si es necesario.
                    </p>
                )}
            </div>

            {/* Nombre */}
            <FormInput
                label="Nombre del Cliente"
                id="receptor_nombre"
                value={data.Nombre}
                onChange={(value) => onChange('Receptor.Nombre', value)}
                error={errors['Receptor.Nombre']?.[0]}
                required
            />

            {/* Tipo de Identificación */}
            <FormSelect
                label="Tipo de Identificación"
                id="receptor_tipo_id"
                value={data.Identificacion.Tipo}
                onChange={(value) => onChange('Receptor.Identificacion.Tipo', value)}
                options={[
                    { value: '01', label: 'Cédula Física' },
                    { value: '02', label: 'Cédula Jurídica' },
                    { value: '03', label: 'DIMEX' },
                    { value: '04', label: 'NITE' },
                    { value: '05', label: 'Extranjero' },
                ]}
                error={errors['Receptor.Identificacion.Tipo']?.[0]}
            />

            {/* Número de Identificación */}
            <FormInput
                label="Número de Identificación"
                id="receptor_numero_id"
                value={data.Identificacion.Numero}
                onChange={(value) => onChange('Receptor.Identificacion.Numero', value)}
                error={errors['Receptor.Identificacion.Numero']?.[0]}
                required
            />

            {/* Email */}
            <FormInput
                label="Email"
                id="receptor_email"
                type="email"
                value={data.CorreoElectronico}
                onChange={(value) => onChange('Receptor.CorreoElectronico', value)}
                error={errors['Receptor.CorreoElectronico']?.[0]}
            />

            {/* Toggle Teléfono */}
            <div className="md:col-span-2">
                <label className="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        checked={showTelefono}
                        onChange={(e) => {
                            setShowTelefono(e.target.checked);
                            if (!e.target.checked) {
                                onChange('Receptor.Telefono.CodigoPais', '');
                                onChange('Receptor.Telefono.NumTelefono', '');
                            }
                        }}
                        className="w-4 h-4 rounded"
                    />
                    <span className="text-sm font-medium text-gray-950 dark:text-white">Agregar Teléfono</span>
                </label>
            </div>

            {/* Campos de Teléfono (condicionales) */}
            {showTelefono && (
                <>
                    <FormInput
                        label="Código País"
                        id="receptor_codigo_pais"
                        value={data.Telefono.CodigoPais}
                        onChange={(value) => onChange('Receptor.Telefono.CodigoPais', value)}
                        placeholder="506"
                    />
                    <FormInput
                        label="Número de Teléfono"
                        id="receptor_telefono"
                        value={data.Telefono.NumTelefono}
                        onChange={(value) => onChange('Receptor.Telefono.NumTelefono', value)}
                    />
                </>
            )}

            {/* Toggle Ubicación */}
            <div className="md:col-span-2">
                <label className="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        checked={showUbicacion}
                        onChange={(e) => {
                            setShowUbicacion(e.target.checked);
                            if (!e.target.checked) {
                                onChange('Receptor.Ubicacion.Provincia', '');
                                onChange('Receptor.Ubicacion.Canton', '');
                                onChange('Receptor.Ubicacion.Distrito', '');
                                onChange('Receptor.Ubicacion.OtrasSenas', '');
                            }
                        }}
                        className="w-4 h-4 rounded"
                    />
                    <span className="text-sm font-medium text-gray-950 dark:text-white">Agregar Ubicación</span>
                </label>
            </div>

            {/* Campos de Ubicación (condicionales) */}
            {showUbicacion && (
                <>
                    <FormInput
                        label="Provincia"
                        id="receptor_provincia"
                        value={data.Ubicacion.Provincia}
                        onChange={(value) => onChange('Receptor.Ubicacion.Provincia', value)}
                    />
                    <FormInput
                        label="Cantón"
                        id="receptor_canton"
                        value={data.Ubicacion.Canton}
                        onChange={(value) => onChange('Receptor.Ubicacion.Canton', value)}
                    />
                    <FormInput
                        label="Distrito"
                        id="receptor_distrito"
                        value={data.Ubicacion.Distrito}
                        onChange={(value) => onChange('Receptor.Ubicacion.Distrito', value)}
                    />
                    <FormInput
                        label="Otras Señas"
                        id="receptor_otras_senas"
                        value={data.Ubicacion.OtrasSenas}
                        onChange={(value) => onChange('Receptor.Ubicacion.OtrasSenas', value)}
                    />
                </>
            )}
        </SectionCard>
    );
}
