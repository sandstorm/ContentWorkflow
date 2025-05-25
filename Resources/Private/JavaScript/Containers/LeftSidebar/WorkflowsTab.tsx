import React, { ComponentType, JSX, PureComponent, ReactElement, ReactInstance } from 'react'
import {connect} from 'react-redux';
import { actions, selectors } from '@neos-project/neos-ui-redux-store'
import {neos} from '@neos-project/neos-ui-decorators';
import { Button } from '@neos-project/react-ui-components'
import { pluginActions } from '../../Redux'
import { PluginConfig, WorkflowDefinition } from '../../types'

const getDataLoaderOptionsForProps = props => ({
    contextNodePath: props.documentNode,
    dataSourceIdentifier: 'FOO',
    //dataSourceUri: props.options.dataSourceUri,
    //dataSourceAdditionalData: props.options.dataSourceAdditionalData,
    dataSourceDisableCaching: true
});

type WorkflowTabProps = {
    dataSourcesDataLoader: {
        resolveValue: (options: any, value: any) => Promise<any>
    },
    documentNode: string,
    config: PluginConfig,
    startCreatingWorkflow: (workflowId: string) => void
}

class _WorkflowTab extends PureComponent<WorkflowTabProps> {


    constructor() {
        // @ts-ignore
        super(...arguments);
    }
    /*componentDidMount() {
        this.loadSelectBoxOptions();
    }

    componentDidUpdate(prevProps) {
        // if our data loader options have changed (e.g. due to use of ClientEval), we want to re-initialize the data source.
        if (JSON.stringify(getDataLoaderOptionsForProps(this.props)) !== JSON.stringify(getDataLoaderOptionsForProps(prevProps))) {
            this.loadSelectBoxOptions();
        }
    }

    loadSelectBoxOptions() {
        this.setState({isLoading: true});
        this.props.dataSourcesDataLoader.resolveValue(getDataLoaderOptionsForProps(this.props), this.props.value)
            .then(selectBoxOptions => {
                this.setState({
                    isLoading: false,
                    selectBoxOptions
                });
            });
    }*/


    // propTypes??
    render() {
        console.log("this.config", this.props.config);
        return <div>
            {Object.entries(this.props.config.definedWorkflows).map(([workflowId, workflow]) => {
                return <Button key={workflowId} onClick={this.handleNewWorkflowClick(workflowId)}>{workflow.name}</Button>
            })}
        </div>
    }
    handleNewWorkflowClick = (workflowId: string) => () =>  {
        this.props.startCreatingWorkflow(workflowId);
        /*this.props.persistChanges([{
            type: 'Sandstorm.ContentWorkflow:HandleCommand',
            subject: this.props.documentNode,
            payload: {
                commandId: 'StartWorkflow',
                commandPayload: {
                    workflowId: workflowId,
                }
            }
        }]);*/


        console.log("BTN CLICK", this, workflowId);
    }
}


export const WorkflowTab = neos(globalRegistry => ({
    dataSourcesDataLoader: globalRegistry.get('dataLoaders').get('DataSources'),
    config: globalRegistry.get('frontendConfiguration').get('Sandstorm.ContentWorkflow'),
    //i18nRegistry: globalRegistry.get('i18n')
}))(connect((state) => ({
    documentNode: selectors.CR.Nodes.documentNodeSelector(state)
}), {
    startCreatingWorkflow: pluginActions.startCreatingWorkflow
})(_WorkflowTab)) as any as ComponentType<any>;
