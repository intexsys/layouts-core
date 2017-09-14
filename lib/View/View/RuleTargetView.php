<?php

namespace Netgen\BlockManager\View\View;

use Netgen\BlockManager\View\View;

class RuleTargetView extends View implements RuleTargetViewInterface
{
    public function getTarget()
    {
        return $this->parameters['target'];
    }

    public function getIdentifier()
    {
        return 'rule_target_view';
    }
}
