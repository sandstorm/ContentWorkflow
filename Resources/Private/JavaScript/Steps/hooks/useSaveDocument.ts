// Interface for save hook return value
import { SaveStatus } from '../../Component/SaveButton'
import { BlockNoteEditor } from '@blocknote/core'
import { useCallback, useState } from 'react'

interface SaveHookResult {
    saveStatus: SaveStatus;
    saveDocument: () => void;
}

// Custom hook for save functionality
export const useSaveDocument = (editor: BlockNoteEditor, dispatchCommand: any): SaveHookResult => {
    const [saveStatus, setSaveStatus] = useState<SaveStatus>('idle')

    const saveDocument = useCallback(async () => {
        // Skip if already saving
        if (saveStatus === 'saving') return

        // Get the content from the editor
        const content = editor.document

        // Start saving
        setSaveStatus('saving')

        const saveToServer = async (): Promise<void> => {
            try {
                // Simulate API call
                // const response = await api.saveDocument(content);

                console.log('Saving content:', content)

                const markdownFromBlocks = await editor.blocksToMarkdownLossy()
                const htmlFromBlocks = await editor.blocksToHTMLLossy()

                await dispatchCommand({
                    command: 'SaveWorkingDocument',
                    contentAsBlocknoteJson: content,
                    contentAsMarkdown: markdownFromBlocks,
                    contentAsHtml: htmlFromBlocks,
                })

                // Set to saved when complete
                setSaveStatus('saved')

                // Reset status after showing checkmark
                setTimeout(() => {
                    setSaveStatus('idle')
                }, 2000)
            } catch (error) {
                console.error('Error saving document:', error)
                setSaveStatus('idle')
                // Here you could also set an error state and show an error message
            }
        }

        await saveToServer()
    }, [editor, saveStatus])

    return { saveStatus, saveDocument }
}


