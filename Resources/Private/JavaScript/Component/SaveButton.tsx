import React from 'react';

import './saveButton.css';

// Define types for save status
export type SaveStatus = 'idle' | 'saving' | 'saved';

// Props for the SaveButton component
interface SaveButtonProps {
    onClick: () => void;
    status: SaveStatus;
}

// Icons as separate components
const SaveIcon: React.FC = () => <span>Save</span>;

const SpinnerIcon: React.FC = () => (
    <svg className="saveButton__spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <circle className="saveButton__spinner-path" cx="12" cy="12" r="10" fill="none" strokeWidth="4" />
    </svg>
);

const CheckmarkIcon: React.FC = () => (
    <svg className="saveButton__checkmark" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path fill="none" stroke="currentColor" strokeWidth="2" d="M20,6 L9,17 L4,12" />
    </svg>
);

// Save Button Component
export const SaveButton: React.FC<SaveButtonProps> = ({ onClick, status }) => {
    // Select icon based on status
    const getIcon = (): React.ReactNode => {
        switch (status) {
            case 'saving': return <SpinnerIcon />;
            case 'saved': return <CheckmarkIcon />;
            default: return <SaveIcon />;
        }
    };

    // Generate class name based on status
    const getClassName = (): string => {
        let className = 'saveButton';
        if (status === 'saving') {
            className += ' saveButton--saving';
        } else if (status === 'saved') {
            className += ' saveButton--saved';
        }
        return className;
    };

    return (
        <button
            onClick={onClick}
            className={getClassName()}
            disabled={status === 'saving'}
        >
            {getIcon()}
        </button>
    );
};
