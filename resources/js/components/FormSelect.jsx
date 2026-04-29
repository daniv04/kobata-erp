/**
 * FormSelect — Select (dropdown) genérico con estilo Filament
 *
 * Props:
 *   - label: string ← etiqueta del select
 *   - id: string ← atributo id
 *   - value: string ← valor actual (must match uno de los options)
 *   - onChange: fn(value) ← se dispara al cambiar la selección
 *   - options: [{value: string, label: string}, ...] ← lista de opciones
 *   - error?: string ← mensaje de error
 *   - required?: boolean
 *   - disabled?: boolean
 *   - placeholder?: string ← texto del primer option deshabilitado (para "selecciona...")
 *
 * EJEMPLO:
 * <FormSelect
 *     label="Tipo de Identificación"
 *     value={form.Receptor.Identificacion.Tipo}
 *     onChange={(tipo) => handleChange('Receptor.Identificacion.Tipo', tipo)}
 *     options={[
 *         { value: '01', label: 'Cédula Física' },
 *         { value: '02', label: 'Cédula Jurídica' },
 *     ]}
 * />
 */
export default function FormSelect({
    label,
    id,
    value,
    onChange,
    options = [],
    error,
    required = false,
    disabled = false,
    placeholder,
}) {
    return (
        <div>
            {label && (
                <label htmlFor={id} className="block mb-2 text-sm font-medium text-gray-950 dark:text-white">
                    {label}
                    {required && <span className="text-red-600 dark:text-red-400 font-medium"> *</span>}
                </label>
            )}

            <div className="flex rounded-lg bg-white shadow-sm ring-1 ring-black/10 transition duration-75 focus-within:ring-2 focus-within:ring-amber-600 dark:bg-white/5 dark:ring-white/20 dark:focus-within:ring-amber-500">
                <select
                    id={id}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    disabled={disabled}
                    className="block w-full border-none bg-transparent px-3 py-1.5 text-sm leading-6 text-gray-950 focus:ring-0 focus:outline-none disabled:text-gray-500 dark:text-white dark:disabled:text-gray-400 appearance-none cursor-pointer"
                >
                    {placeholder && (
                        <option value="" disabled>
                            {placeholder}
                        </option>
                    )}
                    {options.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>

                {/* Flecha del select — usando SVG */}
                <div className="pointer-events-none flex items-center pr-3">
                    <svg
                        className="h-5 w-5 text-gray-400 dark:text-gray-500"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fillRule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clipRule="evenodd"
                        />
                    </svg>
                </div>
            </div>

            {error && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{error}</p>}
        </div>
    );
}
