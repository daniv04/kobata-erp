import SectionCard from '../SectionCard';

export default function ResumenFacturaSection({ data, lines, errors, onChange }) {
    return (
        <SectionCard
            title="Resumen de la Factura"
            description="Totales y método de pago"
        >
            <div className="md:col-span-2 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center text-sm text-gray-500 dark:text-gray-400">
                💰 Sección en construcción — próximamente verás totales y método de pago
            </div>
        </SectionCard>
    );
}
