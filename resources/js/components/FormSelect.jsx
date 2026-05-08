export default function FormSelect({
  label,
  id,
  value,
  onChange,
  options = [],
  required = false,
}) {
  return (
    <div>
      {label && (
        <label htmlFor={id} className="block mb-2 text-sm font-medium text-gray-950 dark:text-white">
          {label}
          {required && <span className="text-red-600"> *</span>}
        </label>
      )}
      <select
        id={id}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-black/10 focus:ring-2 focus:ring-amber-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
    </div>
  );
}
