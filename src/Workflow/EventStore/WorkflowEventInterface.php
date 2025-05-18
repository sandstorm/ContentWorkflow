<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\EventStore;

/**
 * Common interface for all Game "domain events"
 *
 * @api
 */
interface WorkflowEventInterface extends \JsonSerializable
{
    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): WorkflowEventInterface;

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array;
}
