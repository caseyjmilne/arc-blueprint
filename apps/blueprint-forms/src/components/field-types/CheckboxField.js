const CheckboxField = ({ fieldName, fieldConfig, register }) => {
  return (
    <div className="flex items-center">
      <input
        type="checkbox"
        id={fieldName}
        {...register(fieldName)}
        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
      />
      <label
        htmlFor={fieldName}
        className="ml-2 block text-sm font-medium text-gray-700"
      >
        {fieldConfig.label || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
      </label>
    </div>
  );
};

export default CheckboxField;
