<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Gamification\Service;

use App\Action\Entity\Action;
use App\Action\Entity\Log;
use App\Action\Repository\LogRepository;
use App\Carpool\Entity\Ask;
use App\Gamification\Entity\Badge;
use App\Gamification\Entity\GamificationAction;
use App\Gamification\Repository\SequenceItemRepository;
use App\User\Entity\User;
use App\Gamification\Entity\SequenceItem;
use App\Gamification\Entity\ValidationStep;
use App\Gamification\Repository\BadgeRepository;
use App\Gamification\Entity\BadgeProgression;
use App\Gamification\Entity\BadgeSummary;
use App\Gamification\Entity\GamificationNotifier;
use App\Gamification\Entity\Reward;
use App\Gamification\Entity\RewardStep;
use App\Gamification\Entity\SequenceStatus;
use App\Gamification\Event\BadgeEarnedEvent;
use App\Gamification\Event\RewardStepEarnedEvent;
use App\Gamification\Event\ValidationStepEvent;
use App\Gamification\Interfaces\GamificationNotificationInterface;
use App\Gamification\Resource\BadgesBoard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Gamification\Interfaces\GamificationRuleInterface;
use App\Communication\Repository\MessageRepository;
use App\Gamification\Repository\RewardStepRepository;
use App\Gamification\Repository\RewardRepository;
use App\Payment\Entity\CarpoolItem;
use Psr\Log\LoggerInterface;
use App\User\Repository\UserRepository;

/**
 * Gamification Manager
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GamificationManager
{
    private $sequenceItemRepository;
    private $logRepository;
    private $badgeRepository;
    private $entityManager;
    private $eventDispatcher;
    private $gamificationNotifier;
    private $messageRepository;
    private $rewardStepRepository;
    private $rewardRepository;
    private $badgeImageUri;
    private $logger;
    private $userRepository;

    public function __construct(
        SequenceItemRepository $sequenceItemRepository,
        LogRepository $logRepository,
        BadgeRepository $badgeRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        GamificationNotifier $gamificationNotifier,
        MessageRepository $messageRepository,
        RewardStepRepository $rewardStepRepository,
        RewardRepository $rewardRepository,
        string $badgeImageUri,
        LoggerInterface $logger,
        UserRepository $userRepository
    ) {
        $this->sequenceItemRepository = $sequenceItemRepository;
        $this->logRepository = $logRepository;
        $this->badgeRepository = $badgeRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->gamificationNotifier = $gamificationNotifier;
        $this->messageRepository = $messageRepository;
        $this->rewardStepRepository = $rewardStepRepository;
        $this->rewardRepository = $rewardRepository;
        $this->badgeImageUri = $badgeImageUri;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Get all the Badges of the instance
     * @param int $status  Get only the Badges of this status (default : null, every badges are returned)
     * @return Badges[]|null
     */
    public function getBadges(int $status=null): ?array
    {
        if (is_null($status)) {
            return $this->badgeRepository->findAll();
        } else {
            return $this->badgeRepository->findBy(["status"=>$status]);
        }
    }
    
    /**
     * When a new log entry is detected, we treat it to determine if there is something to do (i.e Gamification)
     *
     * @param Log $log          Event of the action
     * @return void
     */
    public function handleLog(Log $log)
    {
        // A new log has been recorded. We need to check if there is a gamification action to take
        $gamificationActions = $log->getAction()->getGamificationActions();
        if (is_array($gamificationActions) && count($gamificationActions)>0) {
            // This action has gamification action, we need to treat it
            foreach ($gamificationActions as $gamificationAction) {
                $this->treatGamificationAction($gamificationAction, $log);
            }
        }
    }

    /**
     * Treatment and evaluation of a GamificationAction
     *
     * @param GamificationAction $gamificationAction
     * @param Log $log
     * @return void
     */
    private function treatGamificationAction(GamificationAction $gamificationAction, Log $log)
    {
        // We check if this action is in a sequenceItem
        $validationSteps = [];
        $sequenceItems = $this->sequenceItemRepository->findBy(['gamificationAction'=>$gamificationAction]);
        if (is_array($sequenceItems) && count($sequenceItems)>0) {
            // This action has gamification action, we need to treat it
            /**
             * @var SequenceItem $sequenceItem
             */
            foreach ($sequenceItems as $sequenceItem) {
                $validationStep = new ValidationStep();
                $validationStep->setUser($log->getUser());
                $validationStep->setSequenceItem($sequenceItem);
                $validationStep->setValidated(true); // By default, the sequenceItem is valid

                if (!is_null($gamificationAction->getGamificationActionRule())) {
                    // at this point a rule is associated, we need to execute it
                    $gamificationActionRuleName = "\\App\\Gamification\Rule\\" . $gamificationAction->getGamificationActionRule()->getName();
                    /**
                     * @var GamificationRuleInterface $gamificationActionRule
                     */
                    $gamificationActionRule = new $gamificationActionRuleName;
                    $validationStep->setValidated($validationStep->isValidated() && $gamificationActionRule->execute($log->getUser(), $log, $sequenceItem));
                }
                // This related action needs to be made a minimum amount of time
                if (!is_null($sequenceItem->getMinCount()) && $sequenceItem->getMinCount()>0) {
                    $validationStep->setValidated($validationStep->isValidated() && $this->checkMinCount($gamificationAction->getAction(), $log->getUser(), $sequenceItem->getMinCount()));
                }
                // this related action needs to be made in a range that range date
                if (($sequenceItem->isInDateRange())) {
                    $validationStep->setValidated($validationStep->isValidated() && $this->checkInDateRange($gamificationAction->getAction(), $log->getUser(), $sequenceItem->getBadge()->getStartDate(), $sequenceItem->getBadge()->getEndDate(), $sequenceItem->getMinCount(), $sequenceItem->getMinUniqueCount()));
                }

                // Dispatch an event who says that a ValidationStep has been evaluated
                $validationStepEvent = new ValidationStepEvent($validationStep);
                $this->eventDispatcher->dispatch(ValidationStepEvent::NAME, $validationStepEvent);
            }
        }
    }

    /**
     * Check if the MinCount criteria is verified
     *
     * @param Action $action    The action to count
     * @param User $user        The User we count for
     * @param int $minCount     The min count to be valid
     * @return boolean  True for valid
     */
    private function checkMinCount(Action $action, User $user, int $minCount): bool
    {
        // We get in the log table all the Action $action made by this User $user
        $logs = $this->logRepository->findBy(['action'=>$action, 'user'=>$user]);
        if (is_array($logs) && count($logs)>=$minCount) {
            return true;
        }

        return false;
    }

    /**
     * Check if the inDateRange criteria is verified
     *
     * @param Action $action            The action to check
     * @param User $user                The User who made the action
     * @param DateTime $startDate       The start date to be valid
     * @param DateTime $endDate         The end date to be valid
     * @param integer $minCount         The min count to be valid
     * @param integer $minUniqueCount   not implemented The unique min count to be vali_d
     * @return boolean  True for valid
     */
    private function checkInDateRange(Action $action, User $user, $startDate, $endDate, $minCount=0, $minUniqueCount = 0): bool
    {
        // We get in the log table all the Action $action made by this User $user
        $logs = $this->logRepository->findBy(['action'=>$action, 'user'=>$user]);
        $logIds = [];
        foreach ($logs as $log) {
            if ($startDate <= $log->getDate() && $log->getDate() <= $endDate) {
                $logIds[] = $log->getId();
            }
        }
        if (count($logIds)>$minCount) {
            return true;
        }
        return false;
    }

    /**
     * Get the Badges board of a User
     *
     * @param User $user    The User
     * @return BadgesBoard
     */
    public function getBadgesBoard(User $user): BadgesBoard
    {
        $badgesBoard = new BadgesBoard();
        
        // Set if the user accept Gamification tracking
        $badgesBoard->setAcceptGamification($user->hasGamification());

        
        
        // Get all the active badges of the platform
        $activeBadges = $this->getBadges(Badge::STATUS_ACTIVE);
        $badges = [];

        /**
         * @var Badge $activeBadge
         */
        foreach ($activeBadges as $activeBadge) {
            $badgeProgression = new BadgeProgression();
            
            // Determine if the badge is already earned
            $badgeProgression->setEarned(false);
            foreach ($activeBadge->getRewards() as $reward) {
                if ($reward->getUser()->getId() == $user->getId()) {
                    $badgeProgression->setEarned(true);
                    break;
                }
            }

            // Minimum data about the current badge
            $badgeSummary = new BadgeSummary();
            $badgeSummary->setBadgeId($activeBadge->getId());
            $badgeSummary->setBadgeName($activeBadge->getName());
            $badgeSummary->setBadgeTitle($activeBadge->getTitle());

            // images
            $badgeSummary->setIcon((!is_null($activeBadge->getIcon())) ? $this->badgeImageUri.$activeBadge->getIcon()->getFileName() : null);
            $badgeSummary->setDecoratedIcon((!is_null($activeBadge->getDecoratedIcon())) ? $this->badgeImageUri.$activeBadge->getDecoratedIcon()->getFileName() : null);
            $badgeSummary->setImage((!is_null($activeBadge->getImage())) ? $this->badgeImageUri.$activeBadge->getImage()->getFileName() : null);
            $badgeSummary->setImageLight((!is_null($activeBadge->getImageLight())) ? $this->badgeImageUri.$activeBadge->getImageLight()->getFileName() : null);

            // We get the sequence and check if the current user validated it
            $sequences = [];
            $nbValidatedSequences = 0;
            foreach ($activeBadge->getSequenceItems() as $sequenceItem) {
                $sequenceStatus = new SequenceStatus();
                $sequenceStatus->setSequenceItemId($sequenceItem->getId());
                $sequenceStatus->setTitle($sequenceItem->getGamificationAction()->getTitle());
                
                
                // We look into the rewardSteps previously existing for this SequenceItem
                // If there is one for the current User, we know that it has already been validated
                $sequenceStatus->setValidated(false);
                foreach ($sequenceItem->getRewardSteps() as $rewardStep) {
                    if ($rewardStep->getUser()->getId() == $user->getId()) {
                        $sequenceStatus->setValidated(true);
                        $nbValidatedSequences++;
                        break;
                    }
                }
                $sequences[] = $sequenceStatus;
            }
            $badgeSummary->setSequences($sequences);

            $badgeProgression->setBadgeSummary($badgeSummary);

            // Compute the earned percentage
            $badgeProgression->setEarningPercentage(0);
            if ($nbValidatedSequences==0) {
                $badgeProgression->setEarningPercentage(0);
            } else {
                $badgeProgression->setEarningPercentage($nbValidatedSequences/count($activeBadge->getSequenceItems())*100);
            }

            $badges[] = $badgeProgression;
        }
        
        $badgesBoard->setBadges($badges);

        return $badgesBoard;
    }

    /**
     * Get the Badges earned by a User
     *
     * @param User $user
     * @return array|null
     */
    public function getBadgesEarned(User $user): ?array
    {
        $badges = [];
        foreach ($user->getRewards() as $reward) {
            $badges[] = $reward->getBadge();
        }
        return $badges;
    }

    /**
     * Take a ValidationStep and take the necessary actions about it (RewardStep, Badge...)
     *
     * @param ValidationStep $validationStep   The ValidationStep to treat
     * @return void
     */
    public function handleValidationStep(ValidationStep $validationStep)
    {
        if ($validationStep->isValidated()) {
            // The ValidationStep has been validated
            // First we get the BadgesBoard of this User. With it, we can check if this particular step has alteady been validated
            $badgesBoard = $this->getBadgesBoard($validationStep->getUser());
            foreach ($badgesBoard->getBadges() as $badgeProgression) {
                $badgeSummary = $badgeProgression->getBadgeSummary();

                $currentSequenceValidation = []; // We will store the status of every SequenceItem
                $newValidation = false;
                foreach ($badgeSummary->getSequences() as $sequenceStatus) {

                    // We found the right sequence
                    if ($sequenceStatus->getSequenceItemId() == $validationStep->getSequenceItem()->getId()) {
                        // If it's a new validation, We store it be inserting a line in RewardStep for the User
                        if (!$sequenceStatus->isValidated()) {
                            $newValidation = true;
                            $rewardStep = new RewardStep();
                            $rewardStep->setUser($validationStep->getUser());
                            $validationStep->getSequenceItem()->addRewardStep($rewardStep);

                            $this->entityManager->persist($validationStep->getSequenceItem());

                            // We also update the current SequenceStatus to evaluate further it this is enough to earn badge
                            $sequenceStatus->setValidated(true);

                            // Dispatch the event
                            $rewardStepEarnedEvent = new RewardStepEarnedEvent($rewardStep);
                            $this->eventDispatcher->dispatch(RewardStepEarnedEvent::NAME, $rewardStepEarnedEvent);
                        }
                    }
                    // We store the status of the current SequenceItem. If all validated, maybe the user earned a Badge
                    $currentSequenceValidation[] = $sequenceStatus->isValidated();
                }
                if (!in_array(false, $currentSequenceValidation)) {
                    // All steps are valid !
                    if ($newValidation) {
                        // There was a new validation, a new Badge is earned !
                        // We get the badge involved and add a User owning this Badge (add a line in Reward table)
                        $badge = $this->badgeRepository->find($badgeSummary->getBadgeId());
                        $reward = new Reward();
                        $reward->setUser($validationStep->getUser());
                        $badge->addReward($reward);
                        $this->entityManager->persist($badge);

                        // Dispatch the event
                        $badgeEarnedEvent = new BadgeEarnedEvent($reward);
                        $this->eventDispatcher->dispatch(BadgeEarnedEvent::NAME, $badgeEarnedEvent);
                    }
                }
            }
            $this->entityManager->flush();
        }
    }

    /**
     * Add a Gamification notification to the current pool that will be return at the end of the request
     *
     * @param GamificationNotificationInterface $gamificationNotification
     * @return void
     */
    public function handleGamificationNotification(GamificationNotificationInterface $gamificationNotification)
    {
        $this->gamificationNotifier->addNotification($gamificationNotification);
    }

    /**
     * Tag a RewardStep as notified
     *
     * @param int $id    Id of the RewardStep to tag
     * @return RewardStep
     */
    public function tagRewardStepAsNotified(int $id): RewardStep
    {
        if ($rewardStep = $this->rewardStepRepository->find($id)) {
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            return $rewardStep;
        }
        throw new \LogicException("No RewardStep found");
    }

    /**
     * Tag a Reward as notified
     *
     * @param int $id    Id of the RewardStep to tag
     * @return Reward
     */
    public function tagRewardAsNotified(int $id): Reward
    {
        if ($reward = $this->rewardRepository->find($id)) {
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
            return $reward;
        }
        throw new \LogicException("No Reward found");
    }

    
    public function retroactivelyGenerateRewards()
    {
        foreach ($this->userRepository->findAll() as $user) {
            // $this->generateBadgeOneRewards($user);
            // $this->generateBadgeTwoRewards($user);
            // $this->generateBadgeThreeRewards($user);
            // $this->generateBadgeFourRewards($user);
            $this->generateBadgeFiveRewards($user);
            // $this->generateBadgeSixRewards($user);
            // $this->generateBadgeSevenRewards($user);
        }
        return;
    }

    public function generateBadgeOneRewards(User $user)
    {
        // Badge 1 "Remove the mask" composed by 4 sequences (1, 2, 3 and 4)
        // Sequence 1 (vérifier son adresse email)
        $badge1 = [];
        if (!is_null($user->getValidatedDate())) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(1));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge1[] = 1;
        }
        // Sequence 2 (vérifier son téléphone)
        if (!is_null($user->getPhoneValidatedDate())) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(2));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge1[] = 2;
        }
        // Sequence 3 (avoir une image de profil)
        if (!is_null($user->getImages()) && count($user->getImages()) > 0) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(3));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge1[] = 3;
        }
        // Sequence 4 (avoir renseigné sa commune de résidence)
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $rewardStep = new RewardStep;
                $rewardStep->setUser($user);
                $rewardStep->setSequenceItem($this->sequenceItemRepository->find(4));
                $rewardStep->setCreatedDate(new \DateTime('now'));
                $rewardStep->setNotifiedDate(new \DateTime('now'));
                $this->entityManager->persist($rewardStep);
                $this->entityManager->flush();
                $badge1[] = 4;
                continue;
            }
        }
        // Badge 1
        if (count($badge1) === 4) {
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(1));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }

    public function generateBadgeTwoRewards(User $user)
    {
        // Badge 2 "launch" composed by sequence 5
        // Sequence 5
        $nbAds = 0;
        foreach ($user->getProposals() as $proposal) {
            if (!$proposal->isPrivate()) {
                $nbAds++;
            }
        }
        if ($nbAds >= 1) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(5));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(2));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }

    public function generateBadgeThreeRewards(User $user)
    {
        // Badge 3 "first time" composed by 2 sequences (6, 7)
        $badge3 = [];
        // Sequence 6 (premier message répondu)
        $messages = $user->getMessages();
        $count = 0;
        foreach ($messages as $message) {
            if (is_null($message->getMessage())) {
                $count++;
            }
        }
        if ($count >= 1) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(6));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge3[] = 1;
        }
        // Sequence 7 (covoiturage accepté)
        $asks = array_merge($user->getAsks(), $user->getAsksRelated());
        $isCarpooled = false;
        foreach ($asks as $ask) {
            if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                $isCarpooled = true;
            }
        }
        if ($isCarpooled) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(7));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge3[] = 1;
        }
        // Badge 3
        if (count($badge3) == 2) {
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(3));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }

    public function generateBadgeFourRewards(User $user)
    {
        // Badge 4 "welcome" composed by 3 sequences (8, 9 and 10)
        $badge4 = [];
        // Sequence 8 (rejoindre une communauté)
        if (count($user->getCommunityUsers()) >= 1) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(8));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge4[] = 1;
        }
        // Sequence 9 (publier une annonce dans une commuanuté)

        $proposals = $user->getProposals();
        // we get all user's proposals and for each proposal we check if he's associated with a community
        foreach ($proposals as $proposal) {
            $communities = $proposal->getCommunities();
            // at the first proposal associated to a community we return true since we need at least one proposal associated to a community
            if (count($communities) > 0) {
                $rewardStep = new RewardStep;
                $rewardStep->setUser($user);
                $rewardStep->setSequenceItem($this->sequenceItemRepository->find(9));
                $rewardStep->setCreatedDate(new \DateTime('now'));
                $rewardStep->setNotifiedDate(new \DateTime('now'));
                $this->entityManager->persist($rewardStep);
                $this->entityManager->flush();
                $badge4[] = 2;
            }
        }
        // Sequence 10 (accepter un covoiturage dans une commuanuté)
        $proposals = $user->getProposals();
        // we get all user's proposals and for each proposal we check if he's associated with a community
        $hasAcceptedCarpool = false;
        foreach ($proposals as $proposal) {
            $communities = $proposal->getCommunities();
            // at the first proposal associated to a community we return true since we need at least one proposal associated to a community
            if (count($communities) > 0) {
                $matchingsOffers=$proposal->getMatchingOffers();
                $matchingsRequests=$proposal->getMatchingRequests();
                foreach ($matchingsOffers as $matching) {
                    foreach ($matching->getAsks() as $ask) {
                        if ($ask->getStatus() === Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() === Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                            $hasAcceptedCarpool = true;
                        }
                    }
                }
                foreach ($matchingsRequests as $matching) {
                    foreach ($matching->getAsks() as $ask) {
                        if ($ask->getStatus() === Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() === Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                            $hasAcceptedCarpool = true;
                        }
                    }
                }
            }
        }
        if ($hasAcceptedCarpool) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(10));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            $badge4[] = 3;
        }
        // Badge 4
        if (count($badge4) === 3) {
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(4));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }

    public function generateBadgeFiveRewards(User $user)
    {
        // Badge 5 "rally" composed by sequence 11
        // Sequence 11 (au moins N annonces publiées)
        $proposals = $user->getProposals();
        $publishedProposals = [];
        // we check that the proposal is a published proposal and not a search
        foreach ($proposals as $proposal) {
            if (!$proposal->isPrivate()) {
                $publishedProposals[] = $proposal;
            }
        }
        if (count($publishedProposals) >= $this->sequenceItemRepository->find(11)->getMinCount()) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(11));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            // Badge 5
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(5));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }

    public function generateBadgeSixRewards(User $user)
    {
        var_dump('badge6');

        // Badge 6 "km_carpooled" composed by sequence 12
        // Sequence 12 (au moins N km covoiturés)
        $asks = array_merge($user->getAsks(), $user->getAsksRelated());
        $carpooledKm = null;
        foreach ($asks as $ask) {
            if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                $carpoolItems = $ask->getCarpoolItems();
                $numberOfTravel = null;
                foreach ($carpoolItems as $carpoolItem) {
                    if ($carpoolItem->getItemStatus() == CarpoolItem::STATUS_REALIZED) {
                        $numberOfTravel = + 1;
                    }
                }
                $carpooledKm = $carpooledKm + ($ask->getMatching()->getCommonDistance() * $numberOfTravel);
            }
        }
        // if a proposal he's carpooled and associated to a community we return true
        if (($carpooledKm / 1000) >= $this->sequenceItemRepository->find(12)->getValue()) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(12));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            // Badge 6
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(6));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }

    public function generateBadgeSevenRewards(User $user)
    {
        var_dump('badge7');

        // Badge 7 "km_carpooled" composed by sequence 13
        // Sequence 13 (au moins N CO² économisés en covoiturant)
        $savedCo2 = $user->getSavedCo2() / 1000;
        if ($savedCo2 >= $this->sequenceItemRepository->find(13)->getValue()) {
            $rewardStep = new RewardStep;
            $rewardStep->setUser($user);
            $rewardStep->setSequenceItem($this->sequenceItemRepository->find(13));
            $rewardStep->setCreatedDate(new \DateTime('now'));
            $rewardStep->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($rewardStep);
            $this->entityManager->flush();
            // Badge 7
            $reward = new Reward;
            $reward->setUser($user);
            $reward->setBadge($this->badgeRepository->find(7));
            $reward->setCreatedDate(new \DateTime('now'));
            $reward->setNotifiedDate(new \DateTime('now'));
            $this->entityManager->persist($reward);
            $this->entityManager->flush();
        }
        return;
    }
}
