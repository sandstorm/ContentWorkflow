import React, { PureComponent } from 'react'
import { connect } from 'react-redux'
import memoize from 'lodash.memoize'
import cx from 'classnames'

import { neos } from '@neos-project/neos-ui-decorators'
import validate from '@neos-project/neos-ui-validators'
import { GlobalRegistry, ValidatorRegistry } from '@neos-project/neos-ts-interfaces'

import { Icon, Button, Dialog } from '@neos-project/react-ui-components'
import I18n from '@neos-project/neos-ui-i18n'
import { EditorEnvelope } from '@neos-project/neos-ui-editors'

// @ts-ignore
import style from './style.module.css'
import { pluginActions, State } from '../../../Redux'
import { CreationDialogConfig, CreationDialogElement, PluginConfig, WorkflowDefinition } from '../../../types'


const defaultState: WorkflowCreationDialogState = {
    transient: {},
    validationErrors: null,
    isDirty: false,
    secondaryInspectorName: '',
    secondaryInspectorComponent: null,
}

const getTransientDefaultValuesFromConfiguration = (configuration: CreationDialogConfig) => {
    if (configuration) {
        return Object.keys(configuration.elements).reduce(
            (transientDefaultValues, elementName) => {
                if (configuration.elements[elementName].defaultValue === undefined) {
                    transientDefaultValues[elementName] = { value: null }
                } else {
                    transientDefaultValues[elementName] = {
                        value: configuration.elements[elementName].defaultValue,
                    }
                }
                return transientDefaultValues
            },
            {},
        )
    }
    return {}
}

function connectToGlobalRegistry(globalRegistry: GlobalRegistry) {
    return {
        // @ts-ignore
        config: globalRegistry.get('frontendConfiguration').get('Sandstorm.ContentWorkflow') as PluginConfig,
        validatorRegistry: globalRegistry.get('validators'),
    };
}

function mapStateToProps(state: State, props: { config: PluginConfig }) {
    const workflowIdToCreate = state.plugins?.contentWorkflow?.workflowModalOpenForWorkflowId
    return {
        workflowIdToCreate: workflowIdToCreate,
        workflowDefinition: props.config.definedWorkflows[workflowIdToCreate],
    }
}

const mapDispatchToProps = {
    cancel: pluginActions.closeWorkflowModal,
    apply: pluginActions.createWorkflow,
};


const getDerivedStateFromProps = (props: WorkflowCreationDialogProps, state: WorkflowCreationDialogState) => {
    if (!props.workflowIdToCreate) {
        return defaultState
    }

    if (state.isDirty) {
        return state
    }

    const transientDefaultValues = getTransientDefaultValuesFromConfiguration(
        props.workflowDefinition?.creationDialog,
    )

    return {
        ...state,
        transient: {
            ...transientDefaultValues,
            ...state.transient,
        },
    }
}

interface WorkflowCreationDialogProps {
    workflowIdToCreate: string | undefined;
    workflowDefinition: WorkflowDefinition | undefined;
    validatorRegistry: ValidatorRegistry;
    cancel: typeof pluginActions.closeWorkflowModal;
    apply: typeof pluginActions.createWorkflow;
}

type WorkflowCreationDialogState = {
    transient: Record<string, {
        value: string,
        hooks: any,
    }>,
    validationErrors: null|Record<string, any>,
    isDirty: boolean,
    secondaryInspectorName: string,
    secondaryInspectorComponent: React.ReactNode | null,
};


class _WorkflowCreationDialog extends PureComponent<WorkflowCreationDialogProps, WorkflowCreationDialogState> {

    static defaultState = {
        transient: {},
        validationErrors: null,
        isDirty: false,
        secondaryInspectorName: '',
        secondaryInspectorComponent: null,
    }

    constructor(props) {
        super(props)
        this.state = getDerivedStateFromProps(props, defaultState)
    }

    static getDerivedStateFromProps(props, state) {
        return getDerivedStateFromProps(props, state)
    }

    handleDialogEditorValueChange = memoize(elementName => (value, hooks) => {
        const transient = Object.assign({}, this.state.transient, {
            [elementName]: { value, hooks }
        });
        const validationErrors = this.getValidationErrorsForTransientValues(transient)

        this.setState({
            transient,
            isDirty: true,
            validationErrors,
        })
    })

    getValidationErrorsForTransientValues = transientValues => {
        const { validatorRegistry, workflowDefinition } = this.props
        const values = this.getValuesMapFromTransientValues(transientValues)

        return validate(values, workflowDefinition?.creationDialog?.elements, validatorRegistry)
    }

    getValuesMapFromTransientValues = transientValues => {
        return Object.keys(transientValues).reduce(
            (valuesMap, elementName) => {
                valuesMap[elementName] = transientValues[elementName].value
                return valuesMap
            },
            {},
        )
    }

    handleCancel = () => {
        const { cancel } = this.props
        cancel()
    }

    handleBack = () => {
        const { cancel } = this.props
        cancel()
    }

    handleApply = () => {
        const { transient } = this.state
        const validationErrors = this.getValidationErrorsForTransientValues(transient)

        if (validationErrors) {
            this.setState({ validationErrors, isDirty: true })
        } else {
            const { apply } = this.props
            apply(this.props.workflowIdToCreate, transient);
            this.setState(defaultState)
        }
    }

    handleKeyPress = event => {
        if (event.key === 'Enter') {
            this.handleApply()
        }
    }

    handleSecondaryInspectorDismissal = () => this.setState({
        secondaryInspectorName: '',
        secondaryInspectorComponent: null,
    })

    /**
     * API function called by nested Editors, to render a secondary inspector.
     *
     * @param string secondaryInspectorName toggle the secondary inspector if the name is the same as before.
     * @param function secondaryInspectorComponentFactory this function, when called without arguments, must return the React component to be rendered.
     */
    renderSecondaryInspector = (secondaryInspectorName, secondaryInspectorComponentFactory) => {
        if (this.state.secondaryInspectorName === secondaryInspectorName) {
            // We toggle the secondary inspector if it is rendered a second time; so that's why we hide it here.
            // @TODO: this.handleCloseSecondaryInspector();
        } else {
            let secondaryInspectorComponent = null
            if (secondaryInspectorComponentFactory) {
                // Hint: we directly resolve the factory function here, to ensure the object is not re-created on every render but stays the same for its whole lifetime.
                secondaryInspectorComponent = secondaryInspectorComponentFactory()
            }

            this.setState({
                secondaryInspectorName,
                secondaryInspectorComponent,
            })
        }
    }

    renderBackAction() {
        return (
            <Button
                id="neos-NodeCreationDialog-Back"
                key="back"
                style="lighter"
                hoverStyle="brand"
                onClick={this.handleBack}
            >
                <I18n id="Neos.Neos:Main:back" fallback="Back" />
            </Button>
        )
    }

    renderTitle() {
        const label = this.props.workflowDefinition?.name;
        return (
            <span>
                <I18n fallback="Create new" id="createNew" />&nbsp;
                <I18n id={label} fallback={label} />
            </span>
        )
    }

    renderSaveAction() {
        const { isDirty, validationErrors } = this.state
        return (
            <Button
                id="neos-NodeCreationDialog-CreateNew"
                key="save"
                style="success"
                hoverStyle="success"
                onClick={this.handleApply}
                disabled={validationErrors && isDirty}
            >
                <Icon icon="plus-square" className={style.buttonIcon} />
                <I18n id="Neos.Neos:Main:createNew" fallback="Create" />
            </Button>
        )
    }

    renderElement(elementName: string, element: CreationDialogElement, isFirst: boolean) {
        const { validationErrors, isDirty } = this.state;
        const validationErrorsForElement = (isDirty && validationErrors) ? validationErrors[elementName] : [];

        const options = Object.assign({}, element.ui.editorOptions, {
            autoFocus: isFirst,
        })

        return (
            <div key={elementName} className={style.editor}>
                <EditorEnvelope
                    identifier={`${elementName}--creation-dialog`}
                    label={element?.ui?.label}
                    editor={element?.ui?.editor}
                    helpMessage={element?.ui?.help?.message || ''}
                    helpThumbnail={element?.ui?.help?.thumbnail || ''}
                    options={options}
                    commit={this.handleDialogEditorValueChange(elementName)}
                    validationErrors={validationErrorsForElement}
                    value={this.state.transient[elementName].value || ''}
                    hooks={this.state.transient[elementName].hooks}
                    onKeyPress={this.handleKeyPress}
                    onEnterKey={this.handleApply}
                    renderSecondaryInspector={this.renderSecondaryInspector}
                />
            </div>
        )
    }

    renderAllElements() {
        const creationDialogElements = this.props.workflowDefinition?.creationDialog?.elements

        if (!creationDialogElements) {
            return []
        }

        return Object.keys(creationDialogElements).reduce(
            (result, elementName, index) => {
                const element = creationDialogElements[elementName]
                const isHidden = element?.ui?.hidden
                if (element && !isHidden) {
                    result.push(
                        this.renderElement(elementName, element, index === 0),
                    )
                }

                return result
            },
            [],
        )
    }

    render() {
        if (!this.props.workflowIdToCreate) {
            return null
        }

        return (
            // @ts-ignore
            <Dialog
                actions={[this.renderBackAction(), this.renderSaveAction()]}
                title={this.renderTitle()}
                onRequestClose={this.handleCancel}
                preventClosing={this.state.isDirty}
                type="success"
                isOpen
                style={this.state.secondaryInspectorComponent ? 'jumbo' : 'wide'}
                autoFocus={true}
            >
                <div
                    className={cx({
                        [style.body]: true,
                        [style.expanded]: Boolean(this.state.secondaryInspectorComponent),
                    })}
                >
                    <div className={style.secondaryColumn}>
                        <div className={style.secondaryColumn__contentWrapper}>
                            <Button
                                style="clean"
                                className={style.close}
                                onClick={this.handleSecondaryInspectorDismissal}
                            >
                                <Icon icon="chevron-left" />
                            </Button>

                            {this.state.secondaryInspectorComponent}
                        </div>
                    </div>
                    <div className={style.primaryColumn}>
                        {this.renderAllElements()}
                    </div>
                </div>
            </Dialog>
        )
    }
}

export const WorkflowCreationDialog =
    neos(connectToGlobalRegistry)(
        connect(mapStateToProps, mapDispatchToProps)(
            _WorkflowCreationDialog,
        ),
    )

