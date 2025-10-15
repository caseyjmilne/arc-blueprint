import { useState, useEffect } from '@wordpress/element';
import api from '../utils/api';

export default function Dashboard() {
  const [schemaCount, setSchemaCount] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/wp-json/arc-blueprint/v1/schemas')
      .then(response => {
        setSchemaCount(response.data.data.length);
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching schemas:', error);
        setLoading(false);
      });
  }, []);

  return (
    <div>
      <h2 className="!text-2xl !font-bold !text-gray-300 mb-6">Dashboard</h2>

      <div className="bg-zinc-800 rounded-lg p-6 border border-zinc-700">
        <div className="text-center">
          <div className="text-6xl font-bold text-sky-200 mb-3">
            {loading ? '...' : schemaCount}
          </div>
          <div className="!text-lg !font-medium !text-gray-400">
            {loading ? 'Loading schemas...' : (schemaCount === 1 ? 'Schema Registered' : 'Schemas Registered')}
          </div>
        </div>
      </div>
    </div>
  );
}
