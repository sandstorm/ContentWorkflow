<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Event;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowTitle;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

readonly class WorkflowWasReopened implements WorkflowEventInterface
{
    public function __construct(
    )
    {
    }

    public static function fromArray(array $values): WorkflowEventInterface
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
