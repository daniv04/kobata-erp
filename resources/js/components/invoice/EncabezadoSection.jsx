import SectionCard from '../SectionCard';
import FormInput from '../FormInput';
import FormSelect from '../FormSelect';

/**
 * EncabezadoSection — Información general de la factura
 * Fecha, tipo de comprobante, condición de venta, etc.
 */
export default function EncabezadoSection({ data, clave, errors, onChange }) {
    const handleCondicionChange = (value) => {
        onChange('Encabezado.CondicionVenta', value);

        // Lógica condicional: si la condición no es 02 o 10, borra PlazoCredito
        if (!['02', '10'].includes(value)) {
            onChange('Encabezado.PlazoCredito', '');
        }

        // Si la condición no es 99, borra CondicionVentaOtros
        if (value !== '99') {
            onChange('Encabezado.CondicionVentaOtros', '');
        }
    };

    return (
        <SectionCard
            title="Información de la Factura"
            description="Fecha, tipo de comprobante y condiciones de venta"
        >
            {/* Fecha y Hora de Emisión */}
            <FormInput
                label="Fecha y Hora de Emisión"
                id="fecha_emision"
                type="datetime-local"
                value={data.FechaEmision}
                onChange={(value) => onChange('Encabezado.FechaEmision', value)}
                required
            />

            {/* Código de Actividad del Emisor */}
            <FormInput
                label="Código de Actividad del Emisor"
                id="codigo_actividad"
                value={data.CodigoActividadEmisor}
                onChange={(value) => onChange('Encabezado.CodigoActividadEmisor', value)}
                placeholder="Ej: 511040"
            />

            {/* Tipo de Comprobante */}
            <FormSelect
                label="Tipo de Comprobante"
                id="tipo_comprobante"
                value={clave.TipoComprobante}
                onChange={(value) => onChange('Clave.TipoComprobante', value)}
                options={[
                    { value: '01', label: 'Factura Electrónica' },
                    { value: '02', label: 'Nota de Débito' },
                    { value: '03', label: 'Nota de Crédito' },
                    { value: '04', label: 'Tiquete Electrónico' },
                    { value: '05', label: 'Nota de Débito' },
                    { value: '06', label: 'Nota de Crédito' },
                ]}
            />

            {/* Condición de Venta */}
            <FormSelect
                label="Condición de Venta"
                id="condicion_venta"
                value={data.CondicionVenta}
                onChange={handleCondicionChange}
                options={[
                    { value: '01', label: 'Contado' },
                    { value: '02', label: 'Crédito' },
                    { value: '03', label: 'Consignación' },
                    { value: '04', label: 'Apartado' },
                    { value: '05', label: 'Arrendamiento' },
                    { value: '06', label: 'Otra' },
                    { value: '99', label: 'Condición de Venta Especial' },
                ]}
            />

            {/* Plazo Crédito (condicional) */}
            {['02', '10'].includes(data.CondicionVenta) && (
                <FormInput
                    label="Plazo Crédito (días)"
                    id="plazo_credito"
                    type="number"
                    value={data.PlazoCredito}
                    onChange={(value) => onChange('Encabezado.PlazoCredito', value)}
                    min="0"
                />
            )}

            {/* Condición de Venta Otros (condicional) */}
            {data.CondicionVenta === '99' && (
                <FormInput
                    label="Describir Condición de Venta"
                    id="condicion_venta_otros"
                    value={data.CondicionVentaOtros}
                    onChange={(value) => onChange('Encabezado.CondicionVentaOtros', value)}
                />
            )}

            {/* Situación del Comprobante */}
            <FormSelect
                label="Situación del Comprobante"
                id="situacion_comprobante"
                value={data.SituacionComprobante}
                onChange={(value) => onChange('Encabezado.SituacionComprobante', value)}
                options={[
                    { value: '1', label: 'Normal' },
                    { value: '2', label: 'Contingencia' },
                    { value: '3', label: 'Sin Internet' },
                ]}
            />

            {/* Datos de la Clave (Sucursal, Terminal) */}
            <FormInput
                label="Sucursal"
                id="clave_sucursal"
                value={clave.Sucursal}
                onChange={(value) => onChange('Clave.Sucursal', value)}
                placeholder="001"
                required
            />

            <FormInput
                label="Terminal"
                id="clave_terminal"
                value={clave.Terminal}
                onChange={(value) => onChange('Clave.Terminal', value)}
                placeholder="001"
                required
            />
        </SectionCard>
    );
}
