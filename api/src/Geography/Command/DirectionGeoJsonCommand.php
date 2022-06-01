<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

use App\Geography\Service\DirectionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update Directions with geoJson data.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class DirectionGeoJsonCommand extends Command
{
    private $directionManager;

    public function __construct(DirectionManager $directionManager)
    {
        $this->directionManager = $directionManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:geography:direction-geo-json')
            ->setDescription('Update directions where the geoJson detail has not been computed.')
            ->setHelp('Find directions where the geoJson detail is null, then decode the path detail and transform it to geoJson; finally put the result into geoJsonDetail.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->directionManager->updateDirectionsWithGeoJson();

        return 0;
    }
}
