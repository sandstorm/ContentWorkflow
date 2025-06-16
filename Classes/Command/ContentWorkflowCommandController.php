<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Command;

use Doctrine\DBAL\Connection;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\EventStore\DoctrineAdapter\DoctrineEventStore;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sandstorm\ContentWorkflow\Domain\Workflow\CoreWorkflowApp;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\WorkflowDefinitionApp;
use Sandstorm\ContentWorkflow\Factory\WorkflowFactory;

#[Flow\Scope("singleton")]
class ContentWorkflowCommandController extends CommandController
{
    #[Flow\Inject]
    protected ForWorkflow $workflow;

    public function setupCommand()
    {
        $this->workflow->setup();
    }
}
