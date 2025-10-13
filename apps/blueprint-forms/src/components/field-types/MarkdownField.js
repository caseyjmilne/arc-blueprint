import { useEffect, useState, useRef } from '@wordpress/element';
import SimpleMDE from 'react-simplemde-editor';
import 'easymde/dist/easymde.min.css';

const MarkdownField = ({ fieldName, fieldConfig, register, error, setValue, watch }) => {
  const [isReady, setIsReady] = useState(false);
  const editorRef = useRef(null);
  const currentValue = watch ? watch(fieldName) : '';

  // Register the field with react-hook-form
  useEffect(() => {
    if (register) {
      register(fieldName);
    }
    setIsReady(true);
  }, [fieldName, register]);

  const handleChange = (value) => {
    if (setValue) {
      setValue(fieldName, value, { shouldValidate: true });
    }
  };

  const editorOptions = {
    spellChecker: false,
    placeholder: fieldConfig.placeholder || 'Enter markdown text...',
    status: false,
    toolbar: [
      'bold',
      'italic',
      'heading',
      '|',
      'quote',
      'unordered-list',
      'ordered-list',
      '|',
      'link',
      'image',
      '|',
      'preview',
      'side-by-side',
      'fullscreen',
      '|',
      'guide'
    ],
    minHeight: fieldConfig.minHeight || '200px',
    maxHeight: fieldConfig.maxHeight || '500px',
  };

  if (!isReady) {
    return <div>Loading editor...</div>;
  }

  return (
    <div>
      <label
        htmlFor={fieldName}
        className="block text-sm font-medium text-gray-700 mb-1"
      >
        {fieldConfig.label || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
        {fieldConfig.required && <span className="text-red-500 ml-1">*</span>}
      </label>
      {fieldConfig.helpText && (
        <p className="text-sm text-gray-500 mb-2">{fieldConfig.helpText}</p>
      )}
      <div className={`markdown-editor-wrapper ${error ? 'border-red-500' : ''}`}>
        <SimpleMDE
          id={fieldName}
          value={currentValue || ''}
          onChange={handleChange}
          options={editorOptions}
          ref={editorRef}
        />
      </div>
      {error && (
        <p className="mt-1 text-sm text-red-600">{error.message}</p>
      )}
    </div>
  );
};

export default MarkdownField;
