<?php

namespace Sandstorm\ContentWorkflow\Ui\DataSource\Dto;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Neos\Flow\Annotations as Flow;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowStepDefinition;

#[Flow\Proxy(false)]
final readonly class WorkflowUiStatus implements \JsonSerializable
{

    public static function empty(): self
    {
        return new self(WorkflowControl::empty());
    }

    public static function buildForNode(NodeInterface $node, ForWorkflow $workflowApp): WorkflowUiStatus
    {
        $currentlyRunningWorkflowId = $node->getProperty('contentWorkflow_currentlyRunningWorkflow');

        $startWorkflowButtons = [];
        $nextWorkflowStepButtons = [];


        if ($currentlyRunningWorkflowId) {
            $currentlyRunningWorkflowId = WorkflowId::fromString($currentlyRunningWorkflowId);
            $state = $workflowApp->stateFor($currentlyRunningWorkflowId);
            $workflowDefinition = $state->workflowDefinition();
        } else {
            $state = $workflowApp->emptyState();
            $workflowDefinition = null;
        }

        if (!$state->isRunning()) {
            foreach ($workflowApp->definitions()->getDefinitions(NodeTypeName::fromString($node->getNodeType()->getName())) as $definition) {
                assert($definition instanceof WorkflowDefinition);
                $startWorkflowButtons[] = new Button(
                    id: $definition->id->value,
                    label: $definition->name,
                );
            }
        } else {
            foreach ($state->currentStepOrNull()->nextSteps as $nextStep) {
                assert($nextStep instanceof WorkflowStepDefinition);
                $nextWorkflowStepButtons[] = new Button(
                    id: $nextStep->id->value,
                    label: $nextStep->name,
                );
            }
        }

        return new self(
            workflowControl: new WorkflowControl(
                startWorkflowButtons: $startWorkflowButtons,
                nextWorkflowStepButtons: $nextWorkflowStepButtons,
                isWorkflowRunning: $state->isRunning(),
                currentWorkflowName: $workflowDefinition?->name,
                currentWorkflowDescription: $workflowDefinition?->description,
                currentWorkflowStepName: $state->currentStepOrNull()?->name,
                currentWorkflowStepDescription: $state->currentStepOrNull()?->description,
            )
        );
    }

    public function __construct(
        private readonly WorkflowControl $workflowControl,
    )
    {

    }

    public function jsonSerialize(): array
    {
        return [
            'workflowControl' => $this->workflowControl->jsonSerialize(),
        ];
    }
}
