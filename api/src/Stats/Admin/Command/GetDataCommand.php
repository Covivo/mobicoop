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

namespace App\Stats\Admin\Command;

use App\Stats\Admin\Service\DataManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get an Analytic Data.
 *
 * @author Maxime Bardot <maxime.bardot.org>
 */
class GetDataCommand extends Command
{
    private $dataManager;

    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:stats:getData')
            ->addArgument('dataName', InputArgument::REQUIRED, 'Name of the data to retreive')
            ->addArgument('startDate', InputArgument::OPTIONAL, 'The start day of the period YYYYMMDD')
            ->addArgument('endDate', InputArgument::OPTIONAL, 'The end day of the period YYYYMMDD')
            ->addArgument('aggregInterval', InputArgument::OPTIONAL, 'Interval for aggregated statistics (1D, 1M, 1Y)')
            ->setDescription('Get the data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dataManager->setDataName($input->getArgument('dataName'));

        $startDate = $endDate = null;
        if (!is_null($input->getArgument('startDate')) && '' !== $input->getArgument('startDate')) {
            $startDate = \DateTime::createFromFormat('Ymd', $input->getArgument('startDate'));
        }
        if (!is_null($input->getArgument('endDate')) && '' !== $input->getArgument('endDate')) {
            $endDate = \DateTime::createFromFormat('Ymd', $input->getArgument('endDate'));
        }

        $this->dataManager->setStartDate($startDate);
        $this->dataManager->setEndDate($endDate);

        if (!is_null($input->getArgument('aggregInterval')) && '' !== $input->getArgument('aggregInterval')) {
            $this->dataManager->setAggregationInterval($input->getArgument('aggregInterval'));
        }

        var_dump($this->dataManager->getData());
    }
}
