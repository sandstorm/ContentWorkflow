<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler;


use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventsToPersist;
use Sandstorm\ContentWorkflow\Domain\Workflow\WorkflowProjectionState;

/**
 * Common interface for all Workflow Command command handlers
 *
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForWorkflow::handle()} to trigger commands.
 */
interface CommandHandlerInterface
{
    public function canHandle(
        CommandInterface $command
    ): bool;

    public function handle(
        CommandInterface $command,
        WorkflowProjectionState $state
    ): WorkflowEventsToPersist;
}
