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
 */

namespace App\Serializer;

use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Community\Entity\CommunityUser;
use App\Gamification\Entity\GamificationNotifier;
use App\Gamification\Entity\Reward;
use App\Gamification\Entity\RewardStep;
use App\Gamification\Repository\RewardRepository;
use App\Gamification\Repository\RewardStepRepository;
use App\Service\FormatDataManager;
use App\User\Entity\User;
use App\User\Service\IdentityProofManager;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
    private $gamificationActive;
    private $logger;
    private $request;
    private $userManager;

    private $log = false;

    private $currentRewardStep;

    private $gratuityActive;
    private $gratuityNotificationNormalizer;

    private $formatDataManager;
    private $identityProofManager;

    public function __construct(
        NormalizerInterface $decorated,
        GamificationNotifier $gamificationNotifier,
        RewardStepRepository $rewardStepRepository,
        RewardRepository $rewardRepository,
        ProposalRepository $proposalRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        string $badgeImageUri,
        bool $gamificationActive,
        LoggerInterface $logger,
        RequestStack $request,
        UserManager $userManager,
        bool $gratuityActive,
        GratuityNotificationNormalizer $gratuityNotificationNormalizer,
        FormatDataManager $formatDataManager,
        IdentityProofManager $identityProofManager
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
        $this->gamificationActive = $gamificationActive;
        $this->logger = $logger;
        $this->request = $request->getCurrentRequest();
        $this->userManager = $userManager;
        $this->gratuityActive = $gratuityActive;
        $this->gratuityNotificationNormalizer = $gratuityNotificationNormalizer;
        $this->formatDataManager = $formatDataManager;
        $this->identityProofManager = $identityProofManager;
    }

    public function getCurrentRewardStep(): RewardStep
    {
        return $this->currentRewardStep;
    }

    public function setCurrentRewardStep(RewardStep $rewardStep)
    {
        $this->currentRewardStep = $rewardStep;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format) && $this->security->getUser() instanceof User;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        if ($this->log) {
            $this->logger->info('Api Normalize on '.get_class($object));
        }

        $data = $this->decorated->normalize($object, $format, $context);

        // add adType to User in admin
        if (isset($context['collection_operation_name']) && 'ADMIN_get' === $context['collection_operation_name'] && ($object instanceof User || $object instanceof CommunityUser)) {
            if ($object instanceof User) {
                $user = $data['id'];
            } else {
                $user = $data['userId'];
            }

            return $this->_getAdData($data, $user);
        }
        if (true == $this->gratuityActive && $object instanceof User && $object->getId() === $this->security->getUser()->getId()) {
            $this->gratuityNotificationNormalizer->setUser($this->security->getUser());
            $data = $this->gratuityNotificationNormalizer->normalize($data);
        }
        // We check if there is some gamificationNotifications entities in waiting for the current User
        if (true == $this->gamificationActive && $object instanceof User && $object->getId() === $this->security->getUser()->getId()) {
            // Waiting RewardSteps
            $waitingRewardSteps = $this->rewardStepRepository->findWaiting($this->security->getUser());
            if (is_array($data) && is_array($waitingRewardSteps) && count($waitingRewardSteps) > 0) {
                $data['gamificationNotifications'] = [];
                foreach ($waitingRewardSteps as $waitingRewardStep) {
                    $data['gamificationNotifications'][] = $this->formatRewardStep($waitingRewardStep);
                }
            }

            // Waiting Rewards
            $waitingRewards = $this->rewardRepository->findWaiting($this->security->getUser());
            if (is_array($data) && is_array($waitingRewards) && count($waitingRewards) > 0) {
                $data['gamificationNotifications'] = [];
                foreach ($waitingRewards as $waitingReward) {
                    $data['gamificationNotifications'][] = $this->formatReward($waitingReward);
                }
            }

            // New gamification notifications
            if (is_array($data) && count($this->gamificationNotifier->getNotifications()) > 0) {
                // We init the array only if it's not already filled
                if (!isset($data['gamificationNotifications'])) {
                    $data['gamificationNotifications'] = [];
                }
                foreach ($this->gamificationNotifier->getNotifications() as $gamificationNotification) {
                    if ($gamificationNotification instanceof Reward) {
                        $rewardIds = [];
                        foreach ($data['gamificationNotifications'] as $notification) {
                            if ('Reward' == $notification['type']) {
                                $rewardIds[] = $notification['id'];
                            }
                        }
                        if (!in_array($gamificationNotification->getId(), $rewardIds)) {
                            $data['gamificationNotifications'][] = $this->formatReward($gamificationNotification);
                        }
                    } elseif ($gamificationNotification instanceof RewardStep) {
                        $rewardStepIds = [];
                        foreach ($data['gamificationNotifications'] as $notification) {
                            if ('RewardStep' == $notification['type']) {
                                $rewardStepIds[] = $notification['id'];
                            }
                        }
                        if (!in_array($gamificationNotification->getId(), $rewardStepIds)) {
                            $data['gamificationNotifications'][] = $this->formatRewardStep($gamificationNotification);
                        }
                        $this->entityManager->persist($gamificationNotification);
                    }
                }
            }
            if (isset($data['gamificationNotifications'])) {
                // we remove RewardStep if he's associated to a gained badge
                $badgeIds = [];
                foreach ($data['gamificationNotifications'] as $gamificationNotification) {
                    if ('Badge' == $gamificationNotification['type']) {
                        $badgeIds[] = $gamificationNotification['id'];
                    }
                }
                foreach ($data['gamificationNotifications'] as $key => $gamificationNotification) {
                    if ('RewardStep' == $gamificationNotification['type'] && in_array($gamificationNotification['badge']['id'], $badgeIds)) {
                        unset($data['gamificationNotifications'][$key]);
                    }
                }
            }
            $this->entityManager->flush();
        }

        if ($object instanceof User && isset($data['identityProofs'])) {
            foreach ($data['identityProofs'] as $key => $proof) {
                $data['identityProofs'][$key]['fileSize'] = $this->formatDataManager->convertFilesize($proof['size']);
                $data['identityProofs'][$key]['fileName'] = $this->identityProofManager->getFileUrlFromArray($proof);
            }
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($this->log) {
            $this->logger->info('Api Denormalize on '.$class);
        }

        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    private function replaceDynamicValuesInRewardStep(string $chain): string
    {
        $chain = str_replace('{minCount}', $this->getCurrentRewardStep()->getSequenceItem()->getMinCount(), $chain);
        $chain = str_replace('{minUniqueCount}', $this->getCurrentRewardStep()->getSequenceItem()->getMinUniqueCount(), $chain);

        return str_replace('{value}', $this->getCurrentRewardStep()->getSequenceItem()->getValue(), $chain);
    }

    /**
     * Format a RewardStep to be notified.
     */
    private function formatRewardStep(RewardStep $rewardStep): array
    {
        if ($this->log) {
            $this->logger->info('Api Normalize formatRewardStep '.$rewardStep->getId());
        }

        $this->setCurrentRewardStep($rewardStep);

        return [
            'type' => 'RewardStep',
            'id' => $rewardStep->getId(),
            'title' => $this->replaceDynamicValuesInRewardStep($rewardStep->getSequenceItem()->getGamificationAction()->getTitle()),
            'notifiedDate' => $rewardStep->getNotifiedDate(),
            'badge' => [
                'id' => $rewardStep->getSequenceItem()->getBadge()->getId(),
                'name' => $rewardStep->getSequenceItem()->getBadge()->getName(),
                'title' => $rewardStep->getSequenceItem()->getBadge()->getTitle(),
            ],
        ];
    }

    /**
     * Format a Reward to be notified.
     */
    private function formatReward(Reward $reward): array
    {
        if ($this->log) {
            $this->logger->info('Api Normalize formatReward '.$reward->getId());
        }

        return [
            'type' => 'Badge',
            'id' => $reward->getBadge()->getId(),
            'rewardId' => $reward->getId(),
            'name' => $reward->getBadge()->getName(),
            'title' => $reward->getBadge()->getTitle(),
            'notifiedDate' => $reward->getNotifiedDate(),
            'text' => $reward->getBadge()->getText(),
            'pictures' => [
                'icon' => (!is_null($reward->getBadge()->getIcon())) ? $this->badgeImageUri.$reward->getBadge()->getIcon()->getFileName() : null,
                'decoratedIcon' => (!is_null($reward->getBadge()->getDecoratedIcon())) ? $this->badgeImageUri.$reward->getBadge()->getDecoratedIcon()->getFileName() : null,
                'image' => (!is_null($reward->getBadge()->getImage())) ? $this->badgeImageUri.$reward->getBadge()->getImage()->getFileName() : null,
                'imageLight' => (!is_null($reward->getBadge()->getImageLight())) ? $this->badgeImageUri.$reward->getBadge()->getImageLight()->getFileName() : null,
            ],
        ];
    }

    private function _getAdData(array $data, int $userId): array
    {
        $nbDriver = $this->proposalRepository->getNbActiveAdsForUserAndRole($userId, Ad::ROLE_DRIVER);
        $nbPassenger = $this->proposalRepository->getNbActiveAdsForUserAndRole($userId, Ad::ROLE_PASSENGER);

        if ($nbDriver > 0 && $nbPassenger > 0) {
            $data['adType'] = User::AD_DRIVER_PASSENGER;
        } elseif ($nbDriver > 0) {
            $data['adType'] = User::AD_DRIVER;
        } elseif ($nbPassenger > 0) {
            $data['adType'] = User::AD_PASSENGER;
        } else {
            $data['adType'] = User::AD_NONE;
        }

        if (isset($data['communityId'])) {
            $nbAdsInCommunityAsDriver = $this->proposalRepository->getNbActiveAdsForUserAndRole($userId, Ad::ROLE_DRIVER, $data['communityId']);
            $nbAdsInCommunityAsPassenger = $this->proposalRepository->getNbActiveAdsForUserAndRole($userId, Ad::ROLE_PASSENGER, $data['communityId']);

            $data['adsInCommunityAsDriver'] = $nbAdsInCommunityAsDriver > 0;
            $data['adsInCommunityAsPassenger'] = $nbAdsInCommunityAsPassenger > 0;
        }

        return $data;
    }
}
