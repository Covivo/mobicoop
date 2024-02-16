<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\Subscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifySubscription extends Stage
{
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_REJECTED = 'REJETEE';
    public const STATUS_VALIDATED = 'VALIDEE';

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    protected $_subscription;

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function __construct(
        EntityManagerInterface $em,
        TimestampTokenManager $timestampTokenManager,
        EecInstance $eecInstance,
        $subscription
    ) {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eecInstance = $eecInstance;

        $this->_subscription = $subscription;

        $this->_setApiProvider();
    }

    public function execute()
    {
        $this->_timestampTokenManager->setMissingSubscriptionTimestampTokens($this->_subscription, Log::TYPE_VERIFY);

        if ($this->_subscription->isReadyToVerify()) {
            try {
                $httpResponse = $this->_apiProvider->verifySubscription($this->_subscription);
            } catch (HttpException $exception) {
                $this->_subscription->addLog($exception, Log::TYPE_VERIFY);
                $this->_subscription->setStatus(self::STATUS_ERROR);

                $this->_em->flush();

                return;
            }

            $this->_subscription->setStatus($httpResponse->getStatus());

            $this->_subscription->setBonusStatus(
                self::STATUS_VALIDATED === $this->_subscription->getStatus()
                ? Subscription::BONUS_STATUS_OK
                : Subscription::BONUS_STATUS_NO
            );

            $this->_subscription->setVerificationDate(new \DateTime());

            $this->_em->flush();
        }
    }
}
