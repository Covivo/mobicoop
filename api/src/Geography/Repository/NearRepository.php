<?php

namespace App\Geography\Repository;

use App\Geography\Entity\Near;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Near|null find($id, $lockMode = null, $lockVersion = null)
 * @method Near|null findOneBy(array $criteria, array $orderBy = null)
 * @method Near[]    findAll()
 * @method Near[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NearRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Near::class);
    }

//    /**
//     * @return Near[] Returns an array of Near objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Near
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
