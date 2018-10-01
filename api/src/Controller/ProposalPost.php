<?php 

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Service\ProposalManager;
use App\Entity\Proposal;
use App\Service\MatchingAnalyzer;

/**
 * Controller class for proposal post.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalPost
{
    private $proposalManager;
    private $matchingAnalyzer;

    public function __construct(ProposalManager $proposalManager, MatchingAnalyzer $matchingAnalyzer)
    {
        $this->proposalManager = $proposalManager;
        $this->matchingAnalyzer = $matchingAnalyzer;
    }

    public function __invoke(Proposal $data): Proposal
    {
        $this->proposalManager->createProposal($data);
        $this->matchingAnalyzer->findMatchingProposals($data);
        return $data;
    }
    
}