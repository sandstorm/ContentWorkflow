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
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State\WorkflowLifecycleState;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\WorkflowLifecycleCommandHandler;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\StepFinished;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event\WorkingDocumentSaved;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\WorkflowStepState;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\WorkflowStepCommandHandler;
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
final class WorkflowProjectionState
{
    use WorkflowLifecycleState;
    use WorkflowStepState;

    public function __construct(
        private readonly ForWorkflowDefinition $workflowDefinitionApp,
        public readonly WorkflowEvents         $events,
    ) {
    }
}
