import { render } from '@wordpress/element';
import App from './App';
import './styles.css';

const rootElement = document.getElementById('arc-blueprint-admin-root');
if (rootElement) {
  render(<App />, rootElement);
}
