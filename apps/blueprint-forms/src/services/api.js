import axios from 'axios';

// Create axios instance with WordPress REST API configuration
const api = axios.create({
  baseURL: window.wpApiSettings?.root || '/wp-json/',
  headers: {
    'X-WP-Nonce': window.wpApiSettings?.nonce || '',
  },
});

/**
 * Get all schemas
 */
export const getSchemas = async () => {
  const response = await api.get('arc-blueprint/v1/schemas');
  return response.data;
};

/**
 * Get a single schema by key
 * @param {string} key - Schema key (lowercase with underscores)
 */
export const getSchema = async (key) => {
  const response = await api.get(`arc-blueprint/v1/schemas/${key}`);
  return response.data;
};

export default api;
