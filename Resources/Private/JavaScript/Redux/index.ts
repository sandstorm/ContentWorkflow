import {produce} from 'immer';
import {action as createAction, ActionType} from 'typesafe-actions';
import { GlobalState } from '@neos-project/neos-ui-redux-store'
import { WorkflowUiStatus } from '../types'

export type State = GlobalState & {
    plugins?: {
        contentWorkflow?: PluginState
    }
};

interface PluginState {
    // TODO REMOVE
    workflowModalOpenForWorkflowId: string|undefined,

    // The current workflow status
    workflowUiStatus: WorkflowUiStatus;
}


export const defaultState: PluginState = {
    workflowModalOpenForWorkflowId: undefined,
    workflowUiStatus: {
        workflowControl: {
            startWorkflowButtons: [],
            nextWorkflowStepButtons: [],

            isWorkflowRunning: false,
            currentWorkflowName: '',
            currentWorkflowDescription: '',
            currentWorkflowStepName: '',
            currentWorkflowStepDescription: '',
        }
    },
};

//
// Export the action types
//
export enum actionTypes {
    UPDATE_WORKFLOW_UI_STATUS = '@sandstorm/contentworkflow/UI/UPDATE_WORKFLOW_UI_STATUS',
    START_CREATING_WORKFLOW = '@sandstorm/contentworkflow/UI/START_CREATING_WORKFLOW',
    CLOSE_WORKFLOW_MODAL = '@sandstorm/contentworkflow/UI/CLOSE_WORKFLOW_MODAL',
    CREATE_WORKFLOW = '@sandstorm/contentworkflow/UI/CREATE_WORKFLOW',

}

const updateWorkflowUiStatus = (workflowUiStatus: WorkflowUiStatus) => createAction(actionTypes.UPDATE_WORKFLOW_UI_STATUS, {workflowUiStatus});

// TODO REMOVE:
const startCreatingWorkflow = (workflowId: string) => createAction(actionTypes.START_CREATING_WORKFLOW, {workflowId});
const closeWorkflowModal = () => createAction(actionTypes.CLOSE_WORKFLOW_MODAL);

const createWorkflow = (workflowId: string, properties: Record<string,any>) => createAction(actionTypes.CREATE_WORKFLOW, {workflowId, properties});

export const pluginActions = {
    updateWorkflowUiStatus,
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
        case actionTypes.UPDATE_WORKFLOW_UI_STATUS: {
            draft.plugins.contentWorkflow.workflowUiStatus = action.payload.workflowUiStatus;
            break;
        }
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

const workflowModalWorkflowId = (state: State) => state.plugins!.contentWorkflow!.workflowModalOpenForWorkflowId;
const workflowUiStatus = (state: State) => state.plugins!.contentWorkflow!.workflowUiStatus;

//
// Export the selectors
//
export const pluginSelectors = {
    workflowModalWorkflowId,
    workflowUiStatus,
};
