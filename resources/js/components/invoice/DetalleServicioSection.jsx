import SectionCard from '../SectionCard';

export default function DetalleServicioSection({ data, errors, onChange }) {
    return (
        <SectionCard
            title="Detalle de Servicios/Productos"
            description="Agrega las líneas de detalle de la factura"
        >
            <div className="md:col-span-2 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">
                📝 Sección en construcción — próximamente agregarás líneas de detalle
            </div>
        </SectionCard>
    );
}
