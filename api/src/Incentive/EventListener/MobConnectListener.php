<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofInvalidatedEvent;
use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\DataProvider\Entity\OpenIdSsoProvider;
use App\Incentive\Event\FirstLongDistanceJourneyPublishedEvent;
use App\Incentive\Event\FirstShortDistanceJourneyPublishedEvent;
use App\Incentive\Event\SubscriptionNotReadyToVerifyEvent;
use App\Incentive\Service\Manager\AuthManager;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Incentive\Validator\UserValidator;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use App\User\Entity\User;
use App\User\Event\SsoAssociationEvent;
use App\User\Event\UserDrivingLicenceNumberUpdateEvent;
use App\User\Event\UserHomeAddressUpdateEvent;
use App\User\Event\UserPhoneUpdateEvent;
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
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, SubscriptionManager $subscriptionManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_authManager = $authManager;
        $this->_subscriptionManager = $subscriptionManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
            CarpoolProofInvalidatedEvent::NAME => 'onProofInvalidated',
            ElectronicPaymentValidatedEvent::NAME => 'onElectronicPaymentValidated',
            FirstLongDistanceJourneyPublishedEvent::NAME => 'onFirstLongDistanceJourneyPublished',
            FirstShortDistanceJourneyPublishedEvent::NAME => 'onFirstShortDistanceJourneyPublished',
            SsoAssociationEvent::NAME => 'onUserAssociated',
            SubscriptionNotReadyToVerifyEvent::NAME => 'onIdentifySubscriptionNotRedyToVerify',
            UserDrivingLicenceNumberUpdateEvent::NAME => 'onDrivingLicenceNumberUpdated',
            UserHomeAddressUpdateEvent::NAME => 'onUserHomeAddressUpdated',
            UserPhoneUpdateEvent::NAME => 'onUserPhoneUpdated',
        ];
    }

    public function onFirstLongDistanceJourneyPublished(FirstLongDistanceJourneyPublishedEvent $event)
    {
        $proposal = $event->getProposal();

        $subscription = !is_null($proposal->getUser()) && !is_null($proposal->getUser()->getLongDistanceSubscription())
            ? $proposal->getUser()->getLongDistanceSubscription()
            : null;

        if (is_null($subscription)) {
            return null;
        }

        $this->_subscriptionManager->commitSubscription($subscription, $proposal);
    }

    public function onFirstShortDistanceJourneyPublished(FirstShortDistanceJourneyPublishedEvent $event)
    {
        $carpoolProof = $event->getCarpoolProof();

        $subscription = !is_null($carpoolProof->getDriver()) && !is_null($carpoolProof->getDriver()->getShortDistanceSubscription())
            ? $carpoolProof->getDriver()->getShortDistanceSubscription()
            : null;

        if (is_null($subscription)) {
            return null;
        }

        $this->_subscriptionManager->commitSubscription($subscription, $carpoolProof);
    }

    /**
     * Listener called when a Mobicoop user is authenticated with an openId account.
     */
    public function onUserAssociated(SsoAssociationEvent $event): void
    {
        $decodeRequest = json_decode($this->_request->getContent());

        $ssoUser = $event->getSsoUser();

        if (
            property_exists($decodeRequest, 'ssoProvider')
            && in_array($decodeRequest->ssoProvider, self::ALLOWED_SSO_PROVIDERS)
        ) {
            $this->_authManager->updateAuth($event->getUser(), $ssoUser);

            if (
                property_exists($decodeRequest, 'eec')
                && (1 === $decodeRequest->eec || true === $decodeRequest)
            ) {
                if (!$ssoUser->isFranceConnected()) {
                    throw new \LogicException('eec_user_not_france_connected');
                }

                $this->_subscriptionManager->createSubscriptions($event->getUser());
            }
        }
    }

    /**
     * Listener called when an electronic payment is validated.
     */
    public function onElectronicPaymentValidated(ElectronicPaymentValidatedEvent $event): void
    {
        $this->_subscriptionManager->validateSubscription($event->getCarpoolPayment());
    }

    /**
     * Listener called when a carpool proof is validated.
     */
    public function onProofValidated(CarpoolProofValidatedEvent $event): void
    {
        $this->_subscriptionManager->validateSubscription($event->getCarpoolProof());
    }

    public function onProofInvalidated(CarpoolProofInvalidatedEvent $event): void
    {
        $this->_subscriptionManager->invalidateProof($event->getCarpoolProof());
    }

    public function onIdentifySubscriptionNotRedyToVerify(SubscriptionNotReadyToVerifyEvent $event)
    {
        $this->_subscriptionManager->subscriptionNotReadyToVerify($event->getSubscription());
    }

    public function onDrivingLicenceNumberUpdated(UserDrivingLicenceNumberUpdateEvent $event)
    {
        $user = $event->getUser();

        if (UserValidator::hasUserEECSubscribed($user)) {
            $this->_subscriptionManager->updateSubscriptionDrivingLicenceNumber($user);
        }
    }

    public function onUserHomeAddressUpdated(UserHomeAddressUpdateEvent $event)
    {
        $user = $event->getUser();

        if (UserValidator::hasUserEECSubscribed($user)) {
            $this->_subscriptionManager->updateSubscriptionsAddress($user);
        }
    }

    public function onUserPhoneUpdated(UserPhoneUpdateEvent $event)
    {
        $user = $event->getUser();

        if (UserValidator::hasUserEECSubscribed($user)) {
            $this->_subscriptionManager->updateSubscriptionPhone($user);
        }
    }
}
