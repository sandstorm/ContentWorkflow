<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto\WorkingDocumentContent;

readonly class SaveWorkingDocument implements CommandInterface
{
    public function __construct(
        // TODO: current step
        public WorkingDocumentContent $content,
    )
    {
    }

    public static function fromArray(array $body): self
    {
        return new self(
            content: WorkingDocumentContent::fromArray($body),
        );
    }
}
