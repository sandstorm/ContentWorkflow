<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepIds;

final class WorkflowStepDefinition
{
    public readonly WorkflowStepDefinitions $resolvedNextSteps;

    public function __construct(
        public WorkflowStepId  $id,
        public string          $name,
        public string          $description,
        public array           $ui,
        public WorkflowStepIds $nextSteps,
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
        foreach ($this->nextSteps as $nextStepId) {
            $resolved[] = $stepDefinitions->find($nextStepId);
        }
        $this->resolvedNextSteps = new WorkflowStepDefinitions($resolved);
    }

    public function jsonSerializeForUi(): array
    {
        return [
            'id' => $this->id->jsonSerialize(),
            'name' => $this->name,
            'description' => $this->description,
            'ui' => $this->ui,
            'nextSteps' => $this->nextSteps->jsonSerialize(),
        ];
    }
}
