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

namespace App\Import\Admin\Service;

use App\Import\Admin\Interfaces\LineImportValidatorInterface;
use App\Import\Admin\Interfaces\PopulatorInterface;
use App\Import\Admin\Resource\Import;
use App\Import\Admin\Service\Populator\UserImportPopulator;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Importer
{
    public const MIME_TYPES = [
        'text/plain',
        'text/csv',
    ];

    /**
     * @var File
     */
    private $_file;

    /**
     * @var string
     */
    private $_filename;

    private $_errors;
    private $_messages;

    private $_manager;

    public function __construct(File $file, string $filename, object $manager = null)
    {
        $this->_file = $file;
        $this->_filename = $filename;
        $this->_validateFile();
        $this->_manager = $manager;
        $this->_errors = [];
        $this->_messages = [];
    }

    public function importUsers(): Import
    {
        $this->_validateLines(new UserLineImportValidator());
        if (0 == count($this->_errors)) {
            $this->_populateTable(new UserImportPopulator($this->_manager));
        }

        return $this->_buildImport();
    }

    public function importRelayPoints(): Import
    {
        // define validation and populator
        return $this->_buildImport();
    }

    private function _validateFile()
    {
        if (!in_array($this->_file->getMimeType(), self::MIME_TYPES)) {
            throw new \LogicException('Incorrect MIME type');
        }
    }

    private function _populateTable(PopulatorInterface $populator)
    {
        $this->_messages = array_merge($this->_messages, $populator->populate($this->_file));
    }

    private function _validateLines(LineImportValidatorInterface $usersImportValidator)
    {
        $openedFile = fopen($this->_file, 'r');

        $numLine = 1;
        while (!feof($openedFile)) {
            $line = fgetcsv($openedFile, 0, ';');
            if ($line) {
                $this->_errors = array_merge($this->_errors, $usersImportValidator->validate($line, $numLine));
            }
            ++$numLine;
        }

        fclose($openedFile);
    }

    private function _buildImport()
    {
        $import = new Import();
        $import->setFile($this->_file);
        $import->setFilename($this->_filename);
        $import->setOriginalName($this->_filename);
        $import->setErrors($this->_errors);
        $import->setMessages($this->_messages);

        return $import;
    }
}
