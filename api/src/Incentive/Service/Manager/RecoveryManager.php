<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
use App\Incentive\Resource\EecInstance;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecoveryManager
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EecInstance
     */
    private $_eecInstance;

    /**
     * @var LongDistanceSubscriptionRepository
     */
    private $_longDistanceSubscriptionRepository;

    /**
     * @var ShortDistanceSubscriptionRepository
     */
    private $_shortDistanceSubscriptionRepository;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    public function __construct(
        EntityManagerInterface $em,
        InstanceManager $instanceManager,
        SubscriptionManager $subscriptionManager,
        LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository,
        ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository,
        UserRepository $userRepository
    ) {
        $this->_em = $em;
        $this->_subscriptionManager = $subscriptionManager;

        $this->_eecInstance = $instanceManager->getEecInstance();

        $this->_longDistanceSubscriptionRepository = $longDistanceSubscriptionRepository;
        $this->_shortDistanceSubscriptionRepository = $shortDistanceSubscriptionRepository;
        $this->_userRepository = $userRepository;
    }

    public function execute()
    {
        $this->_processSubscriptionsthatMayBeReEngaged();

        $this->_processUsersWithRecoverableJourneys();
    }

    private function _processSubscriptionsthatMayBeReEngaged()
    {
        // LD
        if ($this->_eecInstance->isLdFeaturesAvailable()) {
            $subscriptions = $this->_longDistanceSubscriptionRepository->getSubscriptionsthatMayBeReEngaged();

            foreach ($subscriptions as $item) {
                $subscription = $this->_em->getRepository(LongDistanceSubscription::class)->find($item['subscription_id']);
                $carpoolPayment = $this->_em->getRepository(CarpoolPayment::class)->find($item['carpool_payment_id']);

                $this->_subscriptionManager->commitSubscription($subscription, $carpoolPayment, true);
            }
        }

        // SD
        if ($this->_eecInstance->isSdFeaturesAvailable()) {
            $subscriptions = $this->_shortDistanceSubscriptionRepository->getSubscriptionsthatMayBeReEngaged();

            foreach ($subscriptions as $item) {
                $subscription = $this->_em->getRepository(ShortDistanceSubscription::class)->find($item['subscription_id']);
                $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($item['carpool_proof_id']);

                $this->_subscriptionManager->commitSubscription($subscription, $carpoolProof, true);
            }
        }
    }

    private function _processUsersWithRecoverableJourneys()
    {
        if ($this->_eecInstance->isLdFeaturesAvailable()) {
            $users = $this->_userRepository->getUsersWithLDRecoverableJourneys();

            foreach ($users as $user) {
                $this->_processRecoverForUser($user, Subscription::TYPE_LONG);
            }
        }

        if ($this->_eecInstance->isSdFeaturesAvailable()) {
            $users = $this->_userRepository->getUsersWithSDRecoverableJourneys();

            foreach ($users as $user) {
                $this->_processRecoverForUser($user, Subscription::TYPE_SHORT);
            }
        }
    }

    private function _processRecoverForUser(User $user, string $type)
    {
        $this->_subscriptionManager->proofsRecover($type, $user->getId());
    }
}
