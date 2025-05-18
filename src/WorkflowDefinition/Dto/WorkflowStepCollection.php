<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Traversable;

readonly final class WorkflowStepCollection implements \IteratorAggregate
{
    public function __construct(
        private array $items,
    )
    {
    }

    /**
     * @return Traversable<WorkflowStep>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }
}
