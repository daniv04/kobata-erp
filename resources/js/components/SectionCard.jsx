/**
 * SectionCard — Wrapper para agrupar campos en una sección
 * Estilo similar a las secciones de Filament
 *
 * Props:
 *   - title: string ← encabezado de la sección
 *   - description?: string ← descripción debajo del título
 *   - children: ReactNode ← los campos / contenido dentro de la sección
 *
 * EJEMPLO:
 * <SectionCard title="Información del Cliente" description="Datos del receptor de la factura">
 *     <FormInput ... />
 *     <FormSelect ... />
 * </SectionCard>
 */
export default function SectionCard({ title, description, children }) {
    return (
        <div className="rounded-xl bg-white shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
            {/* Header */}
            <div className="flex items-start gap-3 px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <div>
                    <h3 className="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        {title}
                    </h3>
                    {description && (
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400 break-words overflow-hidden">
                            {description}
                        </p>
                    )}
                </div>
            </div>

            {/* Content — grid de 2 columnas que se adapta a 1 en mobile */}
            <div className="p-6">
                <div className="grid gap-6 md:grid-cols-2">
                    {children}
                </div>
            </div>
        </div>
    );
}
