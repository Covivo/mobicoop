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
    
    /**
     * Find proposals matching the proposal passed as an argument.
     * 
     * @param Proposal $proposal
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    public function findMatchingProposals(Proposal $proposal) {
        
        // LIMITATIONS : 
        // - only punctual journeys
        // - only 2 points : starting point and destination
        // - only one-way trip
        
        // we search for the starting and ending point of the proposal
        $startLocality = null;
        $endLocality = null;
        foreach ($proposal->getPoints() as $point) {
            if ($point->getPosition() == 0) $startLocality = $point->getAddress()->getAddressLocality();
            if ($point->getLastPoint()) $endLocality = $point->getAddress()->getAddressLocality();
            if (!is_null($startLocality) && !is_null($endLocality)) break;
        }
        
        // we search the matchings in the proposal entity
        $query = $this->createQueryBuilder('p')
        // we also need the criteria (for the dates, number of seats...) and the starting/ending points/addresses for the location
        ->join('p.criteria', 'c')
        ->join('p.points', 'startPoint')
        ->join('p.points', 'endPoint')
        ->join('startPoint.address', 'startAddress')
        ->join('endPoint.address', 'endAddress');
        
        // we search for the opposite proposal type (offer => requests // request => offers)
        $query->andWhere('p.proposalType = :proposalType')
        ->setParameter('proposalType', ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? Proposal::PROPOSAL_TYPE_REQUEST : Proposal::PROPOSAL_TYPE_OFFER));
        
        // for now we limit the search to the same day 
        $query->andWhere('c.fromDate = :fromDate')
        ->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'));
       
        // we limit the search to the starting and ending point locality
        $query->andWhere('startPoint.position = 0')
        ->andWhere('endPoint.lastPoint = 1');
        $query->andWhere('startAddress.addressLocality = :startLocality')
        ->andWhere('endAddress.addressLocality = :endLocality')
        ->setParameter('startLocality', $startLocality)
        ->setParameter('endLocality', $endLocality);
        
        // we launch the request and return the result
        return $query->getQuery()->getResult();
        
    }
        
}
