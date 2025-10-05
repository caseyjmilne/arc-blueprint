import { useState, useEffect } from '@wordpress/element';
import { useForm } from 'react-hook-form';
import { getSchema, createRecord } from './services/api';

const App = () => {
  const [schema, setSchema] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const { register, handleSubmit, reset, formState: { errors } } = useForm();

  useEffect(() => {
    loadSchema();
  }, []);

  const loadSchema = async () => {
    try {
      setLoading(true);
      const response = await getSchema('ticket');
      console.log('Schema response:', response);
      setSchema(response.data);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const onSubmit = async (data) => {
    if (!schema?.collection?.routes?.endpoint) {
      setError('No endpoint available for submission');
      return;
    }

    try {
      setSubmitting(true);
      setError(null);
      setSuccess(null);

      const response = await createRecord(schema.collection.routes.endpoint, data);

      setSuccess('Ticket created successfully!');
      reset(); // Clear form
      console.log('Created ticket:', response);
    } catch (err) {
      setError(err.response?.data?.message || err.message || 'Failed to create ticket');
      console.error('Submit error:', err);
    } finally {
      setSubmitting(false);
    }
  };

  const getInputType = (fieldName, casts = {}) => {
    // Check casts first
    if (casts[fieldName]) {
      const cast = casts[fieldName];
      if (cast === 'datetime' || cast === 'date') return 'date';
      if (cast === 'integer' || cast === 'int') return 'number';
      if (cast === 'boolean') return 'checkbox';
    }

    // Infer from field name
    if (fieldName.includes('email')) return 'email';
    if (fieldName.includes('password')) return 'password';
    if (fieldName.includes('date')) return 'date';
    if (fieldName.includes('time')) return 'time';

    return 'text';
  };

  if (loading) {
    return <div className="p-6">Loading...</div>;
  }

  if (error) {
    return (
      <div className="p-6">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error}
        </div>
      </div>
    );
  }

  if (!schema || !schema.collection?.model?.fillable) {
    return <div className="p-6">No schema found</div>;
  }

  return (
    <div className="max-w-2xl mx-auto p-6">
      <div className="bg-white p-6 rounded-lg shadow">
        <h2 className="text-2xl font-bold mb-2">{schema.name}</h2>
        <p className="text-sm text-gray-600 mb-6">Model: {schema.collection.model.class}</p>

        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {success && (
          <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {success}
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          {schema.collection.model.fillable.map((fieldName) => {
            const inputType = getInputType(fieldName, schema.collection.model.casts);

            return (
              <div key={fieldName}>
                <label
                  htmlFor={fieldName}
                  className="block text-sm font-medium text-gray-700 mb-1"
                >
                  {fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                </label>

                {inputType === 'checkbox' ? (
                  <input
                    type="checkbox"
                    id={fieldName}
                    {...register(fieldName)}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                ) : fieldName === 'description' ? (
                  <textarea
                    id={fieldName}
                    {...register(fieldName)}
                    rows="3"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                ) : (
                  <input
                    type={inputType}
                    id={fieldName}
                    {...register(fieldName)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                )}
              </div>
            );
          })}

          <button
            type="submit"
            disabled={submitting}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {submitting ? 'Creating...' : 'Create Ticket'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default App;
