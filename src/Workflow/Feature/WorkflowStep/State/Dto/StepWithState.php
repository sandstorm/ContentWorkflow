<?php

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowStep\State\Dto;

use Sandstorm\ContentWorkflow\Domain\WorkflowDefinition\Dto\WorkflowStepDefinition;

final readonly class StepWithState
{
    public function __construct(
        public WorkflowStepDefinition $definition,
        public bool                   $alreadyExecuted,
    )
    {
    }
}
