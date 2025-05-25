<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto;

class WorkflowProperties implements \JsonSerializable
{
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    private function __construct(public readonly array $values)
    {
    }

    public function jsonSerialize(): array
    {
        return $this->values;
    }
}
