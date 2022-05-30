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

use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Service\ProofManager;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a carpool proof for test purpose.
 * This command don't send any proof to the register.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofTestGenerateCommand extends Command
{
    private $proofManager;
    private $carpoolProofRepository;
    private $logger;

    public function __construct(CarpoolProofRepository $carpoolProofRepository, ProofManager $proofManager, LoggerInterface $logger)
    {
        $this->proofManager = $proofManager;
        $this->carpoolProofRepository = $carpoolProofRepository;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:carpool:proof-test-generate')
            ->addArgument('proofId', InputArgument::OPTIONAL, 'The CarpoolProof id to generate')
            ->setDescription('Generate a carpool proof for test purpose.')
            ->setHelp('Generate a carpool proof for test purpose; This command don\'t send any proof to the register.).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $proof = $this->carpoolProofRepository->find($input->getArgument('proofId'));
        $provider = new CarpoolProofGouvProvider('', '', 'test', $this->logger, true);
        var_dump(json_encode($provider->serializeProof($proof)));
    }
}
