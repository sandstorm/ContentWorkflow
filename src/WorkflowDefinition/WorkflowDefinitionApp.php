<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

class WorkflowDefinitionApp implements ForWorkflowDefinition
{
    /**
     * @var array<string,WorkflowDefinition>
     */
    private array $definitions;

    public function __construct(
        WorkflowDefinition ...$workflowDefinitions
    )
    {
        $definitions = [];
        foreach ($workflowDefinitions as $workflowDefinition) {
            $definitions[$workflowDefinition->id->value] = $workflowDefinition;
        }
        $this->definitions = $definitions;
    }

    public static function createFromArray(array $definitions): self
    {
        $converted = [];
        foreach ($definitions as $definitionId => $definition) {
            $converted[] = WorkflowDefinition::fromArray(WorkflowDefinitionId::fromString($definitionId), $definition);
        }
        return new self(...$converted);
    }

    public function getWorkflowDefinitionOrThrow(WorkflowDefinitionId $definitionId): WorkflowDefinition
    {
        return $this->definitions[$definitionId->value] ?? throw new \InvalidArgumentException("No such workflow definition: $definitionId");
    }

    public function getAll(): iterable
    {
        return array_values($this->definitions);
    }
}
