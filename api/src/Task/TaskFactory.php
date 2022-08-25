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

namespace App\Task;

use Exception;

class TaskFactory
{
    private const NAMESPACE = '\App\Task\\';
    private const SUFFIX = 'Task';

    private $schedule;

    public function __construct(array $schedule = [])
    {
        $this->schedule = $schedule;
    }

    /**
     * @return Task[]
     */
    public function create(): array
    {
        $tasks = [];
        $now = gmdate('H:i');
        if (array_key_exists($now, $this->schedule)) {
            foreach ($this->schedule[$now] as $taskName) {
                $tasks[] = $this->createTask($taskName);
            }
        }

        return $tasks;
    }

    private function createTask(string $taskName): Task
    {
        $task = self::NAMESPACE.$taskName.self::SUFFIX;

        try {
            return new $task();
        } catch (Exception $exception) {
            throw new Exception("Unknown task {$taskName}");
        }
    }
}
