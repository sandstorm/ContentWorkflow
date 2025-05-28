import {
    GlobalRegistry,
    NodeType as GlobalNodeType,
    NodeTypesRegistry,
    ValidatorRegistry,
} from '@neos-project/neos-ts-interfaces'
import { z } from 'zod/v4'


export const WorkflowStep = z.object({
    name: z.string(),
    description: z.string(),
})
export type WorkflowStep = z.infer<typeof WorkflowStep>;

export const WorkflowDefinition = z.object({
    name: z.string(),
    description: z.string(),
    steps: z.record(z.string(), WorkflowStep),
})
export type WorkflowDefinition = z.infer<typeof WorkflowDefinition>;

export const WorkflowControl = z.object({
    startWorkflowButtons: z.array(
        z.object({
            id: z.string(),
            label: z.string(),
        }),
    ),

    nextWorkflowStepButtons: z.array(
        z.object({
            id: z.string(),
            label: z.string(),
        })
    ),

    isWorkflowRunning: z.boolean(),
    currentWorkflowName: z.string().nullable(),
    currentWorkflowDescription: z.string().nullable(),
    currentWorkflowStepName: z.string().nullable(),
    currentWorkflowStepDescription: z.string().nullable(),
});

export type WorkflowControl = z.infer<typeof WorkflowControl>;

export const WorkflowUiStatus = z.object({
    workflowControl: WorkflowControl,
    //currentStep: WorkflowStep
})
export type WorkflowUiStatus = z.infer<typeof WorkflowUiStatus>;
