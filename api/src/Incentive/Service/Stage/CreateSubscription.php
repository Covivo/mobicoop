<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Event\InvalidAuthenticationEvent;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Definition\DefinitionSelector;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Manager\InstanceManager;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\MobConnectMessages;
use App\Incentive\Service\Validation\APIAuthenticationValidation;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /**
     * @var LoggerService
     */
    private $_loggerService;

    public function __construct(
        EntityManagerInterface $em,
        TimestampTokenManager $timestampTokenManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerService $loggerService,
        EecInstance $eecInstance,
        User $user,
        string $subscriptionType
    ) {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_loggerService = $loggerService;
        $this->_eecInstance = $eecInstance;

        $this->_user = $user;
        $this->_subscriptionType = $subscriptionType;

        $this->_setApiProvider();
    }

    public function execute()
    {
        try {
            $httpResponse = $this->_apiProvider->postSubscription($this->_subscriptionType, $this->_user);
        } catch (HttpException $exception) {
            $this->_loggerService->log(
                str_replace('[USER]', $this->_user->getId(), str_replace('[TYPE]', $this->_subscriptionType, MobConnectMessages::HTTP_CREATION_REQUEST_ERROR.$exception->getMessage())),
                LoggerService::TYPE_INFO,
                true
            );

            if (APIAuthenticationValidation::isApiAuthenticationError($exception)) {
                $event = new InvalidAuthenticationEvent($this->_user);
                $this->_eventDispatcher->dispatch(InvalidAuthenticationEvent::NAME, $event);
            }

            throw new \LogicException('eec_subscription_'.$this->_subscriptionType.'_unfinalized');
        }

        $subscriptionClass = 'App\Incentive\Entity\\'.ucfirst($this->_subscriptionType).'DistanceSubscription';

        $subscription = new $subscriptionClass(
            $this->_user,
            $httpResponse->getContent()->id,
            DefinitionSelector::getDefinition($this->_subscriptionType)
        );

        $token = $this->_timestampTokenManager->getLatestToken($subscription);
        $subscription->setIncentiveProofTimestampToken($token->getTimestampToken());
        $subscription->setIncentiveProofTimestampSigningTime($token->getSigningTime());

        $this->_em->persist($subscription);
        $this->_em->flush();
    }
}
