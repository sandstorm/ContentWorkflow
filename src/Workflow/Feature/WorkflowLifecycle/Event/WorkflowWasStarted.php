<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event;

use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\NodeConnection;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\WorkflowProperties;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

readonly class WorkflowWasStarted implements WorkflowEventInterface
{
    public function __construct(
        public NodeTypeName $nodeTypeName,
        public WorkflowDefinitionId $workflowDefinitionId,
        public NodeConnection $node,
    )
    {
    }

    public static function fromArray(array $values): WorkflowEventInterface
    {
        return new self(
            NodeTypeName::fromString($values['nodeTypeName']),
            WorkflowDefinitionId::fromString($values['workflowDefinitionId']),
            NodeConnection::fromString($values['node']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'nodeTypeName' => $this->nodeTypeName->jsonSerialize(),
            'workflowDefinitionId' => $this->workflowDefinitionId->jsonSerialize(),
            'node' => $this->node->jsonSerialize(),
        ];
    }
}
