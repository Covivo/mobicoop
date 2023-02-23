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

namespace App\Geography\Command;

use App\Geography\Repository\TerritoryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindPointTerritoriesCommand extends Command
{
    private $territoryRepository;

    public function __construct(TerritoryRepository $territoryRepository)
    {
        $this->territoryRepository = $territoryRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:geography:find-point-territories')
            ->setDescription('Find the territories of a point lat/lon.')
            ->setHelp('Find the territories of a point lat/lon.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lat = 46.468027;
        $lon = -0.817133;

        var_dump($this->territoryRepository->findPointTerritoriesIds($lat, $lon));
    }
}
