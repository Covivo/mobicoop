<?php
// api/src/Serializer/ApiNormalizer

namespace App\Serializer;

use App\Gamification\Entity\Badge;
use App\Gamification\Entity\GamificationNotifier;
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
    private $security;
    private $entityManager;
    private $dataUri;

    public function __construct(
        NormalizerInterface $decorated,
        GamificationNotifier $gamificationNotifier,
        RewardStepRepository $rewardStepRepository,
        RewardRepository $rewardRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        string $dataUri
    ) {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->gamificationNotifier = $gamificationNotifier;
        $this->rewardStepRepository = $rewardStepRepository;
        $this->rewardRepository = $rewardRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->dataUri = $dataUri;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format) && $this->security->getUser() instanceof User;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        
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
                $data['gamificationNotifications'][] = $this->formatBadge($waitingReward->getBadge());

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
                if ($gamificationNotification instanceof Badge) {
                    $data['gamificationNotifications'][] = $this->formatBadge($gamificationNotification);
                } elseif ($gamificationNotification instanceof RewardStep) {
                    $data['gamificationNotifications'][] = $this->formatRewardStep($gamificationNotification);
                    $gamificationNotification->setNotifiedDate(new \DateTime('now'));
                    $this->entityManager->persist($gamificationNotification);
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
     * Format a Badge to be notified
     *
     * @param Badge $badge
     * @return array
     */
    private function formatBadge(Badge $badge): array
    {
        return [
            "type" => "Badge",
            "id" => $badge->getId(),
            "name" => $badge->getName(),
            "title" => $badge->getTitle(),
            "text" => $badge->getText(),
            "pictures" => [
                "icon" => (!is_null($badge->getIcon())) ? $this->dataUri."/".$badge->getIcon()->getFileName() : null,
                "image" => (!is_null($badge->getIcon())) ? $this->dataUri."/".$badge->getImage()->getFileName() : null,
                "imageLight" => (!is_null($badge->getIcon())) ? $this->dataUri."/".$badge->getImageLight()->getFileName() : null
            ]
        ];
    }
}
