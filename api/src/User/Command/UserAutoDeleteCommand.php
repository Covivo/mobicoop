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

namespace App\User\Command;

use App\User\Service\UserAutoDeleter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Auto delete user account after a customable period (see API .env).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserAutoDeleteCommand extends Command
{
    private $userAutoDeleter;

    public function __construct(UserAutoDeleter $userAutoDeleter)
    {
        $this->userAutoDeleter = $userAutoDeleter;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:user:auto-delete')
            ->setDescription('Auto delete user account after a customable period')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->userAutoDeleter->autoDelete();
    }
}
