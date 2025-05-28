<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State\WorkflowLifecycleState;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\StepFinished;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\Dto\StepWithState;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowStepDefinition;

trait WorkflowStepState
{

    function currentStepOrNull(): ?WorkflowStepDefinition
    {
        try {
            $definition = $this->workflowDefinition();
            $finishedSteps = $this->events->findAllOfType(StepFinished::class);
            foreach ($definition->stepDefinitions as $stepDefinition) {
                $alreadyExecuted = $finishedSteps->findLastOrNullWhere(fn(StepFinished $e) => $e->stepId->equals($stepDefinition->id)) !== null;
                if (!$alreadyExecuted) {
                    return $stepDefinition;
                }
            }
        } catch (\Throwable $e) {
            // Error
        }
        return null;
    }

    /**
     * @return StepWithState[]
     */
    function stepListWithCurrentState(): array
    {
        $definition = $this->workflowDefinition();
        $finishedSteps = $this->events->findAllOfType(StepFinished::class);

        $steps = [];
        foreach ($definition->stepDefinitions as $stepDefinition) {
            $alreadyExecuted = $finishedSteps->findLastOrNullWhere(fn(StepFinished $e) => $e->stepId->equals($stepDefinition->id)) !== null;
            $steps[] = new StepWithState(
                definition: $stepDefinition,
                alreadyExecuted: $alreadyExecuted, // TODO IMPL ME
            );
        }
        return $steps;
    }

    /*static public function currentWorkingDocument(WorkflowEvents $state): ?WorkingDocumentContent
    {
        return $state->findLastOrNull(WorkingDocumentSaved::class)?->content;
    }*/
}
