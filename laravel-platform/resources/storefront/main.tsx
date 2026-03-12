import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import './styles/app.css';

// Find the root element - support both 'root' and 'storefront-root'
const rootElement = document.getElementById('root') || document.getElementById('storefront-root');

if (rootElement) {
  const root = ReactDOM.createRoot(rootElement);

  // Detect basename: if running under /store/{slug}, use that as basename
  // For subdomain/custom domain, basename is '/'
  const pathParts = window.location.pathname.split('/');
  const storeIndex = pathParts.indexOf('store');
  const basename = storeIndex !== -1 && pathParts[storeIndex + 1]
    ? `/store/${pathParts[storeIndex + 1]}`
    : '/';

  root.render(
    <React.StrictMode>
      <BrowserRouter basename={basename}>
        <App />
      </BrowserRouter>
    </React.StrictMode>
  );
}
