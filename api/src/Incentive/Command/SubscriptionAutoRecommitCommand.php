<?php

namespace App\Incentive\Command;

use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use App\Incentive\Repository\ShortDistanceSubscriptionRepository;
use App\Incentive\Service\Manager\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionAutoRecommitCommand extends EecCommand
{
    /**
     * @var LongDistanceSubscriptionRepository
     */
    private $_ldSubscriptionRepository;

    /**
     * @var ShortDistanceSubscriptionRepository
     */
    private $_sdSubscriptionRepository;

    public function __construct(EntityManagerInterface $entityManager, SubscriptionManager $subscriptionManager, LongDistanceSubscriptionRepository $longDistanceSubscriptionRepository, ShortDistanceSubscriptionRepository $shortDistanceSubscriptionRepository)
    {
        $this->_ldSubscriptionRepository = $longDistanceSubscriptionRepository;
        $this->_sdSubscriptionRepository = $shortDistanceSubscriptionRepository;

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
        $sdSubscriptions = $this->_sdSubscriptionRepository->getSubscriptionsReadyToBeReseted();
        $ldSubscriptions = $this->_ldSubscriptionRepository->getSubscriptionsReadyToBeReseted();

        foreach (array_merge($sdSubscriptions, $ldSubscriptions) as $subscription) {
            $this->_subscriptionManager->resetSubscription($subscription);
        }

        $sdSubscriptions = $this->_sdSubscriptionRepository->getSubscritpionsReadyToBeRecommited();
        $ldSubscriptions = $this->_ldSubscriptionRepository->getSubscritpionsReadyToBeRecommited();

        foreach (array_merge($sdSubscriptions, $ldSubscriptions) as $subscription) {
            $this->_subscriptionManager->recommitSubscription($subscription);
        }

        $output->writeln('The subscriptions have been updated');
    }
}
