<?php

namespace App\Geography\Repository;

use App\Geography\Entity\Near;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Near|null find($id, $lockMode = null, $lockVersion = null)
 * @method Near|null findOneBy(array $criteria, array $orderBy = null)
 * @method Near[]    findAll()
 * @method Near[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NearRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Near::class);
    }
}
