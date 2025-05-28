<?php

namespace Sandstorm\ContentWorkflow\Domain\WorkflowDefinition;

use Neos\ContentRepository\Domain\NodeType\NodeTypeName;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\DrivingPorts\ForWorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowDefinition;
use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\ValueObject\WorkflowDefinitionId;

class WorkflowDefinitionApp implements ForWorkflowDefinition
{
    public function __construct(
        private readonly NodeTypeManager $nodeTypeManager,
    )
    {
    }
    public function getDefinitionOrThrow(NodeTypeName $nodeTypeName, WorkflowDefinitionId $workflowDefinitionId): WorkflowDefinition
    {
        $nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName->getValue());
        $arr = $nodeType->getConfiguration('options.workflows.' . $workflowDefinitionId->value);
        return WorkflowDefinition::fromArray($workflowDefinitionId, $arr);
    }

    /**
     * @return WorkflowDefinition[]
     */
    public function getDefinitions(NodeTypeName $nodeTypeName): array
    {
        $nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName->getValue());
        $arr = $nodeType->getConfiguration('options.workflows');
        $result = [];
        foreach ($arr as $id => $def) {
            $result[] = WorkflowDefinition::fromArray(WorkflowDefinitionId::fromString($id), $def);
        }

        return $result;
    }
}
