<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\Event\Command;

use App\Event\Service\EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate carpool payment items from the accepted asks.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */

class MailSendCommand extends Command
{
    private $eventManager;
    private $sendEmailEnabled;

    public function __construct(EventManager $eventManager, Bool $eventImportEnabled)
    {
        $this->eventManager = $eventManager;
        $this->eventImportEnabled = $eventImportEnabled;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:email:send')
            ->setDescription('Send email to user to alert of the end of validity of an ad')
            ->setHelp('blabla');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->sendEmailEnabled == false) {
            return 0;
        }

        $this->eventManager->importEvents();
        return 0;
    }
}
