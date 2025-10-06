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

/**
 * Get a single record by ID
 * @param {string} endpoint - Full API endpoint URL
 * @param {number} id - Record ID
 */
export const getRecord = async (endpoint, id) => {
  const response = await axios.get(`${endpoint}/${id}`, {
    headers: {
      'X-WP-Nonce': window.wpApiSettings?.nonce || '',
    },
  });
  return response.data;
};

/**
 * Create a new record using the collection endpoint
 * @param {string} endpoint - Full API endpoint URL
 * @param {object} data - Data to create
 */
export const createRecord = async (endpoint, data) => {
  const response = await axios.post(endpoint, data, {
    headers: {
      'X-WP-Nonce': window.wpApiSettings?.nonce || '',
      'Content-Type': 'application/json',
    },
  });
  return response.data;
};

/**
 * Update an existing record
 * @param {string} endpoint - Full API endpoint URL
 * @param {number} id - Record ID
 * @param {object} data - Data to update
 */
export const updateRecord = async (endpoint, id, data) => {
  const response = await axios.put(`${endpoint}/${id}`, data, {
    headers: {
      'X-WP-Nonce': window.wpApiSettings?.nonce || '',
      'Content-Type': 'application/json',
    },
  });
  return response.data;
};

export default api;
