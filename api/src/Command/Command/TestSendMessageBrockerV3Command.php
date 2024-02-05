<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Command\Command;

use App\DataProvider\Entity\MessageBrokerV3\MessageBrokerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestSendMessageBrockerV3Command extends Command
{
    private $_messageBrokerProvider;

    public function __construct(MessageBrokerProvider $messageBrokerProvider)
    {
        parent::__construct();
        $this->_messageBrokerProvider = $messageBrokerProvider;
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:testSendMessageBrokerV3')
            ->setDescription('Test send a message brocker V3')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
