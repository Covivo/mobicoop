<?php

namespace App\Geography\Repository;

use App\Geography\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Zone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zone[]    findAll()
 * @method Zone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Zone::class);
    }
    
    /**
     * Return the zone for a given latitude and logitude.
     * @param int $latitude     The latitude
     * @param int $longitude    The longitude
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The zone found
     */
    public function findOneByLatitudeLongitude($latitude,$longitude)
    {
        $query = $this->createQueryBuilder('z')
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

//    /**
//     * @return Zone[] Returns an array of Zone objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Zone
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
