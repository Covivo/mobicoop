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

use App\Payment\Event\BankTransfertsSummarizedEvent;
use App\Payment\Exception\BankTransfertException;
use App\Payment\Repository\BankTransfertRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Bank Transfert emitter.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertsSummarizer
{
    public const PATH_TO_FILES = __DIR__.'/../../../../public/upload/bankTransferts/reports';
    public const FILES_EXTENTION = 'csv';
    public const CSV_DELIMITER = ';';

    public const CSV_HEADERS = ['batchId', 'createdDate', 'status', 'recipient', 'amount', 'territoryId'];
    private $_bankTransfertRepository;
    private $_logger;
    private $_eventDispatcher;

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
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->_bankTransfertRepository = $bankTransfertRepository;
        $this->_logger = $logger;
        $this->_eventDispatcher = $eventDispatcher;
    }

    public function summarize(string $batchId)
    {
        $this->_batchId = $batchId;
        $this->_getTransferts();
        $this->_makeCsvFile();
    }

    private function _makeCsvFile()
    {
        $file = fopen(self::PATH_TO_FILES.'/'.$this->_batchId.'.'.self::FILES_EXTENTION, 'w');
        fputcsv($file, self::CSV_HEADERS, self::CSV_DELIMITER);
        foreach ($this->_bankTransferts as $bankTransfert) {
            $line = [];
            $line[0] = $bankTransfert->getBatchId();
            $line[1] = $bankTransfert->getCreatedDate()->format('d/m/Y');
            $line[2] = $bankTransfert->getStatus();
            $line[3] = (!is_null($bankTransfert->getRecipient())) ? $bankTransfert->getRecipient()->getId() : null;
            $line[4] = $bankTransfert->getAmount();
            $line[5] = (!is_null($bankTransfert->getTerritory())) ? $bankTransfert->getTerritory()->getId() : null;
            $details = json_decode($bankTransfert->getDetails(), true);
            if (is_array($details)) {
                foreach ($details as $detail) {
                    $line[] = $detail;
                }
            }
            fputcsv($file, $line, self::CSV_DELIMITER);
        }
        fclose($file);
        $event = new BankTransfertsSummarizedEvent($this->_batchId);
        $this->_eventDispatcher->dispatch(BankTransfertsSummarizedEvent::NAME, $event);
    }

    private function _getTransferts()
    {
        if (!$this->_bankTransferts = $this->_bankTransfertRepository->findBy(['batchId' => $this->_batchId])) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] '.BankTransfertException::SUMMARIZER_NO_TRANSFERT_FOR_THIS_BATCH_ID);
        }
    }
}
