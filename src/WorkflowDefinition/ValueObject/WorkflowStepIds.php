<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject;

use Traversable;

class WorkflowStepIds implements \IteratorAggregate, \JsonSerializable
{
    /**
     * @var WorkflowStepId[]
     */
    private $values;

    public static function fromArray(array $in): self
    {
        $converted = [];
        foreach($in as $value) {
            $converted[] = WorkflowStepId::fromString($value);
        }
        return new self(...$converted);
    }

    private function __construct(WorkflowStepId ...$values)
    {
        $this->values = $values;
    }

    /**
     * @return Traversable<WorkflowStepId>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->values);
    }

    public function jsonSerialize(): array
    {
        return $this->values;
    }
}
