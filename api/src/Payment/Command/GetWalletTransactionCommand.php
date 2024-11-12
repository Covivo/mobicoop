<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

use App\Payment\Service\Transaction\TransactionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class GetWalletTransactionCommand extends Command
{
    private $_TransactionManager;
    private $paymentAccountantAccountId;

    public function __construct(TransactionManager $TransactionManager, $paymentAccountantAccountId)
    {
        parent::__construct();
        $this->_TransactionManager = $TransactionManager;
        $this->paymentAccountantAccountId = $paymentAccountantAccountId;
    }

    protected function configure()
    {
        $this
            ->setName('app:wallet:get-last-month-transactions')
            ->setDescription('Make a serie of Bank Transfert using a CSV file as input.')
            ->setHelp('Make a serie of Bank Transfert using a CSV file as input.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->_TransactionManager->getLastMonthWalletTransactions($this->paymentAccountantAccountId);
    }
}
