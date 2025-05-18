<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

readonly class StepFinished implements WorkflowEventInterface
{
    public function __construct(
        public WorkflowStepId $stepId,
    )
    {
    }

    public static function fromArray(array $values): self
    {
        return new self(
            stepId: WorkflowStepId::fromString($values['stepId']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'stepId' => $this->stepId->value,
        ];
    }
}
