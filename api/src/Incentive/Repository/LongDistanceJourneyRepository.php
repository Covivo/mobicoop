<?php

namespace App\Incentive\Repository;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Payment\Entity\CarpoolItem;
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

    public function findOneBy(array $parameters): ?LongDistanceJourney
    {
        return $this->_repository->findOneBy($parameters);
    }

    public function findOneByCarpoolItem(CarpoolItem $carpoolItem): ?LongDistanceJourney
    {
        return $this->_repository->findOneBy(['carpoolItem' => $carpoolItem]);
    }

    public function findOneByCarpoolItemOrProposal(?CarpoolItem $carpoolItem, ?Proposal $proposal): ?LongDistanceJourney
    {
        if (is_null($carpoolItem)) {
            return null;
        }

        $qb = $this->_entityManager->createQueryBuilder('j');

        $qb
            ->select('j')
            ->from(LongDistanceJourney::class, 'j')
            ->where('j.carpoolItem = :ci')
            ->orWhere('j.initialProposal = :p')
            ->setParameters([
                'ci' => $carpoolItem,
                'p' => $proposal,
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
