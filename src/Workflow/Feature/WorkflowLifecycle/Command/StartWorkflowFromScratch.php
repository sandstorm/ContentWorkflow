<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command;

use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\NodeConnection;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

readonly class StartWorkflowFromScratch implements CommandInterface
{
    public function __construct(
        public NodeTypeName $nodeTypeName,
        public WorkflowDefinitionId $workflowDefinitionId,
        public NodeConnection $node,
    )
    {
    }
}
