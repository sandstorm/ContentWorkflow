<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepIds;

final class WorkflowStepDefinition
{
    public readonly WorkflowStepDefinitions $nextSteps;

    public function __construct(
        public WorkflowStepId  $id,
        public string          $name,
        public string          $description,
        public array           $ui,
        public WorkflowStepIds $nextStepIds,
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
            WorkflowStepIds::fromArray($in['nextSteps'] ?? []),
        );
    }

    public function resolveNextSteps(WorkflowStepDefinitions $stepDefinitions)
    {
        $resolved = [];
        foreach ($this->nextStepIds as $nextStepId) {
            $resolved[] = $stepDefinitions->find($nextStepId);
        }
        $this->nextSteps = new WorkflowStepDefinitions($resolved);
    }
}
