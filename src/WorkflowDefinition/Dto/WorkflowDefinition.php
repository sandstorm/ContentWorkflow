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
        public WorkflowStepDefinition  $initialStep
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
            $stepDefinitions = new WorkflowStepDefinitions($steps);
            $initialStep = $stepDefinitions->find(WorkflowStepId::fromString($in['initialStep']));
            return new self(
                $id,
                $in['name'],
                $in['description'],
                $stepDefinitions,
                $initialStep,
            );
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($id . ': Invalid workflow definition. See nested exception for details.', 0, $e);
        }
    }


    public function __toString()
    {
        return '[WorkflowDefinition: ' . $this->id->value . ']';
    }
}
