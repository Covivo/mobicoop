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

namespace App\Task\Task;

use App\Carpool\Event\InactiveAdRelaunchEvent;
use App\Carpool\Repository\ProposalRepository;
use App\Task\Task;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InactiveAdRelaunchTask implements Task
{
    public const RELAUNCH_DELAY = 15;
    private $proposalRepository;
    private $eventDispatcher;

    public function __construct(ProposalRepository $proposalRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->proposalRepository = $proposalRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute(): int
    {
        $proposals = $this->proposalRepository->findInactiveAdsSinceXDays(self::RELAUNCH_DELAY);

        if (count($proposals) > 0) {
            foreach ($proposals as $proposal) {
                $event = new InactiveAdRelaunchEvent($proposal);
                $this->eventDispatcher->dispatch(InactiveAdRelaunchEvent::NAME, $event);
            }
        }

        return 0;
    }
}
