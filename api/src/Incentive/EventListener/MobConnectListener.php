<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\MobConnectSubscriptionManager;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MobConnectListener implements EventSubscriberInterface
{
    private $_subscriptionManager;
    private $_user;

    public function __construct(MobConnectSubscriptionManager $subscriptionManager)
    {
        $this->_subscriptionManager = $subscriptionManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
            ElectronicPaymentValidatedEvent::NAME => 'onPaymentValidated',
        ];
    }

    public function onUserAssociated($event)
    {
        $this->_subscriptionManager->createSubscriptions($event->getCode());
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
