import './bootstrap';
import { createRoot } from 'react-dom/client';
import InvoiceForm from './components/invoice/InvoiceForm';

/**
 * CONCEPTO: Punto de montaje de React
 *
 * React no controla toda la página — solo el div con id "facturacion-react-root".
 * createRoot() le dice a React "este div es tuyo, manéjalo tú".
 * A partir de ahí, React mantiene su propio Virtual DOM dentro de ese div.
 *
 * El `if (container)` protege contra el caso de que este script cargue en
 * una página que no tenga ese div (por ejemplo, otras páginas del panel).
 */
const container = document.getElementById('facturacion-react-root');

if (container) {
    createRoot(container).render(<InvoiceForm />);
}
