<?php

namespace App\Geography\Repository;

use App\Geography\Entity\Direction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Direction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Direction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Direction[]    findAll()
 * @method Direction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectionRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Direction::class);
    }
    
    /**
     * Return all directions without zones.
     *
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The directions found
     */
    public function findAllWithoutZones()
    {
        $query = $this->repository->createQueryBuilder('d')
        ->leftJoin('d.zones', 'z')
        ->andWhere('z.direction IS NULL')
        ->getQuery();
        
        return $query->getResult()
        ;
    }
}
