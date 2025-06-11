<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State;

use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\NodeConnection;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasAborted;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasReopened;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event\WorkflowWasStarted;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

trait WorkflowLifecycleState
{

    public function nodeConnection(): NodeConnection
    {
        return $this->events
            ->findFirst(WorkflowWasStarted::class)
            ->node;
    }

    public function isRunning(): bool
    {
        $event = $this->events
            ->findLastOrNullWhere(fn($event) =>
                $event instanceof WorkflowWasAborted
                || $event instanceof WorkflowWasStarted
                || $event instanceof WorkflowWasReopened
            );

        // the last event of the 3 above must be Started or reopened for the workflow to be active.
        return $event instanceof WorkflowWasStarted
            || $event instanceof WorkflowWasReopened;
    }

    public function definitionId(): WorkflowDefinitionId
    {
        return $this->events->findFirst(WorkflowWasStarted::class)->workflowDefinitionId;
    }

    public function nodeTypeName(): NodeTypeName
    {
        return $this->events
            ->findFirst(WorkflowWasStarted::class)
            ->nodeTypeName;
    }
    public function workflowDefinition(): WorkflowDefinition
    {
        return $this->workflowDefinitionApp->getDefinitionOrThrow(
            $this->nodeTypeName(),
            $this->definitionId()
        );
    }
}
