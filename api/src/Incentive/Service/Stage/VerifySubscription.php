<?php

namespace App\Incentive\Service\Stage;

use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Validator\SubscriptionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifySubscription extends Stage
{
    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    protected $_subscription;

    /**
     * @var MobConnectSubscriptionVerifyResponse
     */
    private $_httpResponse;

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

        if (SubscriptionValidator::isSubscriptionReadyToVerify($this->_subscription)) {
            try {
                $this->_httpResponse = $this->_apiProvider->verifySubscription($this->_subscription);

                $this->_updateSubscription();
            } catch (HttpException $exception) {
                $this->_subscription->addLog($exception, Log::TYPE_VERIFY);
                $this->_subscription->setStatus(Subscription::STATUS_ERROR);

                $this->_em->flush();

                return;
            }

            $this->_em->flush();
        }
    }

    /**
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    private function _updateSubscription()
    {
        $this->_subscription->setStatus($this->_httpResponse->getStatus());
        $this->_subscription->setVerificationDate(new \DateTime());

        switch ($this->_subscription->getStatus()) {
            case Subscription::STATUS_VALIDATED:
                $this->_updateSubscriptionWhenValidated();

                break;

            case Subscription::STATUS_REJECTED:
                $this->_updateSubscriptionWhenRejected();

                break;
        }

        return $this->_subscription;
    }

    /**
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    private function _updateSubscriptionWhenValidated()
    {
        $this->_subscription->setBonusStatus(Subscription::BONUS_STATUS_OK);

        return $this->_subscription;
    }

    /**
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    private function _updateSubscriptionWhenRejected()
    {
        if (array_key_exists('rejectionReason', $this->_httpResponse->getContent())) {
            $this->_subscription->setRejectReason($this->_httpResponse->getContent()['rejectionReason']);
        }

        if (array_key_exists('comments', $this->_httpResponse->getContent())) {
            $this->_subscription->setComment($this->_httpResponse->getContent()['comments']);
        }

        $this->_subscription->setBonusStatus(Subscription::BONUS_STATUS_NO);

        return $this->_subscription;
    }
}
