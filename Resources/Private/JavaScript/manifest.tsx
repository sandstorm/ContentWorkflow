import React, {PureComponent} from 'react';
import {Tabs} from '@neos-project/react-ui-components';
import { WorkflowTab } from './Containers/LeftSidebar/WorkflowsTab'


import './ContentModule.css'

import manifest from '@neos-project/neos-ui-extensibility'
import { startWorkflowSaga } from './Sagas/startWorkflowSaga'
import { reducer } from './Redux'
import { WorkflowCreationDialog } from './Containers/Modals/WorkflowCreationDialog'

manifest('Sandstorm.ContentWorkflow', {}, (globalRegistry) => {
    const sagasRegistry = globalRegistry.get('sagas');
    const reducersRegistry = globalRegistry.get('reducers');
    const containerRegistry = globalRegistry.get('containers')

    sagasRegistry.set('sandstorm-contentworkflow/startworkflow', {
        saga: startWorkflowSaga
    });

    reducersRegistry.set('Sandstorm.ContentWorkflow:reducer', {reducer});

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

    containerRegistry.set('Modals/WorkflowCreationDialog', WorkflowCreationDialog);
})


const makeFlatNavContainer = OriginalPageTree => {
    class FlatNavContainer extends PureComponent {

        constructor(props) {
            super(props);
        }

        render() {
            return (
                <Tabs>
                    <Tabs.Panel id="foo" title="Seitenbaum" >
                        <OriginalPageTree />
                    </Tabs.Panel>
                    <Tabs.Panel id="bar" title="Workflows">
                        <WorkflowTab />
                    </Tabs.Panel>
                </Tabs>
            );
        }
    }
    return FlatNavContainer;
};

export default makeFlatNavContainer;
