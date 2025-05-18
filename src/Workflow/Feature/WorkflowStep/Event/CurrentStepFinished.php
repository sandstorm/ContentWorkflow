<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;

readonly class CurrentStepFinished implements WorkflowEventInterface
{
    public function __construct(
    )
    {
    }

    public static function fromArray(array $values): self
    {
        return new self(
        );
    }

    public function jsonSerialize(): array
    {
        return [
        ];
    }
}
