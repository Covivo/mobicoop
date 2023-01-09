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

namespace App\Payment\Service\BankTransfert;

use App\Payment\Exception\BankTransfertException;
use App\Payment\Repository\BankTransfertRepository;
use Psr\Log\LoggerInterface;

/**
 * Bank Transfert emitter.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertSummarizer
{
    public const CSV_HEADERS = ['batchId', 'status', 'recipient', 'amount', 'territoryId'];
    private $_bankTransfertRepository;
    private $_logger;
    private $_entityManager;

    /**
     * @var BankTransfert[]
     */
    private $_bankTransferts;

    /**
     * @var string
     */
    private $_batchId;

    public function __construct(
        BankTransfertRepository $bankTransfertRepository,
        LoggerInterface $logger
    ) {
        $this->_bankTransfertRepository = $bankTransfertRepository;
        $this->_logger = $logger;
    }

    public function summarize(string $batchId)
    {
        $this->_batchId = $batchId;
        $this->_getTransferts();
        $this->_makeCsvFile();
    }

    private function _makeCsvFile()
    {
        foreach ($this->_bankTransferts as $bankTransfert) {
            $line = [];
            $line[0] = $bankTransfert->getBatchId();
            $line[1] = $bankTransfert->getStatus();
            $line[2] = $bankTransfert->getRecipient()->getId();
            $line[3] = $bankTransfert->getAmount();
            $line[4] = $bankTransfert->getTerritory()->getId();
            var_dump($line);
        }
    }

    private function _getTransferts()
    {
        if (!$this->_bankTransferts = $this->_bankTransfertRepository->findBy(['batchId' => $this->_batchId])) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] '.BankTransfertException::SUMMARIZER_NO_TRANSFERT_FOR_THIS_BATCH_ID);
        }
    }
}
