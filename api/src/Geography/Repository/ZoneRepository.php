<?php

namespace App\Geography\Repository;

use App\Geography\Entity\Zone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Zone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zone[]    findAll()
 * @method Zone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZoneRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Zone::class);
    }
    
    /**
     * Return the zone for a given latitude and logitude.
     * @param int $latitude     The latitude
     * @param int $longitude    The longitude
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The zone found
     */
    public function findOneByLatitudeLongitude($latitude, $longitude)
    {
        $query = $this->repository->createQueryBuilder('z')
        ->andWhere('z.fromLat <= :lat')
        ->andWhere('z.toLat >= :lat')
        ->andWhere('z.fromLon <= :lon')
        ->andWhere('z.toLon >= :lon')
        ->setParameter('lat', $latitude)
        ->setParameter('lon', $longitude)
        ->getQuery();
        
        return $query->getOneOrNullResult()
        ;
    }

}
