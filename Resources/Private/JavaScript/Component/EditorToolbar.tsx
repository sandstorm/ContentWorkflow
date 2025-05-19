
// Editor Toolbar Component
import { SaveButton, SaveStatus } from './SaveButton'
import React, { useCallback, useEffect, useState, createContext, useContext } from 'react';
import {DispatchCommandContext} from '../context';

// Props for the EditorToolbar component
interface EditorToolbarProps {
    onSave: () => void;
    saveStatus: SaveStatus;
    currentStepId: string;
}

export const EditorToolbar: React.FC<EditorToolbarProps> = ({ onSave, saveStatus, currentStepId }) => {
    const dispatchCommand = useContext(DispatchCommandContext);
    return (
        <div className="editor-toolbar">
            <SaveButton onClick={onSave} status={saveStatus} />
            <button onClick={async () => {
                await dispatchCommand({
                    command: 'FinishCurrentStep',
                    stepId: currentStepId,
                });
                window.location.reload();
            }}>Finish Current Step</button>
        </div>
    );
};
