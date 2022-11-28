<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\MobConnectSubscriptionManager;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use App\User\Event\SsoAssociationEvent;
use App\User\Event\SsoCreationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MobConnectListener implements EventSubscriberInterface
{
    private $_subscriptionManager;
    private $_user;

    public function __construct(MobConnectSubscriptionManager $subscriptionManager)
    {
        $this->_subscriptionManager = $subscriptionManager;
    }

    private function __createSubscriptions($event)
    {
        $user = $event->getUser();

        if (is_null($user->getShortDistanceSubscription() && is_null($user->getLongDistanceSubscription()))) {
            $this->_subscriptionManager->createSubscriptions($user->getSsoId());

            $user->setSubscriptionsJustCreate(true);
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
        $this->__createSubscriptions($event);
    }

    public function onUserCreated(SsoCreationEvent $event)
    {
        $this->__createSubscriptions($event);
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
