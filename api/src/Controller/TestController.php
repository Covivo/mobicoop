<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MatchingAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Proposal;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for API testing purpose.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class TestController extends AbstractController
{
    /**
     * Show matching proposals for a given proposal.
     *
     * @Route("/matcher/{id}", name="matcher", requirements={"id"="\d+"})
     *
     */
    public function matcher($id, EntityManagerInterface $entityManager, MatchingAnalyzer $matchingAnalyzer)
    {
        if ($proposal = $entityManager->getRepository(Proposal::class)->find($id)) {
            // we search for the starting and ending point
            $startLocality = null;
            $endLocality = null;
            foreach ($proposal->getPoints() as $point) {
                if ($point->getPosition() == 0) $startLocality = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                if ($point->getLastPoint()) $endLocality = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                if (!is_null($startLocality) && !is_null($endLocality)) break;
            }
            echo ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? "Offer" : "Request") . " #$id : <ul>";
            echo "<li>" . $proposal->getUser()->getEmail() . "</li>";
            echo "<li>$startLocality => $endLocality</li>";
            echo "<li>" . $proposal->getCriteria()->getFromDate()->format('d/m/Y') . "</li>";
            echo "</ul>";
            echo ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? "Requests" : "Offers") . " found :";
            if ($proposals = $matchingAnalyzer->findMatchingProposals($proposal)) {
                echo "<ul>";
                foreach ($proposals as $proposalFound) {
                    $startLocalityFound = null;
                    $endLocalityFound = null;
                    foreach ($proposalFound->getPoints() as $point) {
                        if ($point->getPosition() == 0) $startLocalityFound = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                        if ($point->getLastPoint()) $endLocalityFound = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                        if (!is_null($startLocalityFound) && !is_null($endLocalityFound)) break;
                    }
                    echo "<li>Proposal #" . $proposalFound->getId() . "<ul>";
                    echo "<li>" . $proposalFound->getUser()->getEmail() . "</li>";
                    echo "<li>$startLocalityFound => $endLocalityFound</li>"; 
                    echo "<li>" . $proposalFound->getCriteria()->getFromDate()->format('d/m/Y') . "</li></ul>";
                }
                echo "</ul>";
            }
        } else {
            echo "No proposal found with id #$id";
        }
        return new Response();
    }
}