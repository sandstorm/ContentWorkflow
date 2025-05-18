<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject;

class WorkflowTitle implements \JsonSerializable
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function __construct(public readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
