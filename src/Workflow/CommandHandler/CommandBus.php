<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler;


use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventsToPersist;

/**
 * Implementation Detail of {@see CoreGameLogicApp::handle}, which does the command dispatching to the different
 * {@see CommandHandlerInterface} implementation.
 *
 * @internal
 */
final readonly class CommandBus
{
    /**
     * @var CommandHandlerInterface[]
     */
    private array $handlers;

    public function __construct(
        CommandHandlerInterface ...$handlers
    ) {
        $this->handlers = $handlers;
    }

    /**
     * The handler only calculate which events they want to have published,
     * but do not do the publishing themselves
     *
     * @param CommandInterface $command
     * @param WorkflowEvents $state
     * @return WorkflowEventsToPersist
     */
    public function handle(CommandInterface $command, WorkflowEvents $state): WorkflowEventsToPersist
    {
        // multiple handlers must not handle the same command
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($command)) {
                return $handler->handle($command, $state);
            }
        }
        throw new \RuntimeException(sprintf('No handler found for Command "%s"', get_debug_type($command)), 1649582778);
    }
}
