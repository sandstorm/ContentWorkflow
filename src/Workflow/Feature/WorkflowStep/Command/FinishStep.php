<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

readonly class FinishStep implements CommandInterface
{
    public function __construct(
        public WorkflowStepId $stepId,
    )
    {
    }

    public static function fromArray(array $body): self
    {
        return new self(
            stepId: WorkflowStepId::fromString($body['stepId']),
        );
    }
}
