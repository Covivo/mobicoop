<?php

namespace App\Incentive\Command;

use App\Incentive\Entity\Subscription;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Incentive\Service\Provider\SubscriptionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SubscriptionRecommitCommand extends EecCommand
{
    public function __construct(EntityManagerInterface $entityManager, SubscriptionManager $subscriptionManager)
    {
        parent::__construct($entityManager, $subscriptionManager);
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-recommit')
            ->setDescription('Recommits erroneous subscription.')
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

        $this->_subscriptionManager->recommitSubscription(
            SubscriptionProvider::getSubscriptionFromType($this->_em, $subscriptionType, intval($input->getOption('subscription')))
        );

        $output->writeln('The incentive has been updated');
    }
}
