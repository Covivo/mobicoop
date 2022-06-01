<?php

namespace App\Import\Repository;

use App\Import\Entity\EventImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method null|EventImport find($id, $lockMode = null, $lockVersion = null)
 * @method EventImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|EventImport findOneBy(array $criteria, array $orderBy = null)
 */
class EventImportRepository extends ServiceEntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(EventImport::class);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?EventImport
    {
        return $this->repository->find($id, $lockMode, $lockVersion);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?EventImport
    {
        return $this->repository->findOneBy($criteria);
    }
}
