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

namespace App\Utility\Entity\CsvMaker;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Csv file maker.
 *
 *@author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CsvMaker
{
    public const PATH_TO_FILES = __DIR__.'/../../../../public/upload/csvExport';
    public const PATH_TO_QUERIES = __DIR__.'/queries/';
    public const CSV_DELIMITER = ';';
    private $_entityManager;
    private $_queryResults;
    private $_logger;
    private $_csvExports;
    private $_service;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, array $csvExports)
    {
        $this->_entityManager = $entityManager;
        $this->_logger = $logger;
        $this->_csvExports = $csvExports;
    }

    public function make()
    {
        foreach ($this->_csvExports as $service => $csvExport) {
            $this->_service = $service;
            foreach ($csvExport['queries'] as $query) {
                $this->_makeCsvFile($query);
            }
        }
    }

    private function _makeCsvFile(string $query)
    {
        try {
            $query_in_file = file_get_contents(self::PATH_TO_QUERIES.$query.'.sql');
        } catch (\Exception $e) {
            $this->_logger->error('No file : '.self::PATH_TO_QUERIES.$query.'.sql');

            return;
        }

        $this->_executeQuery($query_in_file);
        $this->_writeResults($query);
    }

    private function _writeResults(string $resultsFileName)
    {
        $this->_logger->info('Writing results in file');

        $folder = self::PATH_TO_FILES.'/'.$this->_service;

        if (!file_exists($folder)) {
            mkdir($folder);
        }

        $file = fopen($folder.'/'.$resultsFileName.'.csv', 'w+');
        $header = false;
        foreach ($this->_queryResults as $result) {
            if (!$header) {
                fputcsv($file, array_keys($result), self::CSV_DELIMITER);
                $header = true;
            }
            fputcsv($file, $result, self::CSV_DELIMITER);
        }
        fclose($file);
    }

    private function _executeQuery(string $query)
    {
        $this->_logger->info('Execute query');
        $this->_logger->info($query);
        $stmt = $this->_entityManager->getConnection()->prepare($query);

        $stmt->execute();

        $this->_queryResults = $stmt->fetchAll();
    }
}
