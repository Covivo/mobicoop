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
use Doctrine\ORM\EntityManagerInterface;

/**
 * Bank Transfert Manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertManager
{
    public const PATH_TO_FILES = __DIR__.'/../../../../public/upload/bankTransferts';
    public const FILES_EXTENTION = 'csv';
    public const CSV_DELIMITER = ';';

    private $_bankTransfertCollectionBuilder;
    private $_entityManager;
    private $_bankTransfertEmitter;

    public function __construct(BankTransfertCollectionBuilder $bankTransfertCollectionBuilder, EntityManagerInterface $entityManager, BankTransfertEmitter $bankTransfertEmitter)
    {
        $this->_bankTransfertCollectionBuilder = $bankTransfertCollectionBuilder;
        $this->_entityManager = $entityManager;
        $this->_bankTransfertEmitter = $bankTransfertEmitter;
    }

    public function makeBankTransferts()
    {
        $files = glob(self::PATH_TO_FILES.'/*.'.self::FILES_EXTENTION);

        if (0 == count($files)) {
            echo 'No file detected';

            return;
        }

        foreach ($files as $filepath) {
            $this->_checkCsvDelimiter($filepath);
            $this->_bankTransfertCollectionBuilder->setFilePath($filepath);
            $this->_bankTransfertCollectionBuilder->build();
            $this->_showConsoleResults();
            foreach ($this->_bankTransfertCollectionBuilder->getBankTransferts() as $bankTransfert) {
                $this->_entityManager->persist($bankTransfert);
            }
            $this->_entityManager->flush();
        }

        $this->_bankTransfertEmitter->emit($this->_bankTransfertCollectionBuilder->getBatchId());
    }

    private function _checkCsvDelimiter(string $filepath)
    {
        try {
            $file = fopen($filepath, 'r');
        } catch (\Exception $e) {
            throw new BankTransfertException(BankTransfertException::ERROR_OPENING_FILE.$filepath);
        }

        $cpt = 0;
        while (!feof($file)) {
            $line = fgets($file);
            ++$cpt;
            if ('' !== trim($line)) {
                if (false == strpos($line, self::CSV_DELIMITER)) {
                    fclose($file);

                    throw new BankTransfertException(BankTransfertException::BAD_DELIMITER.$line);
                }
            }
        }

        fclose($file);
    }

    private function _showConsoleResults()
    {
        echo '------------------'.PHP_EOL;
        foreach ($this->_bankTransfertCollectionBuilder->getBankTransferts() as $bankTransfert) {
            echo 'id : '.$bankTransfert->getId().PHP_EOL;
            echo 'amount : '.(!is_null($bankTransfert->getAmount()) ? $bankTransfert->getAmount() : 'null').PHP_EOL;
            echo 'recipientId : '.(!is_null($bankTransfert->getRecipient()) ? $bankTransfert->getRecipient()->getId() : 'null').PHP_EOL;
            echo 'territoryId : '.(!is_null($bankTransfert->getTerritory()) ? $bankTransfert->getTerritory()->getId() : 'null').PHP_EOL;
            echo 'carpoolProofId : '.(!is_null($bankTransfert->getCarpoolProof()) ? $bankTransfert->getCarpoolProof()->getId() : 'null').PHP_EOL;
            echo 'details : '.$bankTransfert->getDetails().PHP_EOL;
            echo 'status : '.$bankTransfert->getStatus().PHP_EOL;
            echo '------------------'.PHP_EOL;
        }
    }
}
