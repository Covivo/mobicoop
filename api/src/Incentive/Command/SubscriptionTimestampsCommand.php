<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionTimestampsCommand extends Command
{
    private const ALLOWED_TYPES = ['long', 'short'];

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->_subscriptionManager = $subscriptionManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-timestamps')
            ->setDescription('Set subscription timestamps.')
            ->setHelp('From the ID of a subscription and conditionally, retrieves and hydrates a subscription with the corresponding timestamps.')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of the subscription (allowed long and short)')
            ->addArgument('subscription', InputArgument::REQUIRED, 'The ID of the subscription to be processed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!in_array($input->getArgument('type'), self::ALLOWED_TYPES)) {
            throw new \LogicException('The subscription type is not allowed. Use among '.join(' OR ', self::ALLOWED_TYPES));
        }

        return $this->_subscriptionManager->setUserSubscriptionTimestamps($input->getArgument('type'), $input->getArgument('subscription'));
    }
}
