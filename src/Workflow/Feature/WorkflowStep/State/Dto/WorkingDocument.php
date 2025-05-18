<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\Dto;

readonly class WorkingDocument
{
    public function __construct(
        // TODO: current step
        public array $contentAsBlocknoteJson,
        public string $contentAsMarkdown,
        public string $contentAsHtml,
    )
    {
    }
}
