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

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommandsCommand extends Command
{
    private const PREFIX = 'app:commands:';

    private $schedule;

    public function __construct(array $schedule = [])
    {
        $this->schedule = $schedule;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:execute')
            ->setDescription('Execute commands at a given time')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = 0;

        /**
         * @var Command[] $commands
         */
        $commands = $this->createCommands();

        foreach ($commands as $command) {
            $result += $command->run(new ArrayInput([]), $output);
        }

        return (int) !(0 == $result);
    }

    private function createCommands(): array
    {
        $commands = [];
        $commandNames = $this->createCommandNames($this->schedule);
        foreach ($commandNames as $commandName) {
            try {
                $commands[] = $this->getApplication()->find($commandName);
            } catch (CommandNotFoundException $exception) {
                throw $exception;
            }
        }

        return $commands;
    }

    private function createCommandNames(array $schedule): array
    {
        $commandNames = [];
        $now = gmdate('H:i');
        if (array_key_exists($now, $schedule)) {
            foreach ($schedule[$now] as $commandName) {
                $commandNames[] = self::PREFIX.$commandName;
            }
        }

        return $commandNames;
    }
}
