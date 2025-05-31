<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowStepId;

/**
 * Finish one step and transition to one of the next ones.
 */
readonly class TransitionToStep implements CommandInterface
{
    public function __construct(
        public WorkflowStepId $nextStepId,
    )
    {
    }

    public static function fromArray(array $body): self
    {
        return new self(
            nextStepId: WorkflowStepId::fromString($body['nextStepId']),
        );
    }
}
