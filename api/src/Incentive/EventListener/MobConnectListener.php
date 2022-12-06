<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\MobConnectSubscriptionManager;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use App\User\Event\SsoAssociationEvent;
use App\User\Event\SsoCreationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class MobConnectListener implements EventSubscriberInterface
{
    private const ALLOWED_PROVIDER = 'mobConnect';

    private $_subscriptionManager;

    /**
     * @var User
     */
    private $_user;

    public function __construct(Security $security, MobConnectSubscriptionManager $subscriptionManager)
    {
        $this->_user = $security->getUser();
        $this->_subscriptionManager = $subscriptionManager;
    }

    private function __isUserMobConnected(): bool
    {
        return preg_match('/'.self::ALLOWED_PROVIDER.'/', $this->_user->getSsoProvider()) && !is_null($this->_user->getSsoId());
    }

    private function __createSubscriptions($event)
    {
        $user = $event->getUser();

        if (
            is_null($user->getShortDistanceSubscription())
            && is_null($user->getLongDistanceSubscription())
        ) {
            $this->_subscriptionManager->createSubscriptions($user->getSsoId());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
            ElectronicPaymentValidatedEvent::NAME => 'onPaymentValidated',
            SsoAssociationEvent::NAME => 'onUserAssociated',
            SsoCreationEvent::NAME => 'onUserCreated',
        ];
    }

    public function onUserAssociated(SsoAssociationEvent $event)
    {
        if ($this->__isUserMobConnected()) {
            $this->__createSubscriptions($event);
        }
    }

    public function onUserCreated(SsoCreationEvent $event)
    {
        if ($this->__isUserMobConnected()) {
            $this->__createSubscriptions($event);
        }
    }

    // For long distance journey
    public function onPaymentValidated(ElectronicPaymentValidatedEvent $event)
    {
        $this->_subscriptionManager->updateLongDistanceSubscription($event->getCarpoolPayment());
    }

    // For short distance journey
    public function onProofValidated(CarpoolProofValidatedEvent $event)
    {
        $this->_subscriptionManager->updateShortDistanceSubscription($event->getCarpoolProof());
    }
}
