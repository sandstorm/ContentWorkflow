import { useEffect } from 'react';

// Interface for keyboard shortcuts
interface ShortcutHandlers {
    save: () => void;
}

// Custom hook for keyboard shortcuts
export const useKeyboardShortcuts = (shortcuts: ShortcutHandlers): void => {
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent): void => {
            // Check for Cmd+S (Mac) or Ctrl+S (Windows)
            if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                e.preventDefault(); // Prevent browser's save dialog
                shortcuts.save();
            }
        };

        document.addEventListener('keydown', handleKeyDown);

        // Remove event listener on cleanup
        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [shortcuts]);
};

