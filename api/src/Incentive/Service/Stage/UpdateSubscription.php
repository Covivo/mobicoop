<?php

namespace App\Incentive\Service\Stage;

abstract class UpdateSubscription extends Stage
{
    /**
     * @var bool
     */
    protected $_pushOnlyMode;

    protected function _build()
    {
        $this->_setApiProvider();
    }
}
