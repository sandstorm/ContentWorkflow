<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Factory;

use Doctrine\DBAL\Connection;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\EventStore\DoctrineAdapter\DoctrineEventStore;
use Neos\Flow\Annotations as Flow;
use Sandstorm\ContentWorkflow\Domain\Workflow\CoreWorkflowApp;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\WorkflowDefinitionApp;

#[Flow\Scope("singleton")]
class WorkflowFactory
{
    private DoctrineEventStore $eventStore;

    #[Flow\InjectConfiguration("workflows")]
    protected array $workflowsConfiguration;

    public function __construct(
        private Connection $connection,
        private readonly NodeTypeManager $nodeTypeManager,
    )
    {
        $this->eventStore = new DoctrineEventStore($this->connection, 'sandstorm_contentworkflow_events');
    }

    public function create(): ForWorkflow
    {
        return new CoreWorkflowApp(
            $this->eventStore,
            new WorkflowDefinitionApp($this->nodeTypeManager)
        );
    }

    public function setupEventStore(): void
    {
        $this->eventStore->setup();
    }
}
