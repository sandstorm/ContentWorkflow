<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;

readonly class FinishCurrentStep implements CommandInterface
{
    public function __construct(
    )
    {
    }

    public static function fromArray(array $body): self
    {
        return new self(
        );
    }
}
