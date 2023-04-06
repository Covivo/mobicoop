<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\DataProvider\Entity\OpenIdSsoProvider;
use App\Incentive\Event\FirstLongDistanceJourneyPublishedEvent;
use App\Incentive\Event\FirstShortDistanceJourneyPublishedEvent;
use App\Incentive\Service\Manager\AuthManager;
use App\Incentive\Service\Manager\JourneyManager;
use App\Incentive\Service\Manager\SubscriptionManager;
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
    private const ALLOWED_SSO_PROVIDERS = [
        OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECT,
        OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECTAUTH,
        OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECTBASIC,
    ];

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var AuthManager
     */
    private $_authManager;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, JourneyManager $journeyManager, SubscriptionManager $subscriptionManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_authManager = $authManager;
        $this->_journeyManager = $journeyManager;
        $this->_subscriptionManager = $subscriptionManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
            ElectronicPaymentValidatedEvent::NAME => 'onElectronicPaymentValidated',
            FirstLongDistanceJourneyPublishedEvent::NAME => 'onFirstLongDistanceJourneyPublished',
            FirstShortDistanceJourneyPublishedEvent::NAME => 'onFirstShortDistanceJourneyPublished',
            SsoAssociationEvent::NAME => 'onUserAssociated',
        ];
    }

    public function onFirstLongDistanceJourneyPublished(FirstLongDistanceJourneyPublishedEvent $event)
    {
        $this->_journeyManager->declareFirstLongDistanceJourney($event->getProposal());
    }

    public function onFirstShortDistanceJourneyPublished(FirstShortDistanceJourneyPublishedEvent $event)
    {
        $this->_journeyManager->declareFirstShortDistanceJourney($event->getCarpoolProof());
    }

    /**
     * Listener called when a Mobicoop user is authenticated with an openId account.
     */
    public function onUserAssociated(SsoAssociationEvent $event): void
    {
        $decodeRequest = json_decode($this->_request->getContent());

        $this->_authManager->updateAuth($event->getUser(), $event->getSsoUser());

        if (
            property_exists($decodeRequest, 'ssoProvider')
            && in_array($decodeRequest->ssoProvider, self::ALLOWED_SSO_PROVIDERS)
            && property_exists($decodeRequest, 'eec')
            && (1 === $decodeRequest->eec || true === $decodeRequest)
        ) {
            $this->_subscriptionManager->createSubscriptions($event->getUser());
        }
    }

    /**
     * Listener called when an electronic payment is validated.
     */
    public function onElectronicPaymentValidated(ElectronicPaymentValidatedEvent $event): void
    {
        $this->_journeyManager->receivingElectronicPayment($event->getCarpoolPayment());
    }

    /**
     * Listener called when a carpool proof is validated.
     */
    public function onProofValidated(CarpoolProofValidatedEvent $event): void
    {
        $this->_journeyManager->validationOfProof($event->getCarpoolProof());
    }
}
