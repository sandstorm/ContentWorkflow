import { takeLatest, take, select, call, put, race } from 'redux-saga/effects'
import {
    pluginActions as localActions,
    actionTypes as localActionTypes,
    pluginSelectors as localSelectors,
} from '../Redux/index'
import { actions, actionTypes } from '@neos-project/neos-ui-redux-store'
import { DataSourcesDataLoaderOptions, GlobalRegistry } from '@neos-project/neos-ts-interfaces'
import { WorkflowUiStatus } from '../types'
import { z } from 'zod/v4'

/** Load the workflow state for the given document node */
export function* loadWorkflowStateSaga({ globalRegistry }: { globalRegistry: GlobalRegistry }) {
    const dataSourcesDataLoader = globalRegistry.get('dataLoaders').get('DataSources')

    yield takeLatest(actionTypes.CR.Nodes.SET_DOCUMENT_NODE, function* (action: ReturnType<typeof actions.CR.Nodes.setDocumentNode>) {
        try {
            // @ts-ignore
            const result = yield call([dataSourcesDataLoader, 'resolveValue'], {
                dataSourceIdentifier: 'workflowStatus',
                dataSourceDisableCaching: true,
                contextNodePath: action.payload.documentNode,
            } as DataSourcesDataLoaderOptions);

            const workflowUiStatus = WorkflowUiStatus.safeParse(result)
            if (!workflowUiStatus.success) {
                console.error(result, z.prettifyError(workflowUiStatus.error))
            } else {
                yield put(localActions.updateWorkflowUiStatus(workflowUiStatus.data))
            }
        } catch (e) {
            console.error("error in loadWorkflowStateSaga", e);
            return
        }
    })
}
