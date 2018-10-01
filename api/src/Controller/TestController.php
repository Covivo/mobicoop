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
        $proposal = $entityManager->getRepository(Proposal::class)->find($id);
        echo "debut<br />";
        if ($proposals = $matchingAnalyzer->findMatchingProposals($proposal)) {
            foreach ($proposals as $proposal) {
                echo $proposal->getId() . "<br />";
            }
        }
        echo "fin";
        return new Response();
    }
}