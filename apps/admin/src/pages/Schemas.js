import { useState, useEffect } from '@wordpress/element';
import api from '../utils/api';

export default function Schemas() {
  const [schemas, setSchemas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    api.get('/wp-json/arc-blueprint/v1/schemas')
      .then(response => {
        setSchemas(response.data.data);
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching schemas:', error);
        setError(error.message);
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <div className="text-center !text-lg !text-gray-400">
        Loading schemas...
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-900/20 border border-red-800 rounded-lg p-6">
        <div className="!text-lg !font-semibold text-red-400 mb-2">
          Error Loading Schemas
        </div>
        <div className="!text-base text-red-400">{error}</div>
      </div>
    );
  }

  return (
    <div>
      <h2 className="!text-2xl !font-bold !text-gray-300 mb-6">Registered Schemas</h2>

      <div className="space-y-6">
        {schemas.map(schema => (
          <div
            key={schema.key}
            className="bg-zinc-800 rounded-lg border border-zinc-700 overflow-hidden"
          >
            <div className="p-6 border-b border-zinc-700">
              <div className="flex items-center justify-between mb-3">
                <h3 className="!text-lg !font-semibold !text-gray-300">{schema.key}</h3>
                <span className="px-3 py-1 bg-sky-800 text-sky-200 text-xs font-medium rounded-md">
                  {schema.name}
                </span>
              </div>
              <div className="text-sm text-gray-400 font-mono">{schema.class}</div>
            </div>

            {schema.fields && schema.fields.length > 0 && (
              <div className="p-6">
                <h4 className="!text-base !font-semibold !text-gray-400 mb-3">Fields</h4>
                <div className="space-y-2">
                  {schema.fields.map((field, index) => (
                    <div
                      key={index}
                      className="flex items-center gap-3 p-3 bg-zinc-900 rounded-md"
                    >
                      <span className="text-sm font-medium text-gray-300">{field.name}</span>
                      <span className="px-2 py-0.5 bg-zinc-700 text-gray-400 text-xs rounded">
                        {field.type}
                      </span>
                      {field.label && (
                        <span className="text-sm text-gray-500 ml-auto">{field.label}</span>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            )}

            {schema.collection && schema.collection.model && (
              <div className="p-6 bg-zinc-900/50 border-t border-zinc-700">
                <h4 className="!text-base !font-semibold !text-gray-400 mb-3">
                  Collection Details
                </h4>
                <div className="space-y-2 text-sm">
                  <div className="flex gap-2">
                    <span className="font-medium text-gray-500">Table:</span>
                    <span className="text-gray-300 font-mono">
                      {schema.collection.model.table}
                    </span>
                  </div>
                  {schema.collection.routes && (
                    <div className="flex gap-2">
                      <span className="font-medium text-gray-500">API Endpoint:</span>
                      <span className="text-gray-300 font-mono text-xs">
                        {schema.collection.routes.endpoint}
                      </span>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
