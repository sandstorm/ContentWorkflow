import {takeLatest, take, select, call, put, race} from 'redux-saga/effects';
import {pluginActions as localActions, actionTypes as localActionTypes, selectors as localSelectors} from '../redux/index';

export function * startWorkflowSaga({globalRegistry}) {

    /*yield takeLatest(localActionTypes.START_CREATING_WORKFLOW, function* (action) {
        const {referenceNodeContextPath, referenceNodeFusionPath, preferredMode, nodeType} = action.payload;

    }*/
}
