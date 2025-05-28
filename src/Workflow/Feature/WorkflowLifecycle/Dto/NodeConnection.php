<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\Feature\WorkflowLifecycle\Dto;

use Neos\ContentRepository\Domain\Model\NodeInterface;

class NodeConnection implements \JsonSerializable
{
    public static function fromNode(NodeInterface $node): self
    {
        return new self($node->getContextPath());
    }

    public static function fromString(string $nodeId): self
    {
        return new self($nodeId);
    }

    private function __construct(public readonly string $nodeId)
    {
    }

    public function jsonSerialize(): string
    {
        return $this->nodeId;
    }
}
