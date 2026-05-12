import { useState, useCallback } from 'react';

let nextId = 0;

export function useToast() {
  const [toasts, setToasts] = useState([]);

  const dismiss = useCallback((id) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  }, []);

  const notify = useCallback(({ type = 'success', title, body }) => {
    const id = ++nextId;
    setToasts(prev => [...prev, { id, type, title, body }]);
  }, []);

  return { toasts, notify, dismiss };
}
