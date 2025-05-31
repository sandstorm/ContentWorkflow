<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandHandlerInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventsToPersist;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\AbortWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\ReopenWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\StartWorkflowFromScratch;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasAborted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasReopened;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;
use Sandstorm\ContentWorkflow\Domain\Workflow\WorkflowProjectionState;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;

readonly class WorkflowLifecycleCommandHandler implements CommandHandlerInterface
{

    public function __construct(
        private ForWorkflowDefinition $workflowDefinitionApp,
    )
    {
    }

    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof StartWorkflowFromScratch
            || $command instanceof AbortWorkflow
            || $command instanceof ReopenWorkflow;
    }

    public function handle(CommandInterface $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        return match ($command::class) {
            StartWorkflowFromScratch::class => $this->handleStartFromScratchWorkflow($command, $state),
            AbortWorkflow::class => $this->handleAbortWorkflow($command, $state),
            ReopenWorkflow::class => $this->handleReopenWorkflow($command, $state),
        };
    }

    private function handleStartFromScratchWorkflow(StartWorkflowFromScratch $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        if ($state->events->count() !== 0) {
            throw new \Exception("Cannot start the workflow from scratch, because it already has events");
        }
        $workflowDefinition = $this->workflowDefinitionApp->getDefinitionOrThrow($command->nodeTypeName, $command->workflowDefinitionId);

        return WorkflowEventsToPersist::with(
            new WorkflowWasStarted(
                nodeTypeName: $command->nodeTypeName,
                workflowDefinitionId: $command->workflowDefinitionId,
                node: $command->node,
                initialStep: $workflowDefinition->initialStep->id
            )
        );
    }

    private function handleAbortWorkflow(AbortWorkflow $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        if (!$state->isRunning()) {
            throw new \Exception("Cannot abort the workflow, because it was already aborted");
        }
        return WorkflowEventsToPersist::with(new WorkflowWasAborted());
    }

    private function handleReopenWorkflow(ReopenWorkflow $command, WorkflowProjectionState $state): WorkflowEventsToPersist
    {
        if ($state->isRunning()) {
            throw new \Exception("Cannot reopen the workflow, because it was already open");
        }
        return WorkflowEventsToPersist::with(new WorkflowWasReopened());
    }
}
