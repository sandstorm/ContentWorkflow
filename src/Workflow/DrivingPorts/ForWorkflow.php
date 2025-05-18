<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts;


use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEvents;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;

/**
 * Driving Port for core business operations
 *
 * This is the primary entry point into the domain from external code
 *
 * @api Main entry point into the core domain
 */
interface ForWorkflow
{
    public function hasWorkflow(WorkflowId $workflowId): bool;

    public function getWorkflowState(WorkflowId $workflowId): WorkflowEvents;

    public function handle(WorkflowId $workflowId, CommandInterface $command): void;

    public function definitions(): ForWorkflowDefinition;

}
