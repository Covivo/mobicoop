<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\Gamification\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Gamification\Service\GamificationManager;

/**
 * This command generate rewards and rewardsteps retroactively after the activation of the gamification
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */

class retroactivelyGenerateRewardsCommand extends Command
{
    private $gamificationManager;

    public function __construct(GamificationManager $gamificationManager)
    {
        $this->gamificationManager = $gamificationManager;

        parent::__construct();
    }
    
    protected function configure()
    {
        $this
        ->setName("app:gamification:retroactively-generate")
        ->setDescription("Generate rewards and rewardSteps retroactively gamification's activation date.")
        ->setHelp("Generate rewards and rewardSteps retroactively gamification's activation date.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return (int)!$this->gamificationManager->retroactivelyGenerateRewards();
    }
}
