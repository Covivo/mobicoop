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

namespace App\Utility\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Csv file maker.
 *
 *@author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CsvMaker
{
    public const PATH_TO_FILES = __DIR__.'/../../../public/upload';
    private $_query;
    private $_entityManager;
    private $_queryResults;
    private $_logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->_entityManager = $entityManager;
        $this->_logger = $logger;
    }

    public function setQuery(string $query)
    {
        $this->_query = $query;
    }

    public function make()
    {
        $this->_executeQuery();
        $this->_writeResults();
    }

    private function _writeResults()
    {
        $this->_logger->info('Writing results in file');
        $file = fopen(self::PATH_TO_FILES.'/testCsv.csv', 'w+');
        foreach ($this->_queryResults as $result) {
            fputcsv($file, $result, ';');
        }
        fclose($file);
    }

    private function _executeQuery()
    {
        $this->_logger->info('Execute query');
        $this->_logger->info($this->_query);
        $stmt = $this->_entityManager->getConnection()->prepare($this->_query);

        $stmt->execute();

        $this->_queryResults = $stmt->fetchAll();
    }
}
