<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\CarpoolProofRepository;
use App\DataProvider\Entity\MobConnect\MobConnectApiProvider;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Entity\Flat\ShortDistanceSubscription as FlatShortDistanceSubscription;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Resource\CeeStatus;
use App\Incentive\Resource\CeeSubscriptions;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Subscription Management Manager.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class MobConnectSubscriptionManager
{
    public const LOW_THRESHOLD_PROOF = 1;
    public const HIGH_THRESHOLD_PROOF = 20;
    private const RPC_NUMBER_STATUS = 'OK';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var MobConnectAuthManager
     */
    private $_authManager;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var MobConnectApiProvider
     */
    private $_mobConnectApiProvider;

    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * The authenticated user.
     *
     * @var User
     */
    private $_user;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorageInterface,
        MobConnectAuthManager $authManager,
        CarpoolProofRepository $carpoolProofRepository,
        array $mobConnectParams
    ) {
        $this->_em = $em;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_authManager = $authManager;

        $this->_user = $tokenStorageInterface->getToken()->getUser();

        $this->_mobConnectApiProvider = new MobConnectApiProvider(new MobConnectApiParams($mobConnectParams), $this->_user);
    }

    private function __getCarpoolersNumber($carpool): int
    {
        $conn = $this->_em->getConnection();

        $sql = 'SELECT DISTINCT ci.debtor_user_id FROM carpool_item ci WHERE ci.ask_id = '.$carpool->getAsk()->getId().'';

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

    private function __isShortDistanceJourney($carpool): bool
    {
        switch (true) {
            case $carpool instanceof CarpoolProof:
                return
                    !is_null($carpool->getDirection())
                    && CeeStatus::LONG_DISTANCE_MINIMUM_IN_METERS > $carpool->getDirection()->getDistance()
                    && CarpoolProof::TYPE_HIGH === $carpool->getType();

            case $carpool instanceof CarpoolItem:
                return
                    !is_null($carpool->getAsk())
                    && !is_null($carpool->getAsk()->getMatching())
                    && CeeStatus::LONG_DISTANCE_MINIMUM_IN_METERS <= $carpool->getAsk()->getMatching()->getCommonDistance();

            default:
                return false;

                break;
        }
    }

    private function __isUserCarpoolDriver(User $driver): bool
    {
        if ($this->_user !== $driver) {
            throw new BadRequestHttpException(MobConnectMessages::USER_NOT_CARPOOL_DRIVER);
        }

        return true;
    }

    private function __checkUser(): self
    {
        if (is_null($this->_user->getDrivingLicenseNumber())) {
            throw new BadRequestHttpException(MobConnectMessages::USER_DRIVING_LICENCE_MISSING);
        }

        return $this;
    }

    public function createSubscriptions(string $authorizationCode)
    {
        $this->__checkUser();

        $this->_authManager->createAuth($authorizationCode);

        $mobConnectShortDistanceSubscription = $this->_mobConnectApiProvider->postSubscriptionForShortDistance();
        $shortDistanceSubscription = new ShortDistanceSubscription($this->_user, $mobConnectShortDistanceSubscription);

        $mobConnectLongDistanceSubscription = $this->_mobConnectApiProvider->postSubscriptionForLongDistance();
        $longDistanceSubscription = new LongDistanceSubscription($this->_user, $mobConnectLongDistanceSubscription);

        $this->_em->persist($shortDistanceSubscription);
        $this->_em->persist($longDistanceSubscription);

        $this->_em->flush();
    }

    public function updateLongDistanceSubscription(CarpoolPayment $carpoolPayment)
    {
        $userSubscription = $this->_user->getLongDistanceSubscription();
        $declaredJourneysNumber = count($userSubscription->getLongDistanceJourneys());

        if (
            is_null($userSubscription)                                              // The subscription was not created
            || self::HIGH_THRESHOLD_PROOF < $declaredJourneysNumber                 // The number of journeys already declared is greater than 20
            || CarpoolPayment::STATUS_SUCCESS !== $carpoolPayment->getStatus()      // The payment was not made successfully
        ) {
            return;
        }

        foreach ($carpoolPayment->getCarpoolItems() as $carpoolItem) {
            if (!$this->__isShortDistanceJourney($carpoolItem)) {
                continue;
            }

            $this->__isUserCarpoolDriver($carpoolItem->getCreditorUser());

            $userSubscription->addLongDistanceJourney(new LongDistanceJourney($carpoolItem, $this->__getCarpoolersNumber($carpoolItem)));

            $this->_mobConnectApiProvider->patchUserSubscription($userSubscription->getSubscriptionId(), $this->__getRpcJourneyId($carpoolItem->getId()), false, $carpoolPayment->getCreatedDate());
        }

        $this->_em->flush();
    }

    public function updateShortDistanceSubscription(CarpoolProof $carpoolProof): void
    {
        $userSubscription = $this->_user->getShortDistanceSubscription();
        $declaredJourneysNumber = count($userSubscription->getShortDistanceJourneys());

        if (
            is_null($userSubscription)
            || self::HIGH_THRESHOLD_PROOF < $declaredJourneysNumber    // The number of journeys already declared is greater than 20
            || !$this->__isShortDistanceJourney($carpoolProof)         // The journey is not a short distance journey
        ) {
            return;
        }

        $driver = $carpoolProof->getDriver();

        $this->__isUserCarpoolDriver($driver);

        $userSubscription->addShortDistanceJourney(new ShortDistanceJourney(
            $carpoolProof,
            $this->__getCarpoolersNumber($carpoolProof),
            $this->__getRpcJourneyId($carpoolProof->getId()),
            self::RPC_NUMBER_STATUS
        ));

        ++$declaredJourneysNumber;

        $subscriptionId = $userSubscription->getSubscriptionId();

        $this->_mobConnectApiProvider->patchUserSubscription($subscriptionId, $this->__getRpcJourneyId($carpoolProof->getId()), true);

        if (self::LOW_THRESHOLD_PROOF === $declaredJourneysNumber) {
            $response = $this->_mobConnectApiProvider->verifyUserSubscription($subscriptionId);
            $userSubscription->setStatus($response->getStatus());
            $userSubscription->setLastTimestamp($response->getTimestamp());
        }

        $this->_em->flush();
    }

    /**
     * Returns flat paths to be used in particular as logs.
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
}
