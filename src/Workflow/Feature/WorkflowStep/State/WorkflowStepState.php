<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State\WorkflowLifecycleState;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\Dto\StepWithState;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

class WorkflowStepState
{
    /**
     * @return StepWithState[]
     */
    static function stepListWithCurrentState(WorkflowEvents $state, ForWorkflowDefinition $definitions): array
    {
        $definitionId = WorkflowLifecycleState::definitionId($state);
        $definition = $definitions->getWorkflowDefinitionOrThrow($definitionId);

        $steps = [];
        foreach ($definition->stepDefinitions as $stepDefinition) {
            $steps[] = new StepWithState(
                definition: $stepDefinition,
                alreadyExecuted: false, // TODO IMPL ME
            );
        }
        return $steps;
    }

    static public function currentWorkingDocument(WorkflowEvents $state): ?WorkingDocumentContent
    {
        return $state->findLastOrNull(WorkingDocumentSaved::class)?->content;
    }
}
