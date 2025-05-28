<?php

namespace Sandstorm\ContentWorkflow\Ui\DataSource\Dto;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class WorkflowControl implements \JsonSerializable
{
    public static function empty()
    {
        return new self([], [], false, '', '', '', '');
    }

    public function __construct(
        private array  $startWorkflowButtons,
        private array  $nextWorkflowStepButtons,
        private bool   $isWorkflowRunning,
        private ?string $currentWorkflowName,
        private ?string $currentWorkflowDescription,
        private ?string $currentWorkflowStepName,
        private ?string $currentWorkflowStepDescription,
    )
    {
        foreach ($this->startWorkflowButtons as $button) {
            assert($button instanceof Button);
        }
        foreach ($this->nextWorkflowStepButtons as $button) {
            assert($button instanceof Button);
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'startWorkflowButtons' => $this->startWorkflowButtons,
            'nextWorkflowStepButtons' => $this->nextWorkflowStepButtons,
            'isWorkflowRunning' => $this->isWorkflowRunning,
            'currentWorkflowName' => $this->currentWorkflowName,
            'currentWorkflowDescription' => $this->currentWorkflowDescription,
            'currentWorkflowStepName' => $this->currentWorkflowStepName,
            'currentWorkflowStepDescription' => $this->currentWorkflowStepDescription,
        ];
    }
}
