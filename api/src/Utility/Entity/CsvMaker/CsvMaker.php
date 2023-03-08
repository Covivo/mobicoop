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

use App\Utility\Entity\FtpUploader;
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
    public const PATH_TO_QUERIES = __DIR__.'/queries';
    public const CSV_DELIMITER = ';';
    public const SINGLE_QUERY_FILE_EXTENTION = 'sql';
    public const MULTI_QUERY_FILE_EXTENTION = 'php';
    private $_entityManager;
    private $_queryResults;
    private $_logger;
    private $_csvExports;
    private $_service;
    private $_ftpUploader;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, array $csvExports)
    {
        $this->_entityManager = $entityManager;
        $this->_logger = $logger;
        $this->_csvExports = $csvExports;
        $this->_ftpUploader = null;
    }

    public function make()
    {
        foreach ($this->_csvExports as $service => $csvExport) {
            $this->_service = $service;

            foreach ($csvExport['queries'] as $query) {
                if (is_array($csvExport['upload'])) {
                    $this->_initFtpUploader($csvExport['upload']);
                }

                if (file_exists(self::PATH_TO_QUERIES.'/'.$query.'.'.self::SINGLE_QUERY_FILE_EXTENTION)) {
                    $this->_makeCsvFileFromSingleQuery($query);
                } elseif (file_exists(self::PATH_TO_QUERIES.'/'.$query.'.'.self::MULTI_QUERY_FILE_EXTENTION)) {
                    $this->_makeCsvFileFromMultipleQuery($query);
                } else {
                    $this->_logger->error($query.' not found in queries folder');
                }
            }
        }
    }

    private function _initFtpUploader(array $csvExport)
    {
        $this->_ftpUploader = new FtpUploader(
            $csvExport['protocol'],
            $csvExport['serverUri'],
            $csvExport['login'],
            $csvExport['password'],
            $csvExport['remotePath']
        );
    }

    private function _uploadFile(string $file)
    {
        if (!is_null($this->_ftpUploader)) {
            $this->_logger->info('upload : '.$file);
            $this->_ftpUploader->upload($file);
        }
    }

    private function _makeCsvFileFromMultipleQuery(string $file)
    {
        $this->_logger->info('makeCsvFileFromMultipleQuery : '.$file);

        include self::PATH_TO_QUERIES.'/'.$file.'.'.self::MULTI_QUERY_FILE_EXTENTION;
        $this->_executeMultipleQuery($multipleQueries);
        $this->_writeResults($file);
    }

    private function _makeCsvFileFromSingleQuery(string $query)
    {
        $this->_logger->info('makeCsvFileFromSingleQuery : '.$query);
        $query_in_file = file_get_contents(self::PATH_TO_QUERIES.'/'.$query.'.'.self::SINGLE_QUERY_FILE_EXTENTION);

        $this->_executeSingleQuery($query_in_file);
        $this->_writeResults($query);
    }

    private function _writeResults(string $resultsFileName)
    {
        $this->_logger->info('Writing results in file');

        $folder = self::PATH_TO_FILES.'/'.$this->_service;

        if (!file_exists($folder)) {
            mkdir($folder);
        }

        $path = $folder.'/'.date('YmdHis').'-'.$resultsFileName.'.csv';
        $file = fopen($path, 'w+');
        $header = false;
        foreach ($this->_queryResults as $result) {
            if (!$header) {
                fputcsv($file, array_keys($result), self::CSV_DELIMITER);
                $header = true;
            }
            fputcsv($file, $result, self::CSV_DELIMITER);
        }
        fclose($file);

        $this->_uploadFile($path);
    }

    private function _executeMultipleQuery(array $multipleQueries)
    {
        $this->_logger->info('Execute multiple queries');
        foreach ($multipleQueries as $query) {
            $this->_logger->info($query);
            $stmt = $this->_entityManager->getConnection()->prepare($query);
            $stmt->execute();
        }

        $this->_queryResults = $stmt->fetchAll();
    }

    private function _executeSingleQuery(string $query)
    {
        $this->_logger->info('Execute single query');
        $this->_logger->info($query);
        $stmt = $this->_entityManager->getConnection()->prepare($query);

        $stmt->execute();

        $this->_queryResults = $stmt->fetchAll();
    }
}
