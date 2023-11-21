<?php

namespace App\Incentive\Command;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Service\Manager\JourneyManager;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionUpdateCommand extends Command
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
            ->setName('app:incentive:subscription-update')
            ->setDescription('Update manually a subscription.')
            ->setHelp('From a CarpoolPayment or a CarpoolProof, manually update a subscription.')
            ->addArgument('type', InputArgument::REQUIRED, 'The subscription type')
            ->addArgument('subscription_id', InputArgument::REQUIRED, 'The subscription ID')
            ->addArgument('journey_id', InputArgument::REQUIRED, 'Depending on the case, the ID of the CarpoolPayment or the CarpoolProof')
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
            ? $this->_updateLDSubscription()
            : $this->_updateSDSubscription();

        $output->writeln('The incentive has been updated');
    }

    private function _updateLDSubscription()
    {
        $subscription = $this->_em->getRepository(LongDistanceSubscription::class)->find($this->_currentInput->getArgument('subscription_id'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        $carpoolPayment = $this->_em->getRepository(CarpoolPayment::class)->find($this->_currentInput->getArgument('journey_id'));

        if (is_null($carpoolPayment)) {
            throw new NotFoundHttpException('The payment was not found');
        }

        $this->_journeyManager->receivingElectronicPayment($carpoolPayment);
    }

    private function _updateSDSubscription()
    {
        $subscription = $this->_em->getRepository(ShortDistanceSubscription::class)->find($this->_currentInput->getArgument('subscription_id'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_currentInput->getArgument('journey_id'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The proof was not found');
        }

        $this->_journeyManager->validationOfProof($carpoolProof);
    }
}
