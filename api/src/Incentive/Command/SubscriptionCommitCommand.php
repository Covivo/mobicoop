<?php

namespace App\Incentive\Command;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionCommitCommand extends EecCommand
{
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
            ->addOption('noReset', null, InputOption::VALUE_NONE, 'The subscription will not be reset and no journeys will be deleted. Can be used when there is no engagement path in error.')
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
        $subscription = $this->_em->getRepository(LongDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        /**
         * @var Proposal
         */
        $proposal = $this->_em->getRepository(Proposal::class)->find($this->_currentInput->getOption('journey'));

        if (is_null($proposal)) {
            throw new NotFoundHttpException('The requested Proposal was not found');
        }

        $this->_subscriptionManager->commitSubscription($subscription, $proposal, $this->_currentInput->getOption('pushOnly'), $this->_currentInput->getOption('noReset'));
    }

    private function _commitSDSubscription()
    {
        $subscription = $this->_em->getRepository(ShortDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        /**
         * @var CarpoolProof
         */
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_currentInput->getOption('journey'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The requested CarpoolProof was not found');
        }

        $this->_subscriptionManager->commitSubscription($subscription, $carpoolProof, $this->_currentInput->getOption('pushOnly'), $this->_currentInput->getOption('noReset'));
    }
}
