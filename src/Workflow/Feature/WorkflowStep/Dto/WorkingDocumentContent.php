<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Dto;

readonly class WorkingDocumentContent implements \JsonSerializable
{
    public function __construct(
        public array $contentAsBlocknoteJson,
        public string $contentAsMarkdown,
        public string $contentAsHtml,
    )
    {
    }

    public static function fromArray(array $values): self
    {
        return new self(
            contentAsBlocknoteJson: $values['contentAsBlocknoteJson'],
            contentAsMarkdown: $values['contentAsMarkdown'],
            contentAsHtml: $values['contentAsHtml'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'contentAsBlocknoteJson' => $this->contentAsBlocknoteJson,
            'contentAsMarkdown' => $this->contentAsMarkdown,
            'contentAsHtml' => $this->contentAsHtml,
        ];
    }
}
