import {produce} from 'immer';
import {action as createAction, ActionType} from 'typesafe-actions';

export interface State extends Readonly<{
    plugins?: {
        contentWorkflow?: PluginState
    }
}> {}

interface PluginState {
    workflowModalOpenForWorkflowId: string|undefined,
}

export const defaultState: PluginState = {
    workflowModalOpenForWorkflowId: undefined,
};

//
// Export the action types
//
export enum actionTypes {
    START_CREATING_WORKFLOW = '@sandstorm/contentworkflow/UI/START_CREATING_WORKFLOW',
    CLOSE_WORKFLOW_MODAL = '@sandstorm/contentworkflow/UI/CLOSE_WORKFLOW_MODAL',
    CREATE_WORKFLOW = '@sandstorm/contentworkflow/UI/CREATE_WORKFLOW',
}

const startCreatingWorkflow = (workflowId: string) => createAction(actionTypes.START_CREATING_WORKFLOW, {workflowId});

const closeWorkflowModal = () => createAction(actionTypes.CLOSE_WORKFLOW_MODAL);

const createWorkflow = (workflowId: string, properties: Record<string,any>) => createAction(actionTypes.CREATE_WORKFLOW, {workflowId, properties});

export const pluginActions = {
    startCreatingWorkflow,
    closeWorkflowModal,
    createWorkflow,
};

export type Action = ActionType<typeof pluginActions>;

//
// Export the reducer
//
export const reducer = (state: State, action: Action) => produce(state, draft => {
    if (!draft.plugins || !draft.plugins.contentWorkflow) {
        draft.plugins = draft.plugins || {};
        draft.plugins.contentWorkflow = defaultState;
    }
    switch (action.type) {
        case actionTypes.START_CREATING_WORKFLOW: {
            draft.plugins.contentWorkflow.workflowModalOpenForWorkflowId = action.payload.workflowId;
            break;
        }
        case actionTypes.CLOSE_WORKFLOW_MODAL: {
            draft.plugins.contentWorkflow.workflowModalOpenForWorkflowId = undefined;
            break;
        }
        case actionTypes.CREATE_WORKFLOW: {
            draft.plugins.contentWorkflow.workflowModalOpenForWorkflowId = undefined;
            break;
        }
    }
});

const workflowModalWorkflowId = (state: State) => state.plugins.contentWorkflow.workflowModalOpenForWorkflowId;

//
// Export the selectors
//
export const selectors = {
    workflowModalWorkflowId
};
