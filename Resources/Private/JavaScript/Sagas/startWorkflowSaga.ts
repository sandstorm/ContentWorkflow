import { takeLatest, take, select, call, put, race } from 'redux-saga/effects'
import {
    pluginActions as localActions,
    actionTypes as localActionTypes,
    selectors as localSelectors,
} from '../Redux/index'
import { actions, selectors } from '@neos-project/neos-ui-redux-store'
import {applySaveHooksForTransientValuesMap} from '@neos-project/neos-ui-sagas/src/Changes/saveHooks';

export function* startWorkflowSaga({ globalRegistry }) {
    yield takeLatest(localActionTypes.CREATE_WORKFLOW, function* (action: ReturnType<typeof localActions.createWorkflow>) {
        // @ts-ignore
        const documentNodeId = yield select(selectors.CR.Nodes.documentNodeContextPathSelector);

        const saveHooksRegistry = globalRegistry.get('inspector').get('saveHooks');

        const data = yield * applySaveHooksForTransientValuesMap(action.payload.properties, saveHooksRegistry);

        // @ts-ignore
        return yield put(actions.Changes.persistChanges(
            [{
                type: 'Sandstorm.ContentWorkflow:HandleCommand',
                subject: documentNodeId,
                payload: {
                    commandId: 'StartWorkflow',
                    commandPayload: {
                        workflowId: action.payload.workflowId,
                        properties: action.payload.properties,
                    },
                },
            }],
        ))
    });
}
