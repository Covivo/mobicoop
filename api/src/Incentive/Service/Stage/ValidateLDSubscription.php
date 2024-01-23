<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManager;

class ValidateLDSubscription extends UpdateStage
{
    /**
     * @var CarpoolPayment
     */
    private $_carpoolPayment;

    public function __construct(EntityManager $em, LongDistanceSubscription $subscription, CarpoolPayment $carpoolPayment, bool $pushOnlyMode = false)
    {
        $this->_em = $em;
        $this->_subscription = $subscription;
        $this->_carpoolPayment = $carpoolPayment;
        $this->_pushOnlyMode = $pushOnlyMode;
    }

    public function execute() {}
}
