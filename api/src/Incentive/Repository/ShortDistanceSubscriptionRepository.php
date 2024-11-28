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

    public function getSubscriptionsthatMayBeReEngaged(): array
    {
        $query = "SELECT
                msds.id AS subscription_id,
                msdj2.carpool_proof_id
            FROM mobconnect__short_distance_subscription msds
                INNER JOIN mobconnect__short_distance_journey commit_journey ON msds.commitment_proof_journey_id = commit_journey.id
                INNER JOIN carpool_proof cp ON commit_journey.carpool_proof_id = cp.id
                INNER JOIN mobconnect__short_distance_journey msdj2 ON msds.id = msdj2.subscription_id AND msdj2.id != commit_journey.id
            WHERE
                msds.status IS NULL
                AND (cp.type != '".CarpoolProof::TYPE_HIGH."' OR cp.status != ".CarpoolProof::STATUS_VALIDATED.')
                AND (
                    SELECT COUNT(msdj.id)
                    FROM mobconnect__short_distance_journey msdj
                        INNER JOIN carpool_proof cp2 ON msdj.carpool_proof_id = cp2.id
                    WHERE  msdj.subscription_id = msds.id
                    AND msdj.id != commit_journey.id
                    AND cp2.status = '.CarpoolProof::STATUS_VALIDATED."
                    AND cp2.type = '".CarpoolProof::TYPE_HIGH."') > 1
            GROUP BY msds.id";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
