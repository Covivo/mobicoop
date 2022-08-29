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

declare(strict_types=1);

namespace App\Command;

use App\User\Event\NewlyRegisteredUserEvent;
use App\User\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SendWelcomeEmailCommand extends Command
{
    private $userRepository;
    private $eventDispatcher;

    public function __construct(UserRepository $userRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:send-welcome-email')
            ->setDescription('SendWelcomeEmailCommand')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->userRepository->findNewlyRegisteredUsers();

        if (count($users) > 0) {
            foreach ($users as $user) {
                $event = new NewlyRegisteredUserEvent($user);
                $this->eventDispatcher->dispatch(NewlyRegisteredUserEvent::NAME, $event);
            }
        }

        return 0;
    }
}
