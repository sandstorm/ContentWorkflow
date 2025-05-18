<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

readonly final class WorkflowStepDefinition
{
    public function __construct(
        public WorkflowStepId $id,
        public string         $name,
        public string         $description,
        public array          $ui,
    )
    {
    }

    public static function fromArray(WorkflowStepId $id, array $in): self
    {
        return new self(
            $id,
            $in['name'],
            $in['description'],
            $in['ui'] ?? [],
        );
    }
}
