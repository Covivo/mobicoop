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

declare(strict_types=1);

namespace App\Command\Command;

use App\Carpool\Repository\ProposalRepository;
use App\DataProvider\Entity\MobicoopMatcherProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMatcherV3Command extends Command
{
    private $mobicoopMatcherProvider;
    private $proposalRepository;

    public function __construct(MobicoopMatcherProvider $mobicoopMatcherProvider, ProposalRepository $proposalRepository)
    {
        parent::__construct();
        $this->mobicoopMatcherProvider = $mobicoopMatcherProvider;
        $this->proposalRepository = $proposalRepository;
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:testMatcherV3')
            ->addArgument('proposalId', InputArgument::REQUIRED, 'The id of the search/ad proposal')
            ->setDescription('Test matcher V3')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->mobicoopMatcherProvider->match($this->proposalRepository->find((int) $input->getArgument('proposalId')));

        return 0;
    }
}
