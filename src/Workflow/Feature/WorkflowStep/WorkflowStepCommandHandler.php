<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandHandlerInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventsToPersist;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command\TransitionToStep;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command\SaveWorkingDocument;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\TransitionedToStep;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\Workflow\WorkflowProjectionState;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;

readonly class WorkflowStepCommandHandler implements CommandHandlerInterface
{

    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof SaveWorkingDocument
            || $command instanceof TransitionToStep;
    }

    public function handle(CommandInterface $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        return match ($command::class) {
            SaveWorkingDocument::class => $this->handleSaveWorkingDocument($command, $state),
            TransitionToStep::class => $this->handleTransitionToStep($command, $state),
        };
    }

    private function handleSaveWorkingDocument(SaveWorkingDocument $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        return WorkflowEventsToPersist::with(
            new WorkingDocumentSaved(
                content: $command->content,
            )
        );
    }

    private function handleTransitionToStep(TransitionToStep $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        // TODO: EVENTS reingeben, dann explizit State aufbauen.
        // WorkflowProjectionState::forEvents($events)->...
        //$previousStep = $this->ensurePreviousStepExists($state);
        // otherwise exception.

        // ALTERNATIVE: other event



        return WorkflowEventsToPersist::with(
            new TransitionedToStep(
                completedStepId: $state->currentStepOrNull()->id,
                nextStepId: $command->nextStepId,
            )
        );
    }
}
