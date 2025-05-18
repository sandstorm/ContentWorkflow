<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowTitle;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

readonly class WorkflowWasStarted implements WorkflowEventInterface
{
    public function __construct(
        private WorkflowDefinitionId $workflowDefinitionId,
        private WorkflowTitle $workflowTitle,
    )
    {
    }

    public static function fromArray(array $values): WorkflowEventInterface
    {
        return new self(
            WorkflowDefinitionId::fromString($values['workflowDefinitionId']),
            WorkflowTitle::fromString($values['workflowTitle']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'workflowDefinitionId' => $this->workflowDefinitionId,
            'workflowTitle' => $this->workflowTitle,
        ];
    }
}
