//import "@blocknote/core/fonts/inter.css";
import "./MarkdownBlockEditor.css";

import { BlockNoteView } from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import { useCreateBlockNote } from "@blocknote/react";
import { createRoot } from 'react-dom/client';
import React, { useCallback, useEffect, useState, createContext, useContext } from 'react';
import { DefaultBlockSchema } from '@blocknote/core/src/blocks/defaultBlocks'
import {DispatchCommandContext} from '../context';
import { EditorToolbar } from '../Component/EditorToolbar'
import { useKeyboardShortcuts } from './hooks/useKeyboardShortcuts'
import { useSaveDocument } from './hooks/useSaveDocument'

export function init() {
    document.querySelectorAll('.markdown-block-editor').forEach((el: HTMLElement) => {
        const root = createRoot(el);
        console.log("Creating editor for:", el, el.dataset);
        root.render(<MarkdownBlockEditor
            csrfToken={el.dataset.csrfToken}
            dispatchCommandFromJsEndpoint={el.dataset.dispatchCommandFromJsEndpoint}
            currentStepId={el.dataset.currentStepId}
            currentWorkingDocument={JSON.parse(el.dataset.currentWorkingDocument || "undefined")}
        />);
    });
}

interface MarkdownBlockEditorProps {
    csrfToken: string;
    dispatchCommandFromJsEndpoint: string;
    currentStepId: string;
    currentWorkingDocument?: {
        contentAsBlocknoteJson: DefaultBlockSchema;
        contentAsMarkdown: string;
        contentAsHtml: string;
    }
}

const MarkdownBlockEditor: React.FC<MarkdownBlockEditorProps> = (props: MarkdownBlockEditorProps)=> {
    // Creates a new editor instance.
    const editor = useCreateBlockNote({
        initialContent: props.currentWorkingDocument?.contentAsBlocknoteJson
    });

    async function dispatchCommand(payload: any) {
        return await fetch(props.dispatchCommandFromJsEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            body: JSON.stringify(payload),
            headers: {
                'Content-Type': 'application/json',
                'X-Flow-Csrftoken': props.csrfToken,
            }
        })
    };

    const { saveStatus, saveDocument } = useSaveDocument(editor, dispatchCommand);
    useKeyboardShortcuts({ save: saveDocument });

    return <DispatchCommandContext.Provider value={dispatchCommand}>
        <BlockNoteView editor={editor} theme="dark" />
        <EditorToolbar onSave={saveDocument} saveStatus={saveStatus} currentStepId={props.currentStepId} />
    </DispatchCommandContext.Provider>;
}
