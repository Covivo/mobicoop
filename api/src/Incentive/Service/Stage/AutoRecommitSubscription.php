<?php

namespace App\Incentive\Service\Stage;

class AutoRecommitSubscription extends RecommitSubscription
{
    public function execute()
    {
        $stage = new ResetSubscription($this->_em, $this->_subscription);
        $stage->execute();

        parent::execute();
    }
}
