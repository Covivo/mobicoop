<?php

namespace App\Incentive\Repository;

use App\Incentive\Entity\LongDistanceSubscription;
use Doctrine\ORM\EntityManagerInterface;

class LongDistanceSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(EntityManagerInterface $em, int $deadline)
    {
        parent::__construct($em, $deadline);

        $this->_repository = $this->_em->getRepository(LongDistanceSubscription::class);
    }

    public function subscriptionsWithoutJourney(\DateTimeInterface $deadline)
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->leftJoin('s.longDistanceJourneys', 'j')
            ->where('s.createdAt <= :deadline')
            ->setParameter('deadline', $deadline)
            ->andWhere('j.id IS NULL')
        ;

        return $qb->getQuery()->getResult();
    }

    public function subscriptionsWithUnrealizedJourneys(\DateTimeInterface $deadline, \DateTimeInterface $transitionalPeriodEndDate)
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->join('s.commitmentProofJourney', 'cj')
            ->join('cj.initialProposal', 'p')
            ->join('p.matchingOffers', 'mo')
            ->join('mo.asks', 'a')
            ->join('a.criteria', 'c')
            ->join('a.carpoolProofs', 'cp')
            ->where('s.commitmentProofJourney IS NOT NULL')
            ->andWhere('s.createdAt <= :deadline')
            ->setParameter('deadline', $deadline)
            ->andWhere('c.fromDate <= :transitionalPeriodEndDate')
            ->setParameter('transitionalPeriodEndDate', $transitionalPeriodEndDate)
            ->andWhere('cp.id IS NULL')
        ;

        return $qb->getQuery()->getResult();
    }

    public function subscriptionsWithJourneysAfterExpiry(\DateTimeInterface $deadline, \DateTimeInterface $transitionalPeriodEndDate)
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->join('s.commitmentProofJourney', 'cj')
            ->join('cj.initialProposal', 'p')
            ->join('p.matchingOffers', 'mo')
            ->join('mo.asks', 'a')
            ->join('a.criteria', 'c')
            ->where('s.commitmentProofJourney IS NOT NULL')
            ->andWhere('s.createdAt <= :deadline')
            ->setParameter('deadline', $deadline)
            ->andWhere('c.fromDate > :transitionalPeriodEndDate')
            ->setParameter('transitionalPeriodEndDate', $transitionalPeriodEndDate)
        ;

        return $qb->getQuery()->getResult();
    }

    public function subscriptionsWithJourneysPublishedAfterExpiry(\DateTimeInterface $deadline)
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->leftJoin('s.commitmentProofJourney', 'j')
            ->where('s.createdAt <= :deadline')
            ->andWhere('j.createdAt >= :deadline')
            ->setParameter('deadline', $deadline)
        ;

        return $qb->getQuery()->getResult();
    }
}
