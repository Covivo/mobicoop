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

namespace App\Carpool\Command;

use App\Carpool\Service\ProofManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manualy send carpool proofs in Pending status
 * This command sends the carpool proofs for the given period.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofManualSendCommand extends Command
{
    private $_proofManager;

    public function __construct(ProofManager $proofManager)
    {
        $this->_proofManager = $proofManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:carpool:manual-send')
            ->setDescription('Manually send carpool proof (by default in PENDING status).')
            ->setHelp('Manually send carpool proof (by default in PENDING status).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->_proofManager->manualSendCarpoolProof();
    }
}
