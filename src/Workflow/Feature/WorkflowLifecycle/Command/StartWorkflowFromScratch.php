<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowTitle;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

readonly class StartWorkflowFromScratch implements CommandInterface
{
    public function __construct(
        public WorkflowDefinitionId $workflowDefinitionId,
        public WorkflowTitle $workflowTitle,
    )
    {
    }
}
