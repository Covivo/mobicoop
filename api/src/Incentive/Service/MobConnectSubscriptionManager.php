<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Entity\OpenIdSsoProvider;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Entity\Flat\ShortDistanceSubscription as FlatShortDistanceSubscription;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Event\FirstLongDistanceJourneyValidatedEvent;
use App\Incentive\Event\FirstShortDistanceJourneyValidatedEvent;
use App\Incentive\Event\LastLongDistanceJourneyValidatedEvent;
use App\Incentive\Event\LastShortDistanceJourneyValidatedEvent;
use App\Incentive\Resource\CeeSubscriptions;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Subscription Management Manager.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class MobConnectSubscriptionManager
{
    /**
     * @var MobConnectAuthManager
     */
    private $_authManager;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var MobConnectApiProvider
     */
    private $_mobConnectApiProvider;

    /**
     * @var array
     */
    private $_mobConnectParams;

    /**
     * The authenticated user.
     *
     * @var User
     */
    private $_user;

    private $_userSubscription;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        EventDispatcherInterface $eventDispatcher,
        MobConnectAuthManager $authManager,
        array $mobConnectParams
    ) {
        $this->_em = $em;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_authManager = $authManager;

        $this->_user = $security->getUser();

        $this->_mobConnectParams = $mobConnectParams;
    }

    private function __getCarpoolersNumber(int $askId): int
    {
        $conn = $this->_em->getConnection();

        $sql = 'SELECT DISTINCT ci.debtor_user_id FROM carpool_item ci WHERE ci.ask_id = '.$askId.'';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return count($stmt->fetchAll(\PDO::FETCH_COLUMN)) + 1;
    }

    private function __getFlatJourneys($journeys): array
    {
        $subscriptions = [];

        foreach ($journeys as $journey) {
            array_push($subscriptions, new FlatShortDistanceSubscription($journey));
        }

        return $subscriptions;
    }

    private function __getRpcJourneyId(int $id): string
    {
        return 'Mobicoop_'.$id;
    }

    private function __getSubscriptionId(): string
    {
        return $this->_userSubscription->getSubscriptionId();
    }

    private function __setApiProviderParams()
    {
        $this->_mobConnectApiProvider = new MobConnectApiProvider(new MobConnectApiParams($this->_mobConnectParams), $this->_user);
    }

    private function __verifySubscription()
    {
        $response = $this->_mobConnectApiProvider->verifyUserSubscription($this->__getSubscriptionId());
        // TODO s'assurer du moment où se fait la vérification de la demande: après chaque trajet ou bien une fois que le quotas maximal a été atteint
        // TODO En fonction de la réponse, modifier l'emplacement de la propriété statut (soit dans les journey, soit dans les souscription)
        $this->_userSubscription->setStatus($response->getStatus());
        $this->_userSubscription->setLastTimestamp($response->getTimestamp());
    }

    // * PUBLIC FUNCTIONS ---------------------------------------------------------------------------------------------------------------------------

    /**
     * For the authenticated user, if needed, creates the CEE sheets.
     */
    public function createSubscriptions(string $authorizationCode)
    {
        $this->_authManager->createAuth($authorizationCode);

        $this->__setApiProviderParams();

        if (is_null($this->_user->getShortDistanceSubscription()) && CeeJourneyService::isUserAccountReadyForShortDistanceSubscription($this->_user)) {
            $mobConnectShortDistanceSubscription = $this->_mobConnectApiProvider->postSubscriptionForShortDistance();
            $shortDistanceSubscription = new ShortDistanceSubscription($this->_user, $mobConnectShortDistanceSubscription);

            $this->_em->persist($shortDistanceSubscription);
        }

        if (is_null($this->_user->getLongDistanceSubscription()) && CeeJourneyService::isUserAccountReadyForLongDistanceSubscription($this->_user)) {
            $mobConnectLongDistanceSubscription = $this->_mobConnectApiProvider->postSubscriptionForLongDistance();
            $longDistanceSubscription = new LongDistanceSubscription($this->_user, $mobConnectLongDistanceSubscription);

            $this->_em->persist($longDistanceSubscription);
        }

        $this->_em->flush();
    }

    /**
     * Returns flat paths to be used in particular as logs.
     * This service is called by the CeeSubscriptionsCollectionDataProvider.
     */
    public function getUserSubscriptions(User $user)
    {
        $ceeSubscription = new CeeSubscriptions($this->_user->getId());

        if (!is_null($user->getShortDistanceSubscription())) {
            $shortDistanceSubscriptions = $this->__getFlatJourneys($user->getShortDistanceSubscription()->getShortDistanceJourneys());

            $ceeSubscription->setShortDistanceSubscriptions($shortDistanceSubscriptions);
        }

        if (!is_null($user->getLongDistanceSubscription())) {
            $longDistanceSubscriptions = $this->__getFlatJourneys($user->getLongDistanceSubscription()->getLongDistanceJourneys());

            $ceeSubscription->setLongDistanceSubscriptions($longDistanceSubscriptions);
        }

        return [$ceeSubscription];
    }

    /**
     * Updates subscriptions (long or short distance) based on provided carpoolProof.
     */
    public function updateSubscription(CarpoolProof $carpoolProof): void
    {
        switch (true) {
            case CeeJourneyService::isValidLongDistanceJourney($carpoolProof):
                $this->_userSubscription = $this->_user->getLongDistanceSubscription();

                if (
                    $this->_user !== $carpoolProof->getDriver()
                    || is_null($this->_userSubscription)
                    || CeeJourneyService::isDateExpired($this->_userSubscription->getCreatedAt()->add(new \DateInterval('P'.CeeJourneyService::REFERENCE_TIME_LIMIT.'M')))
                    || CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD < count($this->_userSubscription->getLongDistanceJourneys())
                ) {
                    return;
                }
                $journey = new LongDistanceJourney(
                    $carpoolProof,
                    $this->__getCarpoolersNumber($carpoolProof->getAsk()->getId()),
                    $this->__getRpcJourneyId($carpoolProof->getId()),
                    CeeJourneyService::RPC_NUMBER_STATUS
                );

                $this->_userSubscription->addLongDistanceJourney($journey);

                break;

            case CeeJourneyService::isValidShortDistanceJourney($carpoolProof):
                $this->_userSubscription = $this->_user->getShortDistanceSubscription();

                if (
                    $this->_user !== $carpoolProof->getDriver()
                    || is_null($this->_userSubscription)
                    || CeeJourneyService::isDateExpired($this->_userSubscription->getCreatedAt()->add(new \DateInterval('P'.CeeJourneyService::REFERENCE_TIME_LIMIT.'M')))
                    || CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD <= count($this->_userSubscription->getShortDistanceJourneys())
                ) {
                    return;
                }

                $journey = new ShortDistanceJourney(
                    $carpoolProof,
                    $this->__getCarpoolersNumber($carpoolProof->getAsk()->getId()),
                    $this->__getRpcJourneyId($carpoolProof->getId()),
                    CeeJourneyService::RPC_NUMBER_STATUS
                );

                $this->_userSubscription->addShortDistanceJourney($journey);

                break;
        }

        if ($this->_userSubscription) {
            $this->__setApiProviderParams();

            // The journey is added to the EEC sheet
            $this->_mobConnectApiProvider->patchUserSubscription($this->__getSubscriptionId(), $this->__getRpcJourneyId($carpoolProof->getId()), true);

            switch (true) {
                case $this->_userSubscription instanceof LongDistanceSubscription:
                    switch (count($this->_userSubscription->getLongDistanceJourneys())) {
                        case CeeJourneyService::LOW_THRESHOLD_PROOF:
                            $event = new FirstLongDistanceJourneyValidatedEvent($journey);
                            $this->eventDispatcher->dispatch(FirstLongDistanceJourneyValidatedEvent::NAME, $event);

                            break;

                        case CeeJourneyService::LONG_DISTANCE_TRIP_THRESHOLD:
                            $this->__verifySubscription();

                            if (LongDistanceSubscription::STATUS_VALIDATED === $this->_userSubscription->getStatus()) {
                                $event = new LastLongDistanceJourneyValidatedEvent($journey);
                                $this->_eventDispatcher->dispatch(LastLongDistanceJourneyValidatedEvent::NAME, $event);
                            }

                            break;
                    }

                    break;

                case $this->_userSubscription instanceof ShortDistanceSubscription:
                    switch (count($this->_userSubscription->getLongDistanceJourneys())) {
                        case CeeJourneyService::LOW_THRESHOLD_PROOF:
                            $event = new FirstShortDistanceJourneyValidatedEvent($journey);
                            $this->_eventDispatcher->dispatch(FirstShortDistanceJourneyValidatedEvent::NAME, $event);

                            break;

                        case CeeJourneyService::SHORT_DISTANCE_TRIP_THRESHOLD:
                            $this->__verifySubscription();

                            if (ShortDistanceSubscription::STATUS_VALIDATED === $this->_userSubscription->getStatus()) {
                                $event = new LastShortDistanceJourneyValidatedEvent($journey);
                                $this->_eventDispatcher->dispatch(LastShortDistanceJourneyValidatedEvent::NAME, $event);
                            }

                            break;
                    }

                    break;
            }

            $this->_em->flush();
        }
    }

    /**
     * Updates long distance subscription after a payment has been validated.
     */
    public function updateLongDistanceSubscriptionAfterPayment(CarpoolPayment $carpoolPayment): void
    {
        // Array of carpoolItem where driver is associated with MobConnect
        $filteredCarpoolItems = array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver->getSsoId())
                && !is_null($driver->getSsoProvider())
                && OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECT === $driver->getSsoProvider()
            ;
        });

        foreach ($filteredCarpoolItems as $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            // Array of carpoolProof where driver is the carpoolItem driver
            $filteredCarpoolProofs = array_filter($carpoolItem->getAsk()->getCarpoolProofs(), function (CarpoolProof $carpoolProof) use ($driver) {
                return $carpoolProof->getDriver() === $driver;
            });

            foreach ($filteredCarpoolProofs as $carpool) {
                $this->updateSubscription($carpool);
            }
        }
    }
}
