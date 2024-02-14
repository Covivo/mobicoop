<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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

    /**
     * Returns subscriptions list including the commitment journey:
     * - Happened,
     * - Does not comply with the EEC standard.
     */
    public function getSubscriptionsReadyToBeReseted(): array
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

    public function getSubscritpionsReadyToBeRecommited(): array
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(LongDistanceSubscription::class, 'mlds');

        $query = 'SELECT * FROM mobconnect__long_distance_subscription mlds INNER JOIN mobconnect__long_distance_journey mldj ON mlds.commitment_proof_journey_id = mldj.id INNER JOIN carpool_item ci ON mldj.carpool_item_id = ci.id INNER JOIN carpool_payment cp ON mldj.carpool_payment_id = cp.id JOIN ask a ON ci.ask_id = a.id JOIN criteria c ON a.criteria_id = c.id JOIN carpool_proof cp2 ON a.id = cp2.ask_id  WHERE mlds.status IS NULL AND c.from_date < NOW() AND ( ci.id IS NULL OR ci.creditor_status != '.CarpoolItem::CREDITOR_STATUS_ONLINE.' OR cp.id IS NULL OR cp.status != '.CarpoolPayment::STATUS_SUCCESS.' OR cp.transaction_id IS NULL OR cp2.id IS NULL  OR cp2.status != '.CarpoolProof::STATUS_VALIDATED." OR cp2.type != '".CarpoolProof::TYPE_HIGH."' ) AND (SELECT COUNT(*) FROM mobconnect__long_distance_journey mldj2 INNER JOIN carpool_item ci2 ON mldj2.carpool_item_id = ci2.id INNER JOIN carpool_payment cp3 ON mldj2.carpool_payment_id = cp3.id JOIN ask a2 ON ci.ask_id = a2.id JOIN carpool_proof cp4 ON a2.id = cp4.ask_id  WHERE mldj2.subscription_id = mlds.id AND mldj2.id != mlds.commitment_proof_journey_id AND ci.creditor_status = ".CarpoolItem::CREDITOR_STATUS_ONLINE.' AND cp3.status = '.CarpoolPayment::STATUS_SUCCESS.' AND cp3.transaction_id IS NOT NULL AND cp4.status = '.CarpoolProof::STATUS_VALIDATED." AND cp4.type = '".CarpoolProof::TYPE_HIGH."') >= 1 ;";

        return $this->_em->createNativeQuery($query, $rsm)->getResult();
    }
}
