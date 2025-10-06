const TextareaField = ({ fieldName, fieldConfig, register }) => {
  const rows = fieldConfig.rows || 3;

  return (
    <div>
      <label
        htmlFor={fieldName}
        className="block text-sm font-medium text-gray-700 mb-1"
      >
        {fieldConfig.label || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
      </label>
      <textarea
        id={fieldName}
        {...register(fieldName)}
        rows={rows}
        placeholder={fieldConfig.placeholder || ''}
        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>
  );
};

export default TextareaField;
