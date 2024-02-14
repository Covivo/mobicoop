<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
            ->join('j.carpoolProof', 'cp')
            ->join('cp.ask', 'a')
            ->join('a.criteria', 'c')
            ->where('s.status IS NULL')
            ->andWhere('c.fromDate < :now')
            ->andWhere('cp.status != :status OR cp.type != :type')
            ->setParameters([
                'now' => new \DateTime(),
                'status' => CarpoolProof::STATUS_VALIDATED,
                'type' => CarpoolProof::TYPE_HIGH,
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function getSubscritpionsReadyToBeRecommited(): array
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(ShortDistanceSubscription::class, 'msds');

        $query = 'SELECT * FROM mobconnect__short_distance_subscription msds INNER JOIN mobconnect__short_distance_journey msdj ON msds.commitment_proof_journey_id = msdj.id INNER JOIN carpool_proof cp ON msdj.carpool_proof_id = cp.id INNER JOIN ask a ON cp.ask_id = a.id INNER JOIN criteria c ON a.criteria_id = c.id WHERE msds.status IS NULL AND c.from_date < NOW() AND ( cp.status != '.CarpoolProof::STATUS_VALIDATED." OR cp.`type` != '".CarpoolProof::TYPE_HIGH."' ) AND (SELECT COUNT(*) FROM mobconnect__short_distance_journey msdj2 INNER JOIN carpool_proof cp2 ON msdj2.carpool_proof_id = cp2.id WHERE msdj2.subscription_id = msds.id AND msdj2.id != msds.commitment_proof_journey_id AND cp2.status = ".CarpoolProof::STATUS_VALIDATED." AND cp2.type = '".CarpoolProof::TYPE_HIGH."') >= 1;";

        return $this->_em->createNativeQuery($query, $rsm)->getResult();
    }
}
