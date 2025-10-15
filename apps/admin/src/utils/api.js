import axios from 'axios';

// Configure axios to include WordPress REST API nonce
const api = axios.create({
  headers: {
    'X-WP-Nonce': window.wpApiSettings?.nonce || '',
  },
});

export default api;
