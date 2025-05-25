<?php

namespace Sandstorm\ContentWorkflow\Ui\Changes;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Ui\Domain\Model\AbstractChange;
use Neos\Neos\Ui\Domain\Model\ChangeInterface;

class HandleCommand implements ChangeInterface
{


    private NodeInterface $subject;

    public function setSubject(NodeInterface $subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function canApply()
    {
        return true;
    }

    public function apply()
    {
        // TODO: Implement apply() method.
    }
}
