<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Event;

use Sandstorm\ContentWorkflow\Domain\Workflow\EventStore\WorkflowEventInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;

readonly class WorkingDocumentSaved implements WorkflowEventInterface
{
    public function __construct(
        // TODO: current step
        public WorkingDocumentContent $content,
    )
    {
    }

    public static function fromArray(array $values): self
    {
        return new self(
            content: WorkingDocumentContent::fromArray($values['content']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'content' => $this->content->jsonSerialize(),
        ];
    }
}
