<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts;


use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

/**
 * Driven Port for loading workflow definitions
 */
interface ForWorkflowDefinition
{

    public function getDefinitionOrThrow(NodeTypeName $nodeTypeName, WorkflowDefinitionId $workflowDefinitionId): WorkflowDefinition;

    public function getDefinitions(NodeTypeName $nodeTypeName): array;
}
