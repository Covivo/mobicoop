<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionAutoRecommitCommand extends EecCommand
{
    public function __construct(EntityManagerInterface $entityManager, SubscriptionManager $subscriptionManager)
    {
        parent::__construct($entityManager, $subscriptionManager);
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-autorecommit')
            ->setDescription('Automatically recommits erroneous subscriptions.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_subscriptionManager->autoRecommitSubscriptions();

        $output->writeln('The subscriptions have been updated');
    }
}
