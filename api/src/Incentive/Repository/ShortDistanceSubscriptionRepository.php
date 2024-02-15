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

    public function getSubscriptionsReadyToBeRecommited(bool $resetOnly = false): array
    {
        $countConditionOperator = $resetOnly ? '<' : '>=';

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(ShortDistanceSubscription::class, 'msds');

        $query = 'SELECT * FROM mobconnect__short_distance_subscription msds INNER JOIN mobconnect__short_distance_journey msdj ON msds.commitment_proof_journey_id = msdj.id INNER JOIN carpool_proof cp ON msdj.carpool_proof_id = cp.id INNER JOIN ask a ON cp.ask_id = a.id INNER JOIN criteria c ON a.criteria_id = c.id WHERE msds.status IS NULL AND c.from_date < NOW() AND ( cp.status != '.CarpoolProof::STATUS_VALIDATED." OR cp.`type` != '".CarpoolProof::TYPE_HIGH."' ) AND (SELECT COUNT(*) FROM mobconnect__short_distance_journey msdj2 INNER JOIN carpool_proof cp2 ON msdj2.carpool_proof_id = cp2.id WHERE msdj2.subscription_id = msds.id AND msdj2.id != msds.commitment_proof_journey_id AND cp2.status = ".CarpoolProof::STATUS_VALIDATED." AND cp2.type = '".CarpoolProof::TYPE_HIGH."') ".$countConditionOperator.' 1;';

        return $this->_em->createNativeQuery($query, $rsm)->getResult();
    }
}
