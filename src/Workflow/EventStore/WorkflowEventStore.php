<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\EventStore;

use Neos\EventStore\EventStoreInterface;
use Neos\EventStore\Model\Event\Version;
use Neos\EventStore\Model\Events;
use Neos\EventStore\Model\EventStream\ExpectedVersion;
use Sandstorm\ContentWorkflow\Domain\Workflow\CoreWorkflowApp;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;

/**
 * @internal from the outside world, you'll always use {@see CoreWorkflowApp})
 */
final readonly class WorkflowEventStore
{

    public function __construct(
        private EventStoreInterface $eventStore,
        private EventNormalizer     $eventNormalizer
    )
    {
    }

    public function hasWorkflow(WorkflowId $workflowId): bool
    {
        foreach ($this->eventStore->load($workflowId->streamName()) as $event) {
            // we found at least one event; so the workflow exists.
            return true;
        }

        // we did not find any events for this workflow, so it does not exist
        return false;
    }

    /**
     * @return array{0: WorkflowEvents, 1: Version|null}
     */
    public function getEventsAndLastVersionForWorkflow(WorkflowId $workflowId): array
    {
        $WorkflowEvents = [];
        $version = null;
        foreach ($this->eventStore->load($workflowId->streamName()) as $eventEnvelope) {
            $WorkflowEvents[] = $this->eventNormalizer->denormalize($eventEnvelope->event);
            $version = $eventEnvelope->version;
        }
        return [WorkflowEvents::fromArray($WorkflowEvents), $version];
    }

    public function commit(WorkflowId $workflowId, WorkflowEventsToPersist $events, ExpectedVersion $expectedVersion): void
    {
        $this->eventStore->commit(
            $workflowId->streamName(),
            $this->enrichAndNormalizeEvents($events),
            $expectedVersion
        );
    }

    private function enrichAndNormalizeEvents(WorkflowEventsToPersist $events): Events
    {
        // TODO: $initiatingUserId = $this->authProvider->getAuthenticatedUserId() ?? UserId::forSystemUser();
        // TODO: $initiatingTimestamp = $this->clock->now();

        return Events::fromArray($events->map(function (DecoratedEvent|WorkflowEventInterface $event) {
            return $this->eventNormalizer->normalize($event);
        }));
    }

}
