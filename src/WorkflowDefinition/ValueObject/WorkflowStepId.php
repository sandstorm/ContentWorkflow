<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject;

class WorkflowStepId implements \JsonSerializable
{
    /**
     * @var array<string,self>
     */
    private static array $instances = [];

    private static function instance(string $value): self
    {
        return self::$instances[$value] ??= new self($value);
    }

    public static function fromString(string $value): self
    {
        return self::instance($value);
    }

    private function __construct(public readonly string $value)
    {
    }

    public function __toString(): string
    {
        return '[WorkflowStep: ' . $this->value . ']';
    }

    public function equals(WorkflowStepId $other): bool
    {
        return $this->value === $other->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
