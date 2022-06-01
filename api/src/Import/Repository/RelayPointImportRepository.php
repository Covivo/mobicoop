<?php

namespace App\Import\Repository;

use App\Import\Entity\RelayPointImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method null|RelayPointImport find($id, $lockMode = null, $lockVersion = null)
 * @method RelayPointImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|RelayPointImport findOneBy(array $criteria, array $orderBy = null)
 */
class RelayPointImportRepository extends ServiceEntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(RelayPointImport::class);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?RelayPointImport
    {
        return $this->repository->find($id, $lockMode, $lockVersion);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?RelayPointImport
    {
        return $this->repository->findOneBy($criteria);
    }
}
