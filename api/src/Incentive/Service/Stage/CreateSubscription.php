<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\Log\Log;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Definition\DefinitionSelector;
use App\Incentive\Service\Manager\InstanceManager;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateSubscription extends Stage
{
    /**
     * @var InstanceManager
     */
    protected $_instanceManager;

    /**
     * @var string
     */
    protected $_subscriptionType;

    /**
     * @var User
     */
    protected $_user;

    public function __construct(
        EntityManagerInterface $em,
        TimestampTokenManager $timestampTokenManager,
        EecInstance $eecInstance,
        User $user,
        string $subscriptionType
    ) {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eecInstance = $eecInstance;

        $this->_user = $user;
        $this->_subscriptionType = $subscriptionType;

        $this->_setApiProvider();
    }

    public function execute()
    {
        $httpResponse = $this->_apiProvider->postSubscription($this->_subscriptionType, $this->_user);

        if ($this->_apiProvider->hasRequestErrorReturned($httpResponse)) {
            return;
        }

        $subscriptionClass = 'App\Incentive\Entity\\'.ucfirst($this->_subscriptionType).'DistanceSubscription';

        $subscription = new $subscriptionClass(
            $this->_user,
            $httpResponse->getContent()->id,
            DefinitionSelector::getDefinition($this->_subscriptionType)
        );
        $subscription->addLog($httpResponse, Log::TYPE_SUBSCRIPTION);

        $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_INCENTIVE);

        $this->_em->persist($subscription);
        $this->_em->flush();
    }
}
