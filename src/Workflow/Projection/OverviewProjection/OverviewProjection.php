<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Projection\OverviewProjection;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;

class OverviewProjection
{


    public function handle(WorkflowEventInterface $event, \Neos\EventStore\Model\EventEnvelope $eventEnvelope): void
    {
        match($event::class) {
            WorkflowWasStarted::class => $this->handleWorkflowWasStarted($event)
        };
    }

    private function handleWorkflowWasStarted(WorkflowWasStarted $event)
    {

    }

    public function setup()
    {

    }

    public function reset()
    {

    }
}
