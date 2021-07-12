<?php
// api/src/Serializer/ApiNormalizer

namespace App\Serializer;

use App\Gamification\Entity\Badge;
use App\Gamification\Entity\GamificationNotifier;
use App\Gamification\Entity\RewardStep;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;
    private $gamificationNotifier;

    public function __construct(NormalizerInterface $decorated, GamificationNotifier $gamificationNotifier)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->gamificationNotifier = $gamificationNotifier;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (is_array($data) && count($this->gamificationNotifier->getNotifications())>0) {
            $data['gamificationNotifications'] = [];
            foreach ($this->gamificationNotifier->getNotifications() as $gamificationNotification) {
                if ($gamificationNotification instanceof Badge) {
                    $data['gamificationNotifications'][] = [
                        "type" => "Badge",
                        "id" => $gamificationNotification->getId(),
                        "name" => $gamificationNotification->getName()
                    ];
                } elseif ($gamificationNotification instanceof RewardStep) {
                    $data['gamificationNotifications'][] = [
                        "type" => "RewardStep",
                        "id" => $gamificationNotification->getId(),
                        "title" => $gamificationNotification->getSequenceItem()->getGamificationAction()->getTitle(),
                        "badge" => [
                            "id" => $gamificationNotification->getSequenceItem()->getBadge()->getId(),
                            "name" => $gamificationNotification->getSequenceItem()->getBadge()->getName()
                        ]
                    ];
                }
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
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
