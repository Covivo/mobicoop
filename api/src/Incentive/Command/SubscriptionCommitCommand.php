<?php

namespace App\Incentive\Command;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Service\Manager\JourneyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionCommitCommand extends EecCommand
{
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
            ->setDescription('Commit manually a subscription.')
            ->setHelp('From a Proposal or a CarpoolProof, manually commit a subscription.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The subscription type')
            ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The subscription ID')
            ->addOption('journey', null, InputOption::VALUE_REQUIRED, 'Depending on the case, the ID of the Proposal or the CarpoolProof')
            ->addOption('pushOnly', null, InputOption::VALUE_NONE, 'Indicates whether the trip should be saved in BDD or only pushed to moB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_currentInput = $input;

        $subscriptionType = $this->_currentInput->getOption('type');

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
        $this->_currentSubscription = $this->_em->getRepository(LongDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($this->_currentSubscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        /**
         * @var Proposal
         */
        $initialProposal = $this->_em->getRepository(Proposal::class)->find($this->_currentInput->getOption('journey'));

        $this->checkProposal($initialProposal);

        $this->_currentSubscription->reset();

        $this->_em->flush();

        $this->_journeyManager->declareFirstLongDistanceJourney($initialProposal, $this->_currentInput->getOption('pushOnly'));
    }

    private function _commitSDSubscription()
    {
        $this->_currentSubscription = $this->_em->getRepository(ShortDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($this->_currentSubscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        /**
         * @var CarpoolProof
         */
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_currentInput->getOption('journey'));

        $this->checkCarpoolProof($carpoolProof);

        $this->_currentSubscription->reset();

        $this->_em->flush();

        $this->_journeyManager->declareFirstShortDistanceJourney($carpoolProof, $this->_currentInput->getOption('pushOnly'));
    }
}
