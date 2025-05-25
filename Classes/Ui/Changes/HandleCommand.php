<?php

namespace Sandstorm\ContentWorkflow\Ui\Changes;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Ui\Domain\Model\AbstractChange;
use Neos\Neos\Ui\Domain\Model\ChangeInterface;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\StartWorkflowFromScratch;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\WorkflowProperties;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowTitle;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;
use Sandstorm\ContentWorkflow\Factory\WorkflowFactory;

class HandleCommand implements ChangeInterface
{
    private NodeInterface $subject;
    private string $commandId;
    private array $commandPayload;

    public function __construct(
        protected readonly WorkflowFactory $workflowFactory,
        protected readonly ForWorkflow $workflowApp,
    ) {

    }

    public function setSubject(NodeInterface $subject)
    {
        $this->subject = $subject;
    }

    public function setCommandId(string $commandId)
    {
        $this->commandId = $commandId;
    }

    public function setCommandPayload(array $commandPayload)
    {
        $this->commandPayload = $commandPayload;
    }

    public function injectPersistenceManager() {
        // method needed, but we do not need to impl. it
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function canApply()
    {
        return true;
    }

    public function apply()
    {
        if ($this->commandId === 'StartWorkflow') {
            $this->workflowFactory->setupEventStore();

            $workflowId = WorkflowId::random();
            $this->workflowApp->handle($workflowId, new StartWorkflowFromScratch(
                WorkflowDefinitionId::fromString($this->commandPayload['workflowId']),
                WorkflowProperties::fromArray($this->commandPayload['properties']),
            ));
        }
        // TODO: Implement apply() method.
    }
}
