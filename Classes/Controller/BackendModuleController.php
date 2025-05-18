<?php

namespace Sandstorm\ContentWorkflow\Controller;

use Imagine\Filter\Basic\Save;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Security\Context;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sandstorm\ContentWorkflow\Domain\Workflow\DrivingPorts\ForWorkflow;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command\StartWorkflowFromScratch;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command\FinishStep;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\Command\SaveWorkingDocument;
use Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\WorkflowStepState;
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
        protected readonly Context $securityContext,
        protected readonly ResourceManager $resourceManager,
    )
    {

    }

    public function indexAction()
    {
        $this->view->assign('definitions', $this->workflowApp->definitions()->getAll());
    }

    public function startWorkflowAction(array $form) {
        $this->workflowFactory->setupEventStore();

        $workflowId = WorkflowId::random();
        $this->workflowApp->handle($workflowId, new StartWorkflowFromScratch(
            WorkflowDefinitionId::fromString($form['definitionId']),
            WorkflowTitle::fromString($form['title']),
        ));

        $this->redirect('show', null, null, ['workflowId' => $workflowId->value]);
    }

    public function showAction(string $workflowId)
    {
        $workflowId = WorkflowId::fromString($workflowId);
        $state = $this->workflowApp->getWorkflowState($workflowId);

        $steps = WorkflowStepState::stepListWithCurrentState($state, $this->workflowApp->definitions());
        $this->view->assign('steps', $steps);
        $this->view->assign('workflowId', $workflowId);
        $this->view->assign('csrfToken', $this->securityContext->getCsrfProtectionToken());
        $this->view->assign('dispatchCommandFromJsEndpoint', $this->uriBuilder->uriFor('dispatchCommandFromJs', ['workflowId' => $workflowId->value]));
        $this->view->assign('css', $this->resourceManager->getPublicPackageResourceUri('Sandstorm.ContentWorkflow', 'built/BackendModule.css'));
        $this->view->assign('currentStepId', WorkflowStepState::currentStep($state, $this->workflowApp->definitions())->id);
        $this->view->assign('currentWorkingDocument', WorkflowStepState::currentWorkingDocument($state));
    }

    public function dispatchCommandFromJsAction(string $workflowId)
    {
        $workflowId = WorkflowId::fromString($workflowId);
        $body = $this->request->getHttpRequest()->getParsedBody();
        switch ($body['command']) {
            case 'SaveWorkingDocument':
                $this->workflowApp->handle($workflowId, SaveWorkingDocument::fromArray($body));
                break;
            case 'FinishCurrentStep':
                $this->workflowApp->handle($workflowId, FinishStep::fromArray($body));
                break;
            default:
                throw new \Exception('Unknown command: ' . $body['command']);
        }

        return 'OK';
    }
}
