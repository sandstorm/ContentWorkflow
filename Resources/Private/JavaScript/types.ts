export type PluginConfig = {
    definedWorkflows: Record<string, WorkflowDefinition>
}
export type WorkflowDefinition = {
    name: string,
    description: string,
    creationDialog?: CreationDialogConfig,
    steps: Record<string, WorkflowStep>
}
export type WorkflowStep = {
    name: string,
    description: string,
}
export type CreationDialogConfig = {
    elements?: Record<string, CreationDialogElement>
}
export type CreationDialogElement = {
    defaultValue?: any,
    ui?: {
        hidden?: boolean;
        label?: string,
        editor?: string,
        editorOptions?: any,
        help?: {
            message?: string,
            thumbnail?: string,
        }
    }
}
