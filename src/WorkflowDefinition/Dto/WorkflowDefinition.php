<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

readonly final class WorkflowDefinition implements \Stringable
{
    public function __construct(
        public WorkflowDefinitionId    $id,
        public string                  $name,
        public string                  $description,
        public WorkflowStepDefinitions $stepDefinitions,
    )
    {
        foreach ($this->stepDefinitions as $stepDefinition) {
            $stepDefinition->resolveNextSteps($this->stepDefinitions);
        }
    }

    public static function fromArray(WorkflowDefinitionId $id, array $in): self
    {
        try {
            $steps = [];
            foreach ($in['steps'] as $key => $inStep) {
                $steps[] = WorkflowStepDefinition::fromArray(WorkflowStepId::fromString($key), $inStep);
            }
            return new self(
                $id,
                $in['name'],
                $in['description'],
                new WorkflowStepDefinitions($steps),
            );
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($id . ': Invalid workflow definition. See nested exception for details.', 0, $e);
        }
    }


    public function __toString()
    {
        return '[WorkflowDefinition: ' . $this->id->value . ']';
    }

    public function jsonSerializeForUi()
    {
        return [
            'id' => $this->id->jsonSerialize(),
            'name' => $this->name,
            'description' => $this->description,
            'steps' => $this->stepDefinitions->jsonSerializeForUi(),
        ];
    }
}
