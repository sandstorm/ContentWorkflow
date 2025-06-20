<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow;


use Doctrine\DBAL\Connection;
use Neos\EventStore\EventStoreInterface;
use Neos\EventStore\Helper\InMemoryEventStore;
use Neos\EventStore\Model\EventEnvelope;
use Neos\EventStore\Model\EventStream\ExpectedVersion;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventStore;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasAborted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasReopened;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\WorkflowLifecycleCommandHandler;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\TransitionedToStep;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\WorkflowStepCommandHandler;
use Sandstorm\ContentWorkflow\Domain\Workflow\Projection\OverviewProjection\OverviewProjection;
use Sandstorm\ContentWorkflow\Domain\Workflow\SubscriptionEngine\DoctrineSubscriptionStore;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;
use Wwwision\SubscriptionEngine\Subscriber\Subscriber;
use Wwwision\SubscriptionEngine\Subscriber\Subscribers;
use Wwwision\SubscriptionEngine\Subscription\RunMode;
use Wwwision\SubscriptionEngine\Subscription\SubscriptionId;
use Wwwision\SubscriptionEngine\SubscriptionEngine;
use Wwwision\SubscriptionEngine\Tests\Mocks\InMemorySubscriptionStore;
use Wwwision\SubscriptionEngineNeosAdapter\NeosEventStoreAdapter;

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
    private SubscriptionEngine $subscriptionEngine;

    public static function createInMemoryForTesting(ForWorkflowDefinition $workflowDefinitionApp): ForWorkflow
    {
        return new self(new InMemoryEventStore(), $workflowDefinitionApp);
    }

    public function __construct(
        EventStoreInterface                    $eventStore,
        Connection                             $dbalConnection,
        private readonly ForWorkflowDefinition $workflowDefinitionApp,
    )
    {
        $eventNormalizer = EventStore\EventNormalizer::create([
            WorkflowWasStarted::class,
            WorkflowWasAborted::class,
            WorkflowWasReopened::class,

            WorkingDocumentSaved::class,
            TransitionedToStep::class,
        ]);
        $this->workflowEventStore = new WorkflowEventStore($eventStore, $eventNormalizer);

        $this->commandBus = new CommandHandler\CommandBus(
            new WorkflowLifecycleCommandHandler($workflowDefinitionApp),
            new WorkflowStepCommandHandler(),
        );

        $overviewProjection = new OverviewProjection();
        $subscribers = Subscribers::fromArray([
            Subscriber::create(
                "overview",
                fn(EventEnvelope $eventEnvelope) => $overviewProjection->handle(
                    $eventNormalizer->denormalize($eventEnvelope),
                    $eventEnvelope
                ),
                RunMode::FROM_BEGINNING, //?? ONCE?? -> one time migration
                fn() => $overviewProjection->setup(),
                fn() => $overviewProjection->reset(),
            )
        ]);


        // TODO: USE OTHER STORE HERE (PERSISTENT!!)
        $subscriptionStore = new DoctrineSubscriptionStore($dbalConnection, tableName: 'workflow_subscriptions');

        $this->subscriptionEngine = new SubscriptionEngine(
            new NeosEventStoreAdapter($eventStore),
            $subscriptionStore,
            $subscribers,
        );
    }

    public function setup(): void
    {
        $this->subscriptionEngine->setup();

        // SOLLTE NICHT PER REQUEST SEIN.
        $this->subscriptionEngine->boot();
    }

    public function hasWorkflow(WorkflowId $workflowId): bool
    {
        return $this->workflowEventStore->hasWorkflow($workflowId);
    }

    public function stateFor(WorkflowId $workflowId): WorkflowProjectionState
    {
        [$events,] = $this->workflowEventStore->getEventsAndLastVersionForWorkflow($workflowId);
        return new WorkflowProjectionState($this->workflowDefinitionApp, $events);
    }


    public function emptyState(): WorkflowProjectionState
    {
        return new WorkflowProjectionState($this->workflowDefinitionApp, WorkflowEvents::fromArray([]));
    }

    public function handle(WorkflowId $workflowId, CommandHandler\CommandInterface $command): void
    {
        [$events, $version] = $this->workflowEventStore->getEventsAndLastVersionForWorkflow($workflowId);
        $state = new WorkflowProjectionState($this->workflowDefinitionApp, $events);
        $eventsToPublish = $this->commandBus->handle($command, $state);
        $this->workflowEventStore->commit($workflowId, $eventsToPublish, $version === null ? ExpectedVersion::NO_STREAM() : ExpectedVersion::fromVersion($version));

        // TODO: correct??
        $this->subscriptionEngine->catchUpActive();
        // TODO: onEvent -> wording clash...
    }

    public function definitions(): ForWorkflowDefinition
    {
        return $this->workflowDefinitionApp;
    }

}
