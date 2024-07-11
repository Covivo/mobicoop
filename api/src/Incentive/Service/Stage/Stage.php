<?php

namespace App\Incentive\Service\Stage;

use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Interfaces\StageInterface;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Payment\Repository\CarpoolPaymentRepository;
use Doctrine\ORM\EntityManagerInterface;

abstract class Stage implements StageInterface
{
    /**
     * @var CarpoolPaymentRepository
     */
    protected $_carpoolPaymentRepository;

    /**
     * @var LongDistanceJourneyRepository
     */
    protected $_ldJourneyRepository;

    /**
     * @var MobConnectApiProvider
     */
    protected $_apiProvider;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var TimestampTokenManager
     */
    protected $_timestampTokenManager;

    /**
     * @var EecInstance
     */
    protected $_eecInstance;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    protected $_subscription;

    protected function _setApiProvider()
    {
        $this->_apiProvider = new MobConnectApiProvider($this->_eecInstance);
    }
}
