<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State\WorkflowLifecycleState;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\TransitionedToStep;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\Dto\StepWithState;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowStepDefinition;

trait WorkflowStepState
{

    function currentStepOrNull(): ?WorkflowStepDefinition
    {
        $lastStateTransition = $this->events->findLastOrNullWhere(fn(WorkflowEventInterface $event) => $event instanceof TransitionedToStep || $event instanceof WorkflowWasStarted);
        if (!$lastStateTransition) {
            return null;
        }


        if ($lastStateTransition instanceof WorkflowWasStarted) {
            $definition = $this->workflowDefinition();
            return $definition->stepDefinitions->find($lastStateTransition->initialStep);
        } elseif ($lastStateTransition instanceof TransitionedToStep) {
            $definition = $this->workflowDefinition();
            return $definition->stepDefinitions->find($lastStateTransition->nextStepId);
        } else {
            throw new \RuntimeException('can never happen');
        }
    }

    /**
     * @return StepWithState[]
     */
    function stepListWithCurrentState(): array
    {
        $definition = $this->workflowDefinition();
        $finishedSteps = $this->events->findAllOfType(TransitionedToStep::class);

        $steps = [];
        foreach ($definition->stepDefinitions as $stepDefinition) {
            $alreadyExecuted = $finishedSteps->findLastOrNullWhere(fn(TransitionedToStep $e) => $e->stepId->equals($stepDefinition->id)) !== null;
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
