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

namespace App\Carpool\Command;

use App\Carpool\Service\ProposalManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command deletes carpool orphans.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */

class RemoveCarpoolOrphansCommand extends Command
{
    private $proposalManager;
    
    public function __construct(ProposalManager $proposalManager)
    {
        $this->proposalManager = $proposalManager;
        
        parent::__construct();
    }
    
    protected function configure()
    {
        $this
        ->setName('app:carpool:remove-orphans')
        ->setDescription('Deletes carpool orphans.')
        ->setHelp('Deletes carpool orphans.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return (int)!$this->proposalManager->removeOrphans();
    }
}
