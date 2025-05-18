<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasAborted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasReopened;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;

class WorkflowLifecycleState
{
    static public function isRunning(WorkflowEvents $state): bool
    {
        $event = $state->findLastOrNullWhere(fn($event) => $event instanceof WorkflowWasAborted || $event instanceof WorkflowWasStarted || $event instanceof WorkflowWasReopened);

        // the last event of the 3 above must be Started or reopened for the workflow to be active.
        return $event instanceof WorkflowWasStarted || $event instanceof WorkflowWasReopened;
    }
}
