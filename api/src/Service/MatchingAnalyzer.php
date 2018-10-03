<?php 

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Proposal;

/**
 * Matching analyzer service.
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class MatchingAnalyzer
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function findMatchingProposals(Proposal $proposal)
    {
        return $this->entityManager->getRepository(Proposal::class)->findMatchingProposals($proposal);
    }

}