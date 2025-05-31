<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

readonly class TransitionedToStep implements WorkflowEventInterface
{
    public function __construct(
        public WorkflowStepId $completedStepId,
        public WorkflowStepId $nextStepId,
    )
    {
    }

    public static function fromArray(array $values): self
    {
        return new self(
            completedStepId: WorkflowStepId::fromString($values['completedStepId']),
            nextStepId: WorkflowStepId::fromString($values['nextStepId']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'completedStepId' => $this->completedStepId->value,
            'nextStepId' => $this->nextStepId->value,
        ];
    }
}
