<?php

namespace Sandstorm\ContentWorkflow\Ui\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Ui\DataSource\Dto\Button;
use Sandstorm\ContentWorkflow\Ui\DataSource\Dto\WorkflowControl;
use Sandstorm\ContentWorkflow\Ui\DataSource\Dto\WorkflowUiStatus;

class WorkflowStatusDataSource extends AbstractDataSource
{
    protected static $identifier = 'workflowStatus';

    public function __construct(
        protected readonly ForWorkflow $workflowApp,
    )
    {
    }

    public function getData(?NodeInterface $node = null, array $arguments = [])
    {
        if (!$node) {
            return WorkflowUiStatus::empty();
        }

        return WorkflowUiStatus::buildForNode($node, $this->workflowApp);
    }
}
