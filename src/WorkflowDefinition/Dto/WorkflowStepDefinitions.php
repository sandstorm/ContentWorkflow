<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;
use Traversable;

/**
 * @extends \IteratorAggregate<WorkflowStepDefinition>
 */
readonly final class WorkflowStepDefinitions implements \IteratorAggregate
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

    public function find(WorkflowStepId $stepId): WorkflowStepDefinition
    {
        foreach ($this->items as $item) {
            if ($item->id->equals($stepId)) {
                return $item;
            }
        }
        throw new \RuntimeException("No item with id $stepId found");
    }

    public function jsonSerializeForUi(): array
    {
        return array_map(fn(WorkflowStepDefinition $def) => $def->jsonSerializeForUi(), $this->items);
    }
}
