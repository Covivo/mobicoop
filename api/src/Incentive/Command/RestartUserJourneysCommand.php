<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\RelaunchManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartUserJourneysCommand extends Command
{
    /**
     * @var RelaunchManager
     */
    private $_relaunchManager;

    public function __construct(RelaunchManager $relaunchManager)
    {
        $this->_relaunchManager = $relaunchManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:restart-user-journeys')
            ->addArgument('user', InputArgument::REQUIRED, 'User ID')
            ->setDescription('Restart the declaration of a user\'s journeys.')
            ->setHelp('Restarts the declaration of a user\'s journeys corresponding to the EEC standard')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->_relaunchManager->relaunchJourneysForUser(intval($input->getArgument('user')));
    }
}
