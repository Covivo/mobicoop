<?php

namespace App\Import\Repository;

use App\Import\Entity\CommunityImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method null|CommunityImport find($id, $lockMode = null, $lockVersion = null)
 * @method CommunityImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|CommunityImport findOneBy(array $criteria, array $orderBy = null)
 */
class CommunityImportRepository extends ServiceEntityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CommunityImport::class);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?CommunityImport
    {
        return $this->repository->find($id, $lockMode, $lockVersion);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?CommunityImport
    {
        return $this->repository->findOneBy($criteria);
    }
}
