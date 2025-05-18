//import "@blocknote/core/fonts/inter.css";
import { BlockNoteView } from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import { useCreateBlockNote } from "@blocknote/react";
import { createRoot } from 'react-dom/client';
import React, { useCallback, useEffect, useState } from 'react';
import { BlockNoteEditor, PartialBlock } from '@blocknote/core'
import { SaveButton, SaveStatus } from '../Component/SaveButton'
import { DefaultBlockSchema } from '@blocknote/core/src/blocks/defaultBlocks'

export function init() {
    document.querySelectorAll('.markdown-block-editor').forEach((el: HTMLElement) => {
        const root = createRoot(el);
        console.log("Creating editor for:", el, el.dataset);
        root.render(<MarkdownBlockEditor
            csrfToken={el.dataset.csrfToken}
            dispatchCommandFromJsEndpoint={el.dataset.dispatchCommandFromJsEndpoint}
            currentWorkingDocument={JSON.parse(el.dataset.currentWorkingDocument || "undefined")}
        />);
    });
}

interface MarkdownBlockEditorProps {
    csrfToken: string;
    dispatchCommandFromJsEndpoint: string;
    currentWorkingDocument?: {
        contentAsBlocknoteJson: DefaultBlockSchema;
        contentAsMarkdown: string;
        contentAsHtml: string;
    }
}
const MarkdownBlockEditor: React.FC<MarkdownBlockEditorProps> = (props: MarkdownBlockEditorProps)=> {
    // Creates a new editor instance.
    const editor = useCreateBlockNote({
        initialContent: props.currentWorkingDocument.contentAsBlocknoteJson
    });

    // Use custom hooks
    const { saveStatus, saveDocument } = useSaveDocument(editor, props.csrfToken, props.dispatchCommandFromJsEndpoint);
    useKeyboardShortcuts({ save: saveDocument });

    return <>
        <BlockNoteView editor={editor} theme="dark" />
        <EditorToolbar onSave={saveDocument} saveStatus={saveStatus} />
    </>;
}

// Interface for save hook return value
interface SaveHookResult {
    saveStatus: SaveStatus;
    saveDocument: () => void;
}

// Custom hook for save functionality
const useSaveDocument = (editor: BlockNoteEditor, csrfToken: string, dispatchCommandFromJsEndpoint: string): SaveHookResult => {
    const [saveStatus, setSaveStatus] = useState<SaveStatus>('idle');

    const saveDocument = useCallback(() => {
        // Skip if already saving
        if (saveStatus === 'saving') return;

        // Get the content from the editor
        const content = editor.document;

        // Start saving
        setSaveStatus('saving');

        const saveToServer = async (): Promise<void> => {
            try {
                // Simulate API call
                // const response = await api.saveDocument(content);

                console.log("Saving content:", content);

                const markdownFromBlocks = await editor.blocksToMarkdownLossy();
                const htmlFromBlocks = await editor.blocksToHTMLLossy();

                await fetch(dispatchCommandFromJsEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        command: 'SaveWorkingDocument',
                        contentAsBlocknoteJson: content,
                        contentAsMarkdown: markdownFromBlocks,
                        contentAsHtml: htmlFromBlocks,
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Flow-Csrftoken': csrfToken,
                    }
                })

                // Set to saved when complete
                setSaveStatus('saved');

                // Reset status after showing checkmark
                setTimeout(() => {
                    setSaveStatus('idle');
                }, 2000);
            } catch (error) {
                console.error('Error saving document:', error);
                setSaveStatus('idle');
                // Here you could also set an error state and show an error message
            }
        };

        // Execute the save function
        saveToServer();
    }, [editor, saveStatus]);

    return { saveStatus, saveDocument };
};


// Interface for keyboard shortcuts
interface ShortcutHandlers {
    save: () => void;
}

// Custom hook for keyboard shortcuts
const useKeyboardShortcuts = (shortcuts: ShortcutHandlers): void => {
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent): void => {
            // Check for Cmd+S (Mac) or Ctrl+S (Windows)
            if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                e.preventDefault(); // Prevent browser's save dialog
                shortcuts.save();
            }
        };

        // Add event listener
        document.addEventListener('keydown', handleKeyDown);

        // Remove event listener on cleanup
        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [shortcuts]);
};


// Props for the EditorToolbar component
interface EditorToolbarProps {
    onSave: () => void;
    saveStatus: SaveStatus;
}

// Editor Toolbar Component
const EditorToolbar: React.FC<EditorToolbarProps> = ({ onSave, saveStatus }) => {
    return (
        <div className="editor-toolbar">
            <SaveButton onClick={onSave} status={saveStatus} />
            <style jsx>{`
        .editor-toolbar {
          margin-top: 10px;
          display: flex;
          justify-content: flex-end;
        }
      `}</style>
        </div>
    );
};
