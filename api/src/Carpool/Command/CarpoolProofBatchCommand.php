<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

use App\Carpool\Service\ProofManager;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Send carpool proofs.
 * This command sends the carpool proofs for the given period.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */

class CarpoolProofBatchCommand extends Command
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
        ->setName('app:carpool:proof-batch')
        ->addArgument('fromDate', InputArgument::OPTIONAL, 'The start day of the period')
        ->addArgument('toDate', InputArgument::OPTIONAL, 'The end day of the period (fromDate will be used if only fromDate is given)')
        ->setDescription('Send the carpool proofs for the given period.')
        ->setHelp('Send the carpool proofs to the carpool register; the proofs concerns the given period (default : last [xx] days, with [xx] as env parameter).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromDate = null;
        $toDate = null;
        if ($input->getArgument('fromDate')) {
            $fromDate = DateTime::createFromFormat("Ymd", $input->getArgument('fromDate'));
            $fromDate->setTime(0, 0);
            if ($input->getArgument('toDate')) {
                $toDate = DateTime::createFromFormat("Ymd", $input->getArgument('toDate'));
            } else {
                $toDate = clone $fromDate;
            }
            $toDate->setTime(23, 59, 59, 999);
        }
        return $this->proofManager->sendProofs($fromDate, $toDate);
    }
}
