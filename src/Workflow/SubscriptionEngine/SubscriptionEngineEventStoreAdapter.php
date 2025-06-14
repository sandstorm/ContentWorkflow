<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\SubscriptionEngine;

use Neos\EventStore\EventStoreInterface;
use Neos\EventStore\Model\Event\SequenceNumber;
use Neos\EventStore\Model\EventStream\VirtualStreamName;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\EventNormalizer;
use Wwwision\SubscriptionEngine\EventStore\EventStore;
use Wwwision\SubscriptionEngine\Subscription\Position;

class SubscriptionEngineEventStoreAdapter implements EventStore
{

    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly EventNormalizer $eventNormalizer,
    )
    {
    }

    public function read(Position $startPosition): iterable
    {
        foreach ($this->eventStore->load(VirtualStreamName::all(), null)->withMinimumSequenceNumber(SequenceNumber::fromInteger($startPosition->value)) as $coreEventEnvelope) {
            $this->eventNormalizer->denormalize($coreEventEnvelope->event);

        }
    }

    public function lastPosition(): Position
    {
        foreach ($this->eventStore->load(VirtualStreamName::all())->backwards()->limit(1) as $pos) {
            return Position::fromInteger($pos->sequenceNumber->value);
        }

        return Position::none();
    }
}
