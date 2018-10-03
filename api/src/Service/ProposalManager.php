<?php 

namespace App\Service;

use App\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Proposal manager service.
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalManager
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function createProposal(Proposal $proposal)
    {
        $this->entityManager->persist($proposal);
        // here we should launch the matching analyzer to compute matching proposals and save them in the matching entity
    }
    
}