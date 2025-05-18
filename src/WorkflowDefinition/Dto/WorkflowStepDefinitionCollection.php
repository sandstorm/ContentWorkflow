<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Traversable;

/**
 * @extends \IteratorAggregate<WorkflowStepDefinition>
 */
readonly final class WorkflowStepDefinitionCollection implements \IteratorAggregate
{
    public function __construct(
        private array $items,
    )
    {
    }

    /**
     * @return Traversable<WorkflowStepDefinition>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function first(): WorkflowStepDefinition
    {
        foreach ($this->items as $item) {
            return $item;
        }
        throw new \RuntimeException("No items in collection");
    }
}
