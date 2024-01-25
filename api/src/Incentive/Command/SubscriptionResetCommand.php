<?php

namespace App\Incentive\Command;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionResetCommand extends EecCommand
{
    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-reset')
            ->setDescription('Reset manually a subscription.')
            ->setHelp('From its ID, reset manually a subscription.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The subscription type')
            ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The subscription ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_currentInput = $input;

        $subscriptionType = $this->_currentInput->getOption('type');

        $repository = Subscription::TYPE_LONG === $subscriptionType
            ? $this->_em->getRepository(LongDistanceSubscription::class)
            : $this->_em->getRepository(ShortDistanceSubscription::class);

        $subscription = $repository->find($input->getOption('subscription'));

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The requested subscription was not found');
        }

        $this->_subscriptionManager->resetSubscription($subscription);

        $output->writeln('The subscription has been updated');
    }
}
