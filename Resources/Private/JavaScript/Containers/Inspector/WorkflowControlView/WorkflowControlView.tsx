import React, { PureComponent } from 'react'

import { connect } from 'react-redux'

import { Node } from '@neos-project/neos-ts-interfaces'

import { Button } from '@neos-project/react-ui-components'

import { pluginSelectors, State } from '../../../Redux'
import { actions, selectors } from '@neos-project/neos-ui-redux-store'
import { WorkflowUiStatus } from '../../../types'

function mapStateToProps(state: State) {
    return {
        focusedNode: selectors.CR.Nodes.focusedSelector(state),
        workflowUiStatus: pluginSelectors.workflowUiStatus(state),
    }
}

const mapDispatchToProps = {
    apply: actions.UI.Inspector.apply,
}

interface WorkflowControlViewProps {
    focusedNode: Node,
    workflowUiStatus: WorkflowUiStatus,

    apply: typeof actions.UI.Inspector.apply

    // Props from outside
    // The value of the property "contentWorkflow_currentlyRunningWorkflow":
    // If a workflow is currently running for this node, contentWorkflow_currentlyRunningWorkflow contains the WorkflowId.
    // Otherwise, is empty.
    value: string;
    commit: (value: any, hooks: any) => void;
}

class _WorkflowControlView extends PureComponent<WorkflowControlViewProps> {

    render() {
        if (this.props.value) {
            return this.renderRunningWorkflow()
        } else {
            return this.renderStartWorkflow()
        }
    }

    private renderRunningWorkflow() {
        const { currentWorkflowName, currentWorkflowStepName, nextWorkflowStepButtons } = this.props.workflowUiStatus.workflowControl
        const entries = nextWorkflowStepButtons.map((btnDef) => {
            return <Button key={btnDef.id}
                           onClick={this.handleNewWorkflowClick(btnDef.id)}>{btnDef.label}</Button>
        })
        return (
            <div>
                Running Workflow {currentWorkflowName}: {currentWorkflowStepName}
                {entries}
            </div>
        )
    }

    private renderStartWorkflow() {
        const { startWorkflowButtons } = this.props.workflowUiStatus.workflowControl
        const entries = startWorkflowButtons.map((btnDef) => {
            return <Button key={btnDef.id}
                           onClick={this.handleNewWorkflowClick(btnDef.id)}>{btnDef.label}</Button>
        })

        return (
            <div>
                Start Workflow
                {entries}
            </div>
        )
    }

    handleNewWorkflowClick = (workflowDefinitionId: string) => () => {
        const workflowId = generateId()
        this.props.commit(workflowId, {
            'Sandstorm.ContentWorkflow:Hook.ExecuteCommand': {
                node: this.props.focusedNode.contextPath,
                command: 'StartWorkflow',
                commandPayload: {
                    workflowId,
                    workflowDefinitionId,
                },
            },
        })
        this.props.apply()
    }
}
export const WorkflowControlView =
    connect(mapStateToProps, mapDispatchToProps)(
        _WorkflowControlView,
    );


// Method 2: Simple alphanumeric (customizable length)
function generateId(length = 12) {
    const chars = 'abcdefghijklmnopqrstuvwxyz0123456789'
    let result = ''
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    return result
}
