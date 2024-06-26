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

namespace App\Command\Command;

use App\Carpool\Event\CarpoolAskPostedRelaunch2Event;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Service\AskManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CarpoolAskPostedRelaunch2Command extends Command
{
    public const RELAUNCH_DELAY = 5;
    private $askRepository;
    private $eventDispatcher;
    private $askManager;

    public function __construct(AskRepository $askRepository, EventDispatcherInterface $eventDispatcher, AskManager $askManager)
    {
        $this->askRepository = $askRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->askManager = $askManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:carpool-ask-posted-relaunch2')
            ->setDescription('CarpoolAskPostedRelaunch2Command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $asks = $this->askRepository->findPendingAsksSinceXDays(self::RELAUNCH_DELAY);

        if (count($asks) > 0) {
            foreach ($asks as $ask) {
                $ad = $this->askManager->getAskFromAd($ask->getId(), $ask->getUser()->getId());
                $event = new CarpoolAskPostedRelaunch2Event($ad);
                $this->eventDispatcher->dispatch(CarpoolAskPostedRelaunch2Event::NAME, $event);
            }
        }

        return 0;
    }
}
