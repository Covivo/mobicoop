<?php

namespace App\Incentive\Command;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Service\Manager\JourneyManager;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionUpdateCommand extends EecCommand
{
    public function __construct(EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        parent::__construct($em, $journeyManager);
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-update')
            ->setDescription('Update manually a subscription.')
            ->setHelp('From a CarpoolPayment or a CarpoolProof, manually update a subscription.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The subscription type')
            ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The subscription ID')
            ->addOption('journey', null, InputOption::VALUE_REQUIRED, 'Depending on the case, the ID of the CarpoolPayment or the CarpoolProof')
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
            ? $this->_updateLDSubscription()
            : $this->_updateSDSubscription();

        $output->writeln('The incentive has been updated');
    }

    private function _updateLDSubscription()
    {
        $subscription = $this->_em->getRepository(LongDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        $carpoolPayment = $this->_em->getRepository(CarpoolPayment::class)->find($this->_currentInput->getOption('journey'));

        if (is_null($carpoolPayment)) {
            throw new NotFoundHttpException('The requested CarpoolPayment was not found');
        }

        $this->_subscriptionManager->validateSubscription($carpoolPayment, $this->_currentInput->getOption('pushOnly'));
    }

    private function _updateSDSubscription()
    {
        $subscription = $this->_em->getRepository(ShortDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_currentInput->getOption('journey'));

        $this->checkCarpoolProof($carpoolProof);

        if ($subscription->getUser()->getId() !== $carpoolProof->getDriver()->getId()) {
            throw new BadRequestHttpException('The user associated with the incentive is not the one associated with the CarpoolProof');
        }

        $this->_subscriptionManager->validateSubscription($carpoolProof, $this->_currentInput->getOption('pushOnly'));
    }
}
