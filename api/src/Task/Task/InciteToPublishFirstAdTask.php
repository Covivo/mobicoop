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

use App\User\Event\IncitateToPublishFirstAdEvent;
use App\User\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InciteToPublishFirstAdTask implements Task
{
    public const RELAUNCH_DELAYS = [7, 20];
    private $userRepository;
    private $eventDispatcher;
    private $askManager;

    public function __construct(UserRepository $userRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute(): int
    {
        foreach (self::RELAUNCH_DELAYS as $delay) {
            $usersIds = $this->userRepository->findUserWithNoAd($delay);

            if (count($usersIds) > 0) {
                foreach ($usersIds as $userId) {
                    $event = new IncitateToPublishFirstAdEvent($this->userRepository->find($userId));
                    $this->eventDispatcher->dispatch(IncitateToPublishFirstAdEvent::NAME, $event);
                }
            }
        }

        return 0;
    }
}
