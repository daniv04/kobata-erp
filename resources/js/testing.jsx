import { createRoot } from 'react-dom/client';
import DocumentosList from './components/testing/DocumentosList';

const root = document.getElementById('documentos-testing-root');
if (root) {
    createRoot(root).render(<DocumentosList />);
}
