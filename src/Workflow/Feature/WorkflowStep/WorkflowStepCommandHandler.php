<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandHandlerInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventsToPersist;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command\FinishCurrentStep;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command\SaveWorkingDocument;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\CurrentStepFinished;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;

readonly class WorkflowStepCommandHandler implements CommandHandlerInterface
{

    public function __construct(
        private ForWorkflowDefinition $workflowDefinitionApp,
    )
    {
    }

    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof SaveWorkingDocument
            || $command instanceof FinishCurrentStep;
    }

    public function handle(CommandInterface $command, WorkflowEvents $state): WorkflowEventsToPersist
    {
        return match ($command::class) {
            SaveWorkingDocument::class => $this->handleSaveWorkingDocument($command, $state),
            FinishCurrentStep::class => $this->handleFinishCurrentStep($command, $state),
        };
    }

    private function handleSaveWorkingDocument(SaveWorkingDocument $command, WorkflowEvents $state): WorkflowEventsToPersist
    {
        return WorkflowEventsToPersist::with(
            new WorkingDocumentSaved(
                content: $command->content,
            )
        );
    }

    private function handleFinishCurrentStep(FinishCurrentStep $command, WorkflowEvents $state): WorkflowEventsToPersist
    {
        return WorkflowEventsToPersist::with(
            new CurrentStepFinished(
            )
        );
    }
}
