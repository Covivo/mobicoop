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

namespace App\Payment\Service\Transaction;

use App\Payment\Service\PaymentDataProvider;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class TransactionManager
{
    public const PATH_TO_FILES = __DIR__.'/../../../../public/export/transactions';
    public const FILES_EXTENTION = 'csv';
    public const CSV_DELIMITER = ';';

    private $_paymentProvider;
    private $_TransactionsSummarizer;
    private $_entityManager;

    /**
     * @var string
     */
    private $_batchId;

    public function __construct(
        PaymentDataProvider $paymentProvider,
        TransactionsSummarizer $TransactionsSummarizer
    ) {
        $this->_paymentProvider = $paymentProvider;
        $this->_TransactionsSummarizer = $TransactionsSummarizer;
    }

    public function getWalletTransactions($walletId, $beforeDate, $afterDate)
    {
        $transactions = $this->_paymentProvider->getWalletTransactions($walletId, $beforeDate, $afterDate);
        $this->_TransactionsSummarizer->summarize($transactions);
    }
}
