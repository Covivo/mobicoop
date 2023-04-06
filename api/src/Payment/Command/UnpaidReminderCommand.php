<?php

namespace App\Payment\Command;

use App\Payment\Service\UnpaidReminderManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnpaidReminderCommand extends Command
{
    /**
     * @var UnpaidReminderManager
     */
    private $_unpaidReminderManager;

    public function __construct(UnpaidReminderManager $unpaidReminderManager)
    {
        parent::__construct();

        $this->_unpaidReminderManager = $unpaidReminderManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:payment:unpaid-reminder')
            ->setDescription('Sending a reminder email to passengers with unpaid journeys.')
            ->setHelp('Sending a reminder email to passengers with unpaid journeys.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->_unpaidReminderManager->SendReminderEmails();
    }
}
