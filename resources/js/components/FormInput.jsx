/**
 * FormInput — Input de texto genérico con estilo Filament
 *
 * Props:
 *   - label: string ← etiqueta del input
 *   - id: string ← atributo id para asociar con label
 *   - value: string ← valor actual
 *   - onChange: fn(value) ← se dispara al cambiar el input
 *   - error?: string ← mensaje de error, si existe
 *   - type?: string ← "text" | "email" | "number" | "date" | "datetime-local" | etc.
 *   - placeholder?: string
 *   - required?: boolean ← muestra asterisco rojo
 *   - disabled?: boolean
 *   - pattern?: string ← validación HTML
 *   - min?: string|number
 *   - max?: string|number
 *   - step?: string|number
 *
 * CONCEPTO: Componentes reutilizables
 * Este componente encapsula "un input con su label y errores".
 * Al envolver esa lógica aquí, otros componentes (como ReceptorSection)
 * no necesitan repetir el HTML — solo dicen <FormInput ... />.
 * Es como crear un "widget" o "componente de UI base" que el resto usa.
 */
export default function FormInput({
    label,
    id,
    value,
    onChange,
    error,
    type = 'text',
    placeholder,
    required = false,
    disabled = false,
    pattern,
    min,
    max,
    step,
}) {
    return (
        <div>
            {/* Label + asterisco si required */}
            {label && (
                <label htmlFor={id} className="block mb-2 text-sm font-medium text-gray-950 dark:text-white">
                    {label}
                    {required && <span className="text-red-600 dark:text-red-400 font-medium"> *</span>}
                </label>
            )}

            {/* Input wrapper — estilo Filament */}
            {/* focus-within:ring-2 hace que brille cuando el input está focused */}
            <div className="flex rounded-lg bg-white shadow-sm ring-1 ring-black/10 transition duration-75 focus-within:ring-2 focus-within:ring-amber-600 dark:bg-white/5 dark:ring-white/20 dark:focus-within:ring-amber-500">
                <input
                    type={type}
                    id={id}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    placeholder={placeholder}
                    disabled={disabled}
                    pattern={pattern}
                    min={min}
                    max={max}
                    step={step}
                    // Estilo del input — sin borde, fondo transparente, el borde está en el wrapper
                    className="block w-full border-none bg-transparent px-3 py-1.5 text-sm leading-6 text-gray-950 placeholder:text-gray-400 focus:ring-0 focus:outline-none disabled:text-gray-500 dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400"
                />
            </div>

            {/* Mensaje de error */}
            {error && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{error}</p>}
        </div>
    );
}
