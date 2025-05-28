<?php
declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Ui\DataSource\Dto;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class Button implements \JsonSerializable
{
    public function __construct(
        private string $id,
        private string $label,
    )
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
        ];
    }
}
