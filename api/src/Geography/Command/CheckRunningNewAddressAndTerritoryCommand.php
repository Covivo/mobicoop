<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Geography\Command;

use App\Geography\Service\AddressTerritoryLinkChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check if the link new Addresses with territories is still running.
 * If so, it can delete the lock file (see parameters).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CheckRunningNewAddressAndTerritoryCommand extends Command
{
    private $_addressTerritoryLinkChecker;

    public function __construct(AddressTerritoryLinkChecker $addressTerritoryLinkChecker)
    {
        $this->_addressTerritoryLinkChecker = $addressTerritoryLinkChecker;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:geography:check-running-territory-link-new-addresses')
            ->addArgument('autoDeleteLockFile', InputArgument::OPTIONAL, 'If set at 1, the lock file is deleted if he is older than the limitDate. 0 by default')
            ->addArgument('limitDate', InputArgument::OPTIONAL, 'The limit date of the lock file we mean to check. If the lock file is older, we can delete it. Today by default. Format : Ymd.')
            ->setDescription('Check if the link addresses with territories is still running.')
            ->setHelp('Check if the link addresses with territories is still running.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $autoDeleteLockFile = false;
        if ('1' === $input->getArgument('autoDeleteLockFile')) {
            $autoDeleteLockFile = true;
        }

        $now = new \DateTime('now');
        $limitDate = $now->format('Ymd').' 00:00:00';
        if (null !== $input->getArgument('limitDate')) {
            $limitDate = $input->getArgument('limitDate').' 00:00:00';
        }

        $this->_addressTerritoryLinkChecker->checkLockFile(\DateTime::createFromFormat('Ymd H:i:s', $limitDate), $autoDeleteLockFile);
    }
}
