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

namespace App\Serializer;

use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Gamification\Entity\GamificationNotifier;
use App\Gamification\Entity\Reward;
use App\Gamification\Entity\RewardStep;
use App\Gamification\Repository\RewardRepository;
use App\Gamification\Repository\RewardStepRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;
    private $gamificationNotifier;
    private $rewardStepRepository;
    private $rewardRepository;
    private $proposalRepository;
    private $security;
    private $entityManager;
    private $badgeImageUri;

    public function __construct(
        NormalizerInterface $decorated,
        GamificationNotifier $gamificationNotifier,
        RewardStepRepository $rewardStepRepository,
        RewardRepository $rewardRepository,
        ProposalRepository $proposalRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        string $badgeImageUri
    ) {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->gamificationNotifier = $gamificationNotifier;
        $this->rewardStepRepository = $rewardStepRepository;
        $this->rewardRepository = $rewardRepository;
        $this->proposalRepository = $proposalRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->badgeImageUri = $badgeImageUri;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format) && $this->security->getUser() instanceof User;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        // add adType to User in admin
        if (isset($context['collection_operation_name']) && $context['collection_operation_name'] === 'ADMIN_get' && $object instanceof User) {
            $nbDriver = $this->proposalRepository->getNbActiveAdsForUserAndRole($data['id'], Ad::ROLE_DRIVER);
            $nbPassenger = $this->proposalRepository->getNbActiveAdsForUserAndRole($data['id'], Ad::ROLE_PASSENGER);
            if ($nbDriver>0 && $nbPassenger>0) {
                $data['adType'] = User::AD_DRIVER_PASSENGER;
            } elseif ($nbDriver>0) {
                $data['adType'] = User::AD_DRIVER;
            } elseif ($nbPassenger>0) {
                $data['adType'] = User::AD_PASSENGER;
            } else {
                $data['adType'] = User::AD_NONE;
            }
            return $data;
        }
        
        // We check if there is some gamificationNotifications entities in waiting for the current User

        // Waiting RewardSteps
        $waitingRewardSteps = $this->rewardStepRepository->findWaiting($this->security->getUser());
        if ($object instanceof User && is_array($data) && is_array($waitingRewardSteps) && count($waitingRewardSteps)>0) {
            $data['gamificationNotifications'] = [];
            foreach ($waitingRewardSteps as $waitingRewardStep) {
                $data['gamificationNotifications'][] = $this->formatRewardStep($waitingRewardStep);

                // We update the RewardStep and flag it as notified
                $waitingRewardStep->setNotifiedDate(new \DateTime('now'));
                $this->entityManager->persist($waitingRewardStep);
            }
        }

        // Waiting Rewards
        $waitingRewards = $this->rewardRepository->findWaiting($this->security->getUser());
        if ($object instanceof User && is_array($data) && is_array($waitingRewards) && count($waitingRewards)>0) {
            $data['gamificationNotifications'] = [];
            foreach ($waitingRewards as $waitingReward) {
                $data['gamificationNotifications'][] = $this->formatReward($waitingReward);

                // We update the RewardStep and flag it as notified
                $waitingReward->setNotifiedDate(new \DateTime('now'));
                $this->entityManager->persist($waitingReward);
            }
        }

        // New gamification notifications
        if (is_array($data) && count($this->gamificationNotifier->getNotifications())>0) {
            
            // We init the array only if it's not already filled
            if (!isset($data['gamificationNotifications'])) {
                $data['gamificationNotifications'] = [];
            }

            foreach ($this->gamificationNotifier->getNotifications() as $gamificationNotification) {
                if ($gamificationNotification instanceof Reward) {
                    $data['gamificationNotifications'][] = $this->formatReward($gamificationNotification);
                } elseif ($gamificationNotification instanceof RewardStep) {
                    $data['gamificationNotifications'][] = $this->formatRewardStep($gamificationNotification);
                    $this->entityManager->persist($gamificationNotification);
                }
            }
        }

        if (isset($data['gamificationNotifications'])) {
            // we remove RewardStep if he's associated to a gained badge
            $badgeIds = [];
            foreach ($data["gamificationNotifications"] as $gamificationNotification) {
                if ($gamificationNotification["type"] == "Badge") {
                    $badgeIds[] = $gamificationNotification["id"];
                }
            }
            foreach ($data["gamificationNotifications"] as $key => $gamificationNotification) {
                if ($gamificationNotification["type"] == "RewardStep" && in_array($gamificationNotification["badge"]["id"], $badgeIds)) {
                    unset($data["gamificationNotifications"][$key]);
                }
            }
        }

        $this->entityManager->flush();
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    /**
     * Format a RewardStep to be notified
     *
     * @param RewardStep $rewardStep
     * @return array
     */
    private function formatRewardStep(RewardStep $rewardStep): array
    {
        return [
            "type" => "RewardStep",
            "id" => $rewardStep->getId(),
            "title" => $rewardStep->getSequenceItem()->getGamificationAction()->getTitle(),
            "badge" => [
                "id" => $rewardStep->getSequenceItem()->getBadge()->getId(),
                "name" => $rewardStep->getSequenceItem()->getBadge()->getName()
            ]
        ];
    }

    /**
     * Format a Reward to be notified
     *
     * @param Reward $reward
     * @return array
     */
    private function formatReward(Reward $reward): array
    {
        return [
            "type" => "Badge",
            "id" => $reward->getBadge()->getId(),
            "name" => $reward->getBadge()->getName(),
            "title" => $reward->getBadge()->getTitle(),
            "text" => $reward->getBadge()->getText(),
            "pictures" => [
                "icon" => (!is_null($reward->getBadge()->getIcon())) ? $this->badgeImageUri.$reward->getBadge()->getIcon()->getFileName() : null,
                "decoratedIcon" => (!is_null($reward->getBadge()->getDecoratedIcon())) ? $this->badgeImageUri.$reward->getBadge()->getDecoratedIcon()->getFileName() : null,
                "image" => (!is_null($reward->getBadge()->getImage())) ? $this->badgeImageUri.$reward->getBadge()->getImage()->getFileName() : null,
                "imageLight" => (!is_null($reward->getBadge()->getImageLight())) ? $this->badgeImageUri.$reward->getBadge()->getImageLight()->getFileName() : null
            ]
        ];
    }
}
