<?php

namespace App\Incentive\Command;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Service\Manager\JourneyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionCommitCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    /**
     * @var InputInterface
     */
    private $_currentInput;

    public function __construct(EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        $this->_em = $em;
        $this->_journeyManager = $journeyManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-commit')
            ->setDescription('Returns the subscription tokens.')
            ->setHelp('Returns the subscription tokens and if requested, obtain them before from moB.')
            ->addArgument('type', InputArgument::REQUIRED, 'The id of the user')
            ->addArgument('subscription_id', InputArgument::REQUIRED, 'Specifies whether missing tokens should be obtained')
            ->addArgument('journey_id', InputArgument::REQUIRED, 'Specifies whether missing tokens should be obtained')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_currentInput = $input;

        $subscriptionType = $this->_currentInput->getArgument('type');

        if (!Subscription::isTypeAllowed($subscriptionType)) {
            throw new BadRequestHttpException('The value for the type parameter is invalid. Please choose one of the values among: '.join(', ', Subscription::ALLOWED_TYPE));
        }

        Subscription::TYPE_LONG === $subscriptionType
            ? $this->_commitLDSubscription()
            : $this->_commitSDSubscription();

        $output->writeln('The incentive has been updated');
    }

    private function _commitLDSubscription()
    {
        $subscription = $this->_em->getRepository(LongDistanceSubscription::class)->find($this->_currentInput->getArgument('subscription_id'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        $initialProposal = $this->_em->getRepository(Proposal::class)->find($this->_currentInput->getArgument('journey_id'));

        if (is_null($initialProposal)) {
            throw new NotFoundHttpException('The journey (Proposal) was not found');
        }

        $subscription->reset();

        $this->_em->flush();

        $this->_journeyManager->declareFirstLongDistanceJourney($initialProposal);
    }

    private function _commitSDSubscription()
    {
        $subscription = $this->_em->getRepository(ShortDistanceSubscription::class)->find($this->_currentInput->getArgument('subscription_id'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_currentInput->getArgument('journey_id'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The journey (CarpoolProof) was not found');
        }

        $subscription->reset();

        $this->_em->flush();

        $this->_journeyManager->declareFirstShortDistanceJourney($carpoolProof);
    }
}
