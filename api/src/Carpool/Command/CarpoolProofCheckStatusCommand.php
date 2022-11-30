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

namespace App\Carpool\Command;

use App\Carpool\Service\ProofManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Send carpool proofs.
 * This command sends the carpool proofs for the given period.
 *
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class CarpoolProofCheckStatusCommand extends Command
{
    private $proofManager;

    public function __construct(ProofManager $proofManager)
    {
        $this->proofManager = $proofManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:carpool:proof-check-status')
            ->setDescription('Check the status of carpoolProofs.')
            ->setHelp('Check with the carpool register if the carpool is verified')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->proofManager->checkProofs();
    }
}
