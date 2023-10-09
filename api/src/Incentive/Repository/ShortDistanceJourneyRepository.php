<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceJourney;
use Doctrine\ORM\EntityManagerInterface;

class ShortDistanceJourneyRepository
{
    protected $_entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    public function findOneByCarpoolProof(CarpoolProof $carpoolProof): ?ShortDistanceJourney
    {
        $qb = $this->_entityManager->createQueryBuilder('j');

        $qb
            ->select('j')
            ->from(ShortDistanceJourney::class, 'j')
            ->where('j.carpoolProof = :cp')
            ->setParameter('cp', $carpoolProof)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
