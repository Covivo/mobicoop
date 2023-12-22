<?php

namespace App\Incentive\Command;

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

class SubscriptionResetCommand extends EecCommand
{
    public function __construct(EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        parent::__construct($em, $journeyManager);
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:proof-invalidate')
            ->setDescription('Invalidate manually a proof.')
            ->setHelp('From its CarpoolProof ID, manually reset a subscription.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The subscription type')
            ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The subscription ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_currentInput = $input;

        $subscriptionType = $this->_currentInput->getOption('type');

        if (!Subscription::isTypeAllowed($subscriptionType)) {
            throw new BadRequestHttpException('The value for the type parameter is invalid. Please choose one of the values among: '.join(', ', Subscription::ALLOWED_TYPE));
        }

        /**
         * @var LongDistanceSubscription|ShortDistanceSubscription
         */
        $subscription = Subscription::TYPE_LONG === $subscriptionType
            ? $this->_em->getRepository(LongDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'))
            : $this->_em->getRepository(ShortDistanceSubscription::class)->find($this->_currentInput->getOption('subscription'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The requested subscription was not found');
        }

        $subscription->reset();

        $this->_em->flush();

        $output->writeln('The subscription has been updated');
    }
}
