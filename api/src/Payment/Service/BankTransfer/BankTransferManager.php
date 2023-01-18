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

namespace App\Payment\Service\BankTransfer;

use App\Payment\Exception\BankTransferException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Bank Transfert Manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransferManager
{
    public const PATH_TO_FILES = __DIR__.'/../../../../public/upload/bankTransfers';
    public const FILES_EXTENTION = 'csv';
    public const CSV_DELIMITER = ';';

    private $_BankTransferCollectionBuilder;
    private $_entityManager;
    private $_BankTransferEmitter;
    private $_BankTransfersSummarizer;

    public function __construct(
        BankTransferCollectionBuilder $BankTransferCollectionBuilder,
        EntityManagerInterface $entityManager,
        BankTransferEmitter $BankTransferEmitter,
        BankTransfersSummarizer $BankTransfersSummarizer
    ) {
        $this->_BankTransferCollectionBuilder = $BankTransferCollectionBuilder;
        $this->_entityManager = $entityManager;
        $this->_BankTransferEmitter = $BankTransferEmitter;
        $this->_BankTransfersSummarizer = $BankTransfersSummarizer;
    }

    public function makeBankTransfers()
    {
        $files = glob(self::PATH_TO_FILES.'/*.'.self::FILES_EXTENTION);

        if (0 == count($files)) {
            echo 'No file detected';

            return;
        }

        foreach ($files as $filepath) {
            $this->_checkCsvDelimiter($filepath);
            $this->_BankTransferCollectionBuilder->setFilePath($filepath);
            $this->_BankTransferCollectionBuilder->build();
            $this->_BankTransferCollectionBuilder->getBatchId();
            $this->_showConsoleResults();
            foreach ($this->_BankTransferCollectionBuilder->getBankTransfers() as $BankTransfer) {
                $this->_entityManager->persist($BankTransfer);
            }
            $this->_entityManager->flush();
        }

        // $this->_BankTransferEmitter->emit($this->_BankTransferCollectionBuilder->getBatchId());
        $this->_BankTransfersSummarizer->summarize($this->_BankTransferCollectionBuilder->getBatchId());
    }

    private function _checkCsvDelimiter(string $filepath)
    {
        try {
            $file = fopen($filepath, 'r');
        } catch (\Exception $e) {
            throw new BankTransferException(BankTransferException::ERROR_OPENING_FILE.$filepath);
        }

        $cpt = 0;
        while (!feof($file)) {
            $line = fgets($file);
            ++$cpt;
            if ('' !== trim($line)) {
                if (false == strpos($line, self::CSV_DELIMITER)) {
                    fclose($file);

                    throw new BankTransferException(BankTransferException::BAD_DELIMITER.$line);
                }
            }
        }

        fclose($file);
    }

    private function _showConsoleResults()
    {
        echo '------------------'.PHP_EOL;
        foreach ($this->_BankTransferCollectionBuilder->getBankTransfers() as $BankTransfer) {
            echo 'id : '.$BankTransfer->getId().PHP_EOL;
            echo 'amount : '.(!is_null($BankTransfer->getAmount()) ? $BankTransfer->getAmount() : 'null').PHP_EOL;
            echo 'recipientId : '.(!is_null($BankTransfer->getRecipient()) ? $BankTransfer->getRecipient()->getId() : 'null').PHP_EOL;
            echo 'territoryId : '.(!is_null($BankTransfer->getTerritory()) ? $BankTransfer->getTerritory()->getId() : 'null').PHP_EOL;
            echo 'carpoolProofId : '.(!is_null($BankTransfer->getCarpoolProof()) ? $BankTransfer->getCarpoolProof()->getId() : 'null').PHP_EOL;
            echo 'details : '.$BankTransfer->getDetails().PHP_EOL;
            echo 'status : '.$BankTransfer->getStatus().PHP_EOL;
            echo '------------------'.PHP_EOL;
        }
    }
}
