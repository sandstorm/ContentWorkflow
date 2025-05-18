<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow;


use Neos\EventStore\EventStoreInterface;
use Neos\EventStore\Helper\InMemoryEventStore;
use Neos\EventStore\Model\EventStream\ExpectedVersion;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventStore;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasAborted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasReopened;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\WorkflowLifecycleCommandHandler;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;

/**
 * Main implementation of core business logic
 *
 * This class implements the driving port {@see ForCoreGameLogic} and
 * coordinates the core domain operations. It:
 * - Contains the actual business logic
 * - Uses driven ports (e.g. ForLogging) to interact with external services
 * - Never directly depends on framework code
 *
 * @internal from the outside world, you'll always use the interface {@see ForCoreGameLogic}, except when constructing this application
 */
final class CoreWorkflowApp implements DrivingPorts\ForWorkflow
{
    private CommandHandler\CommandBus $commandBus;
    private WorkflowEventStore $workflowEventStore;

    public static function createInMemoryForTesting(ForWorkflowDefinition $workflowDefinitionApp): ForWorkflow
    {
        return new self(new InMemoryEventStore(), $workflowDefinitionApp);
    }

    public function __construct(
        EventStoreInterface   $eventStore,
        private readonly ForWorkflowDefinition $workflowDefinitionApp,
    ) {
        $eventNormalizer = EventStore\EventNormalizer::create([
            WorkflowWasStarted::class,
            WorkflowWasAborted::class,
            WorkflowWasReopened::class,
        ]);
        $this->workflowEventStore = new WorkflowEventStore($eventStore, $eventNormalizer);

        $this->commandBus = new CommandHandler\CommandBus(
            new WorkflowLifecycleCommandHandler($workflowDefinitionApp)
        );
    }

    public function hasWorkflow(WorkflowId $workflowId): bool
    {
        return $this->workflowEventStore->hasWorkflow($workflowId);
    }

    public function getWorkflowState(WorkflowId $workflowId): WorkflowEvents
    {
        [$state,] = $this->workflowEventStore->getWorkflowStateAndLastVersion($workflowId);
        return $state;
    }

    public function handle(WorkflowId $workflowId, CommandHandler\CommandInterface $command): void
    {
        [$state, $version] = $this->workflowEventStore->getWorkflowStateAndLastVersion($workflowId);
        $eventsToPublish = $this->commandBus->handle($command, $state);
        $this->workflowEventStore->commit($workflowId, $eventsToPublish, $version === null ? ExpectedVersion::NO_STREAM() : ExpectedVersion::fromVersion($version));
    }

    public function definitions(): ForWorkflowDefinition
    {
        return $this->workflowDefinitionApp;
    }
}
