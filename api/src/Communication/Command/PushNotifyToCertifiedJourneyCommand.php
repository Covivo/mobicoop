<?php

namespace App\Communication\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushNotifyToCertifiedJourneyCommand extends Command
{
    /**
     * @var int
     */
    private $_interval;

    public function __construct(?int $interval)
    {
        parent::__construct();

        $this->_interval = $interval;
    }

    protected function configure()
    {
        $this
            ->setName('app:notify:journey-certify')
            ->setDescription('Sends a push notification 10 minutes before departure time and 10 minutes before the end of the journey to invite you to certify your journey')
            ->addOption('interval', null, InputOption::VALUE_OPTIONAL, 'Specifies the time interval to be respected between broadcasts. This interval must be the same as that of the CRON that executes the command. By default we use an interval of 10 minutes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {}
}
