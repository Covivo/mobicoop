<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceJourney;
use Doctrine\ORM\EntityManagerInterface;

class ShortDistanceJourneyRepository
{
    protected $_repository;
    protected $_entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->_repository = $entityManager->getRepository(ShortDistanceJourney::class);
        $this->_entityManager = $entityManager;
    }

    public function findOneByCarpoolProof(CarpoolProof $carpoolProof): ?ShortDistanceJourney
    {
        $qb = $this->_entityManager->createQueryBuilder('j');

        $qb
            ->where('j.carpoolProof = :cp')
            ->setParameter($carpoolProof, 'cp')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
