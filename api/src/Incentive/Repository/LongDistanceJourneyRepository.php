<?php

namespace App\Incentive\Repository;

use App\Incentive\Entity\LongDistanceJourney;
use Doctrine\ORM\EntityManagerInterface;

class LongDistanceJourneyRepository
{
    protected $_repository;
    protected $_entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->_repository = $entityManager->getRepository(LongDistanceJourney::class);
        $this->_entityManager = $entityManager;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->_repository->findBy($criteria, $orderBy, $limit, $offset);
    }
}
