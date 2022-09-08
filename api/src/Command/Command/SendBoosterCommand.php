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

use App\Carpool\Repository\ProposalRepository;
use App\User\Event\SendBoosterEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SendBoosterCommand extends Command
{
    public const RELAUNCH_DELAY_PUNCTUAL = 2;
    public const REALUNCH_DELAY_REGULAR = 7;
    private $eventDispatcher;
    private $proposalRepository;

    public function __construct(EventDispatcherInterface $eventDispatcher, ProposalRepository $proposalRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->proposalRepository = $proposalRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:send-booster')
            ->setDescription('SendBoosterCommand')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $punctualProposals = $this->proposalRepository->findPunctualAdWithoutAskSinceXDays(self::RELAUNCH_DELAY_PUNCTUAL);
        $regularProposals = $this->proposalRepository->findRegularAdWithoutAskSinceXDays(self::REALUNCH_DELAY_REGULAR);

        if (count($punctualProposals) > 0) {
            foreach ($punctualProposals as $punctualProposal) {
                $proposal = $this->proposalRepository->find(intval($punctualProposal['proposal_id']));
                $event = new SendBoosterEvent($proposal->getUser());
                $this->eventDispatcher->dispatch(SendBoosterEvent::NAME, $event);
            }
        }
        if (count($regularProposals) > 0) {
            foreach ($regularProposals as $regularProposal) {
                $proposal = $this->proposalRepository->find(intval($regularProposal['proposal_id']));
                $event = new SendBoosterEvent($proposal->getUser());
                $this->eventDispatcher->dispatch(SendBoosterEvent::NAME, $event);
            }
        }

        return 0;
    }
}
