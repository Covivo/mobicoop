<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\MobConnectSubscriptionManager;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use App\User\Entity\User;
use App\User\Event\SsoAssociationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class providing the functions necessary for listening to events allowing the operation of EEC sheets.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class MobConnectListener implements EventSubscriberInterface
{
    private const ALLOWED_SSO_PROVIDER = 'mobConnect';

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var MobConnectSubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(RequestStack $requestStack, MobConnectSubscriptionManager $subscriptionManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_subscriptionManager = $subscriptionManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
            ElectronicPaymentValidatedEvent::NAME => 'onPaymentValidated',
            SsoAssociationEvent::NAME => 'onUserAssociated',
        ];
    }

    /**
     * Listener called when a Mobicoop user is authenticated with an openId account.
     */
    public function onUserAssociated(SsoAssociationEvent $event): void
    {
        $decodeRequest = json_decode($this->_request->getContent());

        $this->_subscriptionManager->updateAuth($event->getUser(), $event->getSsoUser());

        if (
            property_exists($decodeRequest, 'ssoProvider')
            && self::ALLOWED_SSO_PROVIDER === $decodeRequest->ssoProvider
            && property_exists($decodeRequest, 'eec')
            && 1 === $decodeRequest->eec
        ) {
            $this->_subscriptionManager->createSubscriptions($event->getUser());
        }
    }

    /**
     * Listener called when an electronic payment is validated.
     */
    public function onPaymentValidated(ElectronicPaymentValidatedEvent $event): void
    {
        $this->_subscriptionManager->updateLongDistanceSubscriptionAfterPayment($event->getCarpoolPayment());
    }

    /**
     * Listener called when a carpool proof is validated.
     */
    public function onProofValidated(CarpoolProofValidatedEvent $event): void
    {
        $this->_subscriptionManager->updateSubscription($event->getCarpoolProof());
    }
}
