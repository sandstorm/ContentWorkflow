<?php

namespace Sandstorm\ContentWorkflow\Ui\Feedback;

use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Neos\Ui\Domain\Model\AbstractFeedback;
use Neos\Neos\Ui\Domain\Model\FeedbackInterface;
use Sandstorm\ContentWorkflow\Ui\DataSource\Dto\WorkflowUiStatus;

class WorkflowStateUpdatedFeedback extends AbstractFeedback
{

    public function __construct(
        protected readonly WorkflowUiStatus $workflowUiStatus
    )
    {
    }

    public function getType()
    {
        return 'Sandstorm.ContentWorkflow:WorkflowStateUpdated';
    }

    public function getDescription()
    {
        return 'Workflow State Updated';
    }

    public function isSimilarTo(FeedbackInterface $feedback)
    {
        return false;
    }

    public function serializePayload(ControllerContext $controllerContext)
    {
        return $this->workflowUiStatus->jsonSerialize();
    }
}
