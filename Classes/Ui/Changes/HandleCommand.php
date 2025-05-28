<?php

namespace Sandstorm\ContentWorkflow\Ui\Changes;

use Neos\Flow\Annotations as Flow;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Neos\Neos\Ui\Domain\Model\AbstractChange;
use Neos\Neos\Ui\Domain\Model\ChangeInterface;
use Neos\Neos\Ui\Domain\Model\FeedbackCollection;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\StartWorkflowFromScratch;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\NodeConnection;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\WorkflowProperties;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowTitle;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;
use Sandstorm\ContentWorkflow\Factory\WorkflowFactory;
use Sandstorm\ContentWorkflow\Ui\DataSource\Dto\WorkflowUiStatus;
use Sandstorm\ContentWorkflow\Ui\Feedback\WorkflowStateUpdatedFeedback;


class HandleCommand implements ChangeInterface
{
    private NodeInterface $subject;
    private string $commandId;
    private array $commandPayload;

    /**
     * @Flow\Inject
     * @var FeedbackCollection
     */
    protected $feedbackCollection;

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

            $workflowId = WorkflowId::fromString($this->commandPayload['workflowId']);
            $this->workflowApp->handle($workflowId, new StartWorkflowFromScratch(
                NodeTypeName::fromString($this->subject->getNodeType()->getName()),
                WorkflowDefinitionId::fromString($this->commandPayload['workflowDefinitionId']),
                NodeConnection::fromNode($this->subject),
            ));
        }

        $this->feedbackCollection->add(
            new WorkflowStateUpdatedFeedback(
                WorkflowUiStatus::buildForNode($this->subject, $this->workflowApp)
            )
        );
    }
}
