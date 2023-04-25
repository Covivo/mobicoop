<?php

namespace App\UserRelaunch\Command;

use App\UserRelaunch\Service\RelaunchManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RelaunchCommand extends Command
{
    /**
     * @var RelaunchManager
     */
    private $_relaunchManager;

    public function __construct(RelaunchManager $relaunchManager)
    {
        parent::__construct();

        $this->_relaunchManager = $relaunchManager;
    }

    public function configure()
    {
        $this
            ->setName('app:relaunch:users')
            ->setDescription('Send relaunch notifications to users.')
            ->setHelp('Send relaunch notifications to users as configured in the relaunch.json file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->_relaunchManager->relaunchUsers();
    }
}
