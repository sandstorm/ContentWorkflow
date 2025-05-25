<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Command;

use Sandstorm\ContentWorkflow\Domain\Workflow\CommandHandler\CommandInterface;

readonly class AbortWorkflow implements CommandInterface
{
    public function __construct(
    )
    {
    }
}
