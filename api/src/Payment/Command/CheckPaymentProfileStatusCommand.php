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

namespace App\Payment\Command;

use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Service\PaymentDataProvider;
use App\Payment\Service\PaymentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate carpool payment items from the accepted asks.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CheckPaymentProfileStatusCommand extends Command
{
    private $paymentManager;
    private $paymentProfileRepository;
    private $paymentDataProvider;

    public function __construct(PaymentManager $paymentManager, PaymentProfileRepository $paymentProfileRepository, PaymentDataProvider $paymentDataProvider)
    {
        $this->paymentManager = $paymentManager;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->paymentDataProvider = $paymentDataProvider;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:payment-profile:check-batch')
            ->setDescription('Check if the payment profile is up to date with mangopay bdd.')
            ->setHelp('Get each mangoPay user and check identityProof status and update paymentProfile if needed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paymentProfiles = $this->paymentProfileRepository->findAllIdentifiers();

        if (0 == count($paymentProfiles)) {
            return 0;
        }

        foreach ($paymentProfiles as $paymentProfile) {
            if (is_null($paymentProfile['validationId'])) {
                continue;
            }

            $userPaymentProfile = $this->paymentDataProvider->getUser($paymentProfile['identifier']);

            if (isset($userPaymentProfile['ProofOfIdentity']) && !is_null($userPaymentProfile['ProofOfIdentity'])) {
                $kycDocument = $this->paymentDataProvider->getKycDocument(143377120);
                $this->paymentManager->updatePaymentProfile($kycDocument);
            }
        }
        var_dump('end');

        exit;

        return 0;
    }
}
