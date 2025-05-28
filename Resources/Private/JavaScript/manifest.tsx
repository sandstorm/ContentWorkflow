import React, { PureComponent } from 'react'
import { Tabs } from '@neos-project/react-ui-components'
import { WorkflowTab } from './Containers/LeftSidebar/WorkflowsTab'
import { takeLatest, take, select, call, put, race, fork, spawn } from 'redux-saga/effects'
import { delay } from 'redux-saga'

import './ContentModule.css'

import manifest from '@neos-project/neos-ui-extensibility'
import { startWorkflowSaga } from './Sagas/startWorkflowSaga'
import { pluginActions, reducer } from './Redux'
import { WorkflowCreationDialog } from './Containers/Modals/WorkflowCreationDialog'
import { WorkflowControlView } from './Containers/Inspector/WorkflowControlView/WorkflowControlView'
import { actions } from '@neos-project/neos-ui-redux-store'
import { Change, GlobalRegistry } from '@neos-project/neos-ts-interfaces'
import { loadWorkflowStateSaga } from './Sagas/loadWorkflowStateSaga'

manifest('Sandstorm.ContentWorkflow', {}, (globalRegistry: GlobalRegistry) => {
    const sagasRegistry = globalRegistry.get('sagas')
    const reducersRegistry = globalRegistry.get('reducers')
    //const viewsRegistry = globalRegistry.get('inspector').get('views');
    const editorRegistry = globalRegistry.get('inspector').get('editors')
    const saveHooksRegistry = globalRegistry.get('inspector').get('saveHooks')
    const serverFeedbackHandlers = globalRegistry.get('serverFeedbackHandlers')

    const containerRegistry = globalRegistry.get('containers')

    sagasRegistry.set('sandstorm-contentworkflow/startworkflow', {
        saga: startWorkflowSaga,
    })
    sagasRegistry.set('sandstorm-contentworkflow/loadWorkflowState', {
        saga: loadWorkflowStateSaga,
    })

    serverFeedbackHandlers.set('Sandstorm.ContentWorkflow:WorkflowStateUpdated/Main', (feedbackPayload: any, { store }: any) => {
        store.dispatch(pluginActions.updateWorkflowUiStatus(feedbackPayload));
    });

    reducersRegistry.set('Sandstorm.ContentWorkflow:reducer', { reducer })

    editorRegistry.set('Sandstorm.ContentWorkflow:WorkflowControl', {
        component: WorkflowControlView,
    })
    saveHooksRegistry.set('Sandstorm.ContentWorkflow:Hook.ExecuteCommand', function* (value, options) {
        yield spawn(function* () {
            // we want to move our HandleCommand to AFTER persist; so that the server sees the newest state.
            yield delay(1)
            yield put(actions.Changes.persistChanges(
                [{
                    type: 'Sandstorm.ContentWorkflow:HandleCommand',
                    subject: options.node,
                    payload: {
                        commandId: options.command,
                        commandPayload: options.commandPayload,
                    },
                } as any as Change],
            ))
        })
        return value
    })

    const PageTreeToolbar = containerRegistry.get('LeftSideBar/Top/PageTreeToolbar')
    const PageTreeSearchbar = containerRegistry.get('LeftSideBar/Top/PageTreeSearchbar')
    const PageTree = containerRegistry.get('LeftSideBar/Top/PageTree')

    const OriginalTree = () => (
        <div>
            <div>
                <PageTreeToolbar />
            </div>
            <PageTreeSearchbar />
            <PageTree />
        </div>
    )
    containerRegistry.set('LeftSideBar/Top/PageTreeToolbar', () => null)
    containerRegistry.set('LeftSideBar/Top/PageTreeSearchbar', () => null)
    containerRegistry.set('LeftSideBar/Top/PageTree', makeFlatNavContainer(OriginalTree))

    containerRegistry.set('Modals/WorkflowCreationDialog', WorkflowCreationDialog)
})


const makeFlatNavContainer = OriginalPageTree => {
    class FlatNavContainer extends PureComponent {

        constructor(props) {
            super(props)
        }

        render() {
            return (
                <Tabs>
                    <Tabs.Panel id="foo" title="Seitenbaum">
                        <OriginalPageTree />
                    </Tabs.Panel>
                    <Tabs.Panel id="bar" title="Workflows">
                        <WorkflowTab />
                    </Tabs.Panel>
                </Tabs>
            )
        }
    }

    return FlatNavContainer
}

export default makeFlatNavContainer
