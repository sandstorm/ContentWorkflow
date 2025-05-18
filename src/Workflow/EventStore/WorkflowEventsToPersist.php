<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\EventStore;

/**
 * A set of Game "domain events" which should be appended to the event stream.
 *
 * This is only used on the WRITE SIDE of the event stream.
 *
 * @implements \IteratorAggregate<WorkflowEventInterface|DecoratedEvent>
 * @internal only used during event publishing (from within command handlers) - and their implementation is not API
 */
final readonly class WorkflowEventsToPersist implements \IteratorAggregate, \Countable
{
    /**
     * @var non-empty-array<WorkflowEventInterface|DecoratedEvent>
     */
    public array $events;

    private function __construct(WorkflowEventInterface|DecoratedEvent ...$events)
    {
        /** @var non-empty-array<WorkflowEventInterface|DecoratedEvent> $events */
        $this->events = $events;
    }

    public static function with(WorkflowEventInterface|DecoratedEvent ...$events): self
    {
        return new self(...$events);
    }

    public static function empty(): self
    {
        return new self();
    }

    public function withAppendedEvents(WorkflowEventInterface|DecoratedEvent ...$events): self
    {
        return new self(...$this->events, ...$events);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->events;
    }

    /**
     * @template T
     * @param \Closure(WorkflowEventInterface|DecoratedEvent $event): T $callback
     * @return non-empty-array<T>
     */
    public function map(\Closure $callback): array
    {
        return array_map($callback, $this->events);
    }

    public function count(): int
    {
        return count($this->events);
    }
}
