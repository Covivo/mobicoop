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

namespace App\Payment\Command;

use App\Payment\Service\PaymentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate carpool payment items from the accepted asks.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class FullfillCarpoolPaymentsCommand extends Command
{
    private $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:payment:fullfill-carpool-payments')
            ->setDescription('Try to fullfill all waiting carpoolpayment')
            ->setHelp('Try to fullfill all waiting carpoolpayment')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->paymentManager->tryToFullfillPendingCarpoolPayments();

        return 0;
    }
}
