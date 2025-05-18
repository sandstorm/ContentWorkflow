<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts;


use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

/**
 * Driven Port for loading workflow definitions
 */
interface ForWorkflowDefinition
{
    public function getWorkflowDefinitionOrThrow(WorkflowDefinitionId $definitionId): WorkflowDefinition;
    public function getAll(): iterable;
}
