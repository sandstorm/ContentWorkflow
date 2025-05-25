<?php

namespace Sandstorm\ContentWorkflow\Tests\Domain\Workflow;

use Neos\Flow\Tests\UnitTestCase;
use Sandstorm\ContentWorkflow\Domain\Workflow\CoreWorkflowApp;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\AbortWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\ReopenWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\StartWorkflowFromScratch;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto\WorkflowProperties;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\State\WorkflowLifecycleState;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowStepDefinitionCollection;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\WorkflowDefinitionApp;

class WorkflowBasicLifecycleTest extends UnitTestCase
{
    private ForWorkflow $workflowApp;
    private WorkflowId $workflowId;

    protected function setUp(): void {
        $workflow1 = new WorkflowDefinition(
            id: WorkflowDefinitionId::fromString('wf1'),
            name: 'Blog Post creation',
            description: 'A workflow for blog posts',
            stepDefinitions: new WorkflowStepDefinitionCollection([]),
        );

        $this->workflowApp = CoreWorkflowApp::createInMemoryForTesting(new WorkflowDefinitionApp($workflow1));
        $this->workflowId = WorkflowId::fromString('foo');
    }

    /**
     * @test
     */
    public function workflowCreation(): void
    {
        $this->workflowApp->handle($this->workflowId, new StartWorkflowFromScratch(
            workflowDefinitionId: WorkflowDefinitionId::fromString('wf1'),
            workflowProperties: WorkflowProperties::fromArray(['title' => 'Blog Post about Event Sourcing']),
        ));

        $this->assertEquals($this->workflowApp->getWorkflowState($this->workflowId)->count(), 1, 'Event count mismatch');
    }

    /**
     * @test
     */
    public function workflowCreation_errorIfAlreadyExists(): void
    {
        $this->workflowApp->handle($this->workflowId, new StartWorkflowFromScratch(
            workflowDefinitionId: WorkflowDefinitionId::fromString('wf1'),
            workflowProperties: WorkflowProperties::fromArray(['title' => 'Blog Post about Event Sourcing']),
        ));

        $this->expectException(\Exception::class);
        $this->workflowApp->handle($this->workflowId, new StartWorkflowFromScratch(
            workflowDefinitionId: WorkflowDefinitionId::fromString('wf1'),
            workflowProperties: WorkflowProperties::fromArray(['title' => 'Blog Post about Event Sourcing']),
        ));
    }

    /**
     * @test
     */
    public function workflowAbortAndReopen(): void
    {
        $this->workflowApp->handle($this->workflowId, new StartWorkflowFromScratch(
            workflowDefinitionId: WorkflowDefinitionId::fromString('wf1'),
            workflowProperties: WorkflowProperties::fromArray(['title' => 'Blog Post about Event Sourcing']),
        ));

        $this->workflowApp->handle($this->workflowId, new AbortWorkflow());
        $state = $this->workflowApp->getWorkflowState($this->workflowId);
        $this->assertFalse(WorkflowLifecycleState::isRunning($state));

        $this->workflowApp->handle($this->workflowId, new ReopenWorkflow());
        $state = $this->workflowApp->getWorkflowState($this->workflowId);
        $this->assertTrue(WorkflowLifecycleState::isRunning($state));
    }

    /**
     * @test
     */
    public function workflowAbort_errorIfAlreadyClosed(): void
    {
        $this->workflowApp->handle($this->workflowId, new StartWorkflowFromScratch(
            workflowDefinitionId: WorkflowDefinitionId::fromString('wf1'),
            workflowProperties: WorkflowProperties::fromArray(['title' => 'Blog Post about Event Sourcing']),
        ));

        $this->workflowApp->handle($this->workflowId, new AbortWorkflow());

        $this->expectException(\Exception::class);
        $this->workflowApp->handle($this->workflowId, new AbortWorkflow());
    }

    /**
     * @test
     */
    public function workflowReopen_errorIfAlreadyOpened(): void
    {
        $this->workflowApp->handle($this->workflowId, new StartWorkflowFromScratch(
            workflowDefinitionId: WorkflowDefinitionId::fromString('wf1'),
            workflowProperties: WorkflowProperties::fromArray(['title' => 'Blog Post about Event Sourcing']),
        ));

        $this->workflowApp->handle($this->workflowId, new AbortWorkflow());
        $this->workflowApp->handle($this->workflowId, new ReopenWorkflow());

        $this->expectException(\Exception::class);
        $this->workflowApp->handle($this->workflowId, new ReopenWorkflow());
    }
}
