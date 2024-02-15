<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use Doctrine\ORM\EntityManagerInterface;

class ShortDistanceSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(EntityManagerInterface $em, int $deadline)
    {
        parent::__construct($em, $deadline);

        $this->_repository = $this->_em->getRepository(ShortDistanceSubscription::class);
    }

    public function findByProofCommitment(CarpoolProof $carpoolProof): ?ShortDistanceSubscription
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->innerJoin('s.shortDistanceJourneys', 'j')
            ->where('j.carpoolProof = :proof')
            ->setParameter('proof', $carpoolProof)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getSubscriptionsReadyToBeRecommited(): array
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->join('s.commitmentProofJourney', 'j')
            ->join('j.carpoolProof', 'cp')
            ->join('cp.ask', 'a')
            ->join('a.criteria', 'c')
            ->where('s.status IS NULL')
            ->andWhere('c.fromDate < :now')
            ->andWhere('cp.status != :proofStatus OR cp.type != :proofType')
            ->setParameters([
                'now' => new \DateTime(),
                'proofStatus' => CarpoolProof::STATUS_VALIDATED,
                'proofType' => CarpoolProof::TYPE_HIGH,
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
