<?php

namespace Sandstorm\ContentWorkflow\Controller;

use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\StartWorkflowFromScratch;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowId;
use Sandstorm\ContentWorkflow\Domain\Workflow\ValueObject\WorkflowTitle;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;
use Sandstorm\ContentWorkflow\Factory\WorkflowFactory;

class BackendModuleController extends AbstractModuleController
{

    protected $defaultViewObjectName = FusionView::class;

    public function __construct(
        protected readonly ForWorkflow $workflowApp,
        protected readonly WorkflowFactory $workflowFactory,
    )
    {

    }

    public function indexAction()
    {
        $this->view->assign('definitions', $this->workflowApp->definitions()->getAll());
    }

    public function startWorkflowAction(array $form) {
        $this->workflowFactory->setupEventStore();

        $this->workflowApp->handle(WorkflowId::random(), new StartWorkflowFromScratch(
            WorkflowDefinitionId::fromString($form['definitionId']),
            WorkflowTitle::fromString($form['title']),
        ));

        $this->redirect('index');
    }
}
