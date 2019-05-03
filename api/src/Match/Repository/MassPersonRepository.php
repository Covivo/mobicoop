<?php

namespace App\Match\Repository;

use App\Match\Entity\MassPerson;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Match\Entity\Mass;

/**
 * @method MassPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method MassPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method MassPerson[]    findAll()
 * @method MassPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MassPersonRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(MassPerson::class);
    }

    /**
     * Return all destinations for a mass.
     *
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The destinations (Address) found
     */
    public function findAllDestinationsForMass(Mass $mass)
    {
        $query = $this->repository->createQueryBuilder('mp')
            ->select('DISTINCT wa.houseNumber, wa.street, wa.postalCode, wa.addressLocality, wa.addressCountry')
            ->leftJoin('mp.workAddress', 'wa')
            ->andWhere('mp.mass = :mass')
            ->setParameter('mass', $mass)
            ->getQuery();

        return $query->getResult();
    }
}
