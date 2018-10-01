<?php

namespace App\Repository;

use App\Entity\Proposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Proposal::class);
    }
    
    public function findMatchingProposals(Proposal $proposal) {
        
        return $this->createQueryBuilder('p')
        ->join('p.criteria', 'c')
        ->join('p.points', 'po')
        ->andWhere('p.proposalType = :proposalType')
        ->andWhere('c.fromDate = :fromDate')
        ->setParameter('proposalType', ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? Proposal::PROPOSAL_TYPE_REQUEST : Proposal::PROPOSAL_TYPE_OFFER))
        ->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'))
        ->getQuery()
        ->getResult()
        ;
    }
        
}
