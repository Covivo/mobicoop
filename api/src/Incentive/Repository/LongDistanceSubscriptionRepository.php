<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
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

    public function getSubscriptionsReadyToBeRecommited(): array
    {
        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->join('s.commitmentProofJourney', 'j')
            ->join('j.carpoolItem', 'ci')
            ->join('j.carpoolPayment', 'cp')
            ->leftJoin('ci.ask', 'a')
            ->leftJoin('a.criteria', 'c')
            ->leftJoin('a.carpoolProofs', 'cp2')
            ->where('s.status IS NULL')
            ->andWhere('c.fromDate < :now')
            ->andWhere('ci.creditorStatus != :creditorStatus OR cp.status != :paymentStatus OR cp.transactionId IS NULL OR cp2.status != :proofStatus OR cp2.type != :proofType')
            ->setParameters([
                'now' => new \DateTime(),
                'creditorStatus' => CarpoolItem::CREDITOR_STATUS_ONLINE,
                'paymentStatus' => CarpoolPayment::STATUS_SUCCESS,
                'proofStatus' => CarpoolProof::STATUS_VALIDATED,
                'proofType' => CarpoolProof::TYPE_HIGH,
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
