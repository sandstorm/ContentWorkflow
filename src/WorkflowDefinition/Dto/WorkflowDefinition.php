<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

readonly final class WorkflowDefinition implements \Stringable
{
    public function __construct(
        public WorkflowDefinitionId   $id,
        public string                 $name,
        public string                 $description,
        public WorkflowStepCollection $steps,
    )
    {
    }

    public static function fromArray(WorkflowDefinitionId $id, array $in): self
    {
        $steps = [];
        foreach ($in['steps'] as $key => $inStep) {
            $steps[] = WorkflowStep::fromArray(WorkflowStepId::fromString($key), $inStep);
        }
        return new self(
            $id,
            $in['name'],
            $in['description'],
            new WorkflowStepCollection($steps),
        );
    }


    public function __toString()
    {
        return '[WorkflowDefinition: ' . $this->id->value . ']';
    }
}
