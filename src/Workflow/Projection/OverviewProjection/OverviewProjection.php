<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Projection\OverviewProjection;

use Wwwision\SubscriptionEngine\EventStore\Event;
use Wwwision\SubscriptionEngine\Subscriber\EventHandler;

class OverviewProjection implements EventHandler
{

    public function __invoke(Event $event): void
    {
        // TODO: EVENT BRIDGING -> HOW???
    }
}
