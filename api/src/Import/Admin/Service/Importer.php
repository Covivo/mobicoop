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

use App\Import\Admin\Interfaces\DeletorInterface;
use App\Import\Admin\Interfaces\LineImportValidatorInterface;
use App\Import\Admin\Interfaces\PopulatorInterface;
use App\Import\Admin\Resource\Import;
use App\Import\Admin\Service\Deletor\EventImportDeletor;
use App\Import\Admin\Service\LineValidator\EventLineImportValidator;
use App\Import\Admin\Service\LineValidator\RelayPointLineImportValidator;
use App\Import\Admin\Service\LineValidator\UserLineImportValidator;
use App\Import\Admin\Service\Populator\EventImportPopulator;
use App\Import\Admin\Service\Populator\RelayPointImportPopulator;
use App\Import\Admin\Service\Populator\UserImportPopulator;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class Importer
{
    private const MIME_TYPES = [
        'text/plain',
        'text/csv',
    ];

    private const USER_ENTITY = 'User';
    private const RELAY_POINT_ENTITY = 'RelayPoint';
    private const EVENT_ENTITY = 'Event';

    private const TIME_LIMIT = 6 * 60 * 60;

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
    private $_repository;

    /**
     * @var User
     */
    private $_requester;

    public function __construct(File $file, string $filename, object $manager = null, User $requester = null, object $repository = null)
    {
        $this->_file = $file;
        $this->_filename = $filename;
        $this->_manager = $manager;
        $this->_errors = [];
        $this->_messages = [];
        $this->_requester = $requester;
        $this->_repository = $repository;
    }

    public function importUsers(): Import
    {
        set_time_limit(self::TIME_LIMIT);
        if (!$this->_validateFile()) {
            return $this->_buildImport(self::USER_ENTITY);
        }
        $this->_validateLines(new UserLineImportValidator());
        if (0 == count($this->_errors)) {
            $this->_populateTable(new UserImportPopulator($this->_manager, $this->_requester));
        }

        return $this->_buildImport(self::USER_ENTITY);
    }

    public function importRelayPoints(): Import
    {
        set_time_limit(self::TIME_LIMIT);
        if (!$this->_validateFile()) {
            return $this->_buildImport(self::USER_ENTITY);
        }
        $this->_validateLines(new RelayPointLineImportValidator());
        if (0 == count($this->_errors)) {
            $this->_populateTable(new RelayPointImportPopulator($this->_manager, $this->_requester));
        }

        return $this->_buildImport(self::RELAY_POINT_ENTITY);
    }

    public function importEvents(): Import
    {
        set_time_limit(self::TIME_LIMIT);
        if (!$this->_validateFile()) {
            return $this->_buildImport(self::EVENT_ENTITY);
        }
        $this->_validateLines(new EventLineImportValidator());
        if (0 == count($this->_errors)) {
            $this->_populateTable(new EventImportPopulator($this->_manager, $this->_repository));
        }

        return $this->_buildImport(self::EVENT_ENTITY);
    }

    public function deleteEvents()
    {
        set_time_limit(self::TIME_LIMIT);
        if (!$this->_validateFile()) {
            return $this->_buildImport(self::EVENT_ENTITY);
        }
        $this->_validateLines(new EventLineImportValidator());
        if (0 == count($this->_errors)) {
            $this->_deleteTable(new EventImportDeletor($this->_manager, $this->_repository));
        }
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    private function _validateFile(): bool
    {
        if (!in_array($this->_file->getMimeType(), self::MIME_TYPES)) {
            $this->_errors[] = 'Incorrect MIME type';

            return false;
        }

        return true;
    }

    private function _populateTable(PopulatorInterface $populator)
    {
        $messages = $populator->populate($this->_file);
        $this->_messages = array_merge($this->_messages, $messages);
    }

    private function _deleteTable(DeletorInterface $deletor)
    {
        $messages = $deletor->delete($this->_file);
        $this->_messages = array_merge($this->_messages, $messages);
    }

    private function _validateLines(LineImportValidatorInterface $lineImportValidator)
    {
        $openedFile = fopen($this->_file, 'r');

        $numLine = 1;
        while (!feof($openedFile)) {
            $line = fgetcsv($openedFile, 0, ';');
            if ($line) {
                $this->_errors = array_merge($this->_errors, $lineImportValidator->validate($line, $numLine));
            }
            ++$numLine;
        }

        fclose($openedFile);
    }

    private function _buildImport(string $entity)
    {
        $import = new Import();
        $import->setEntity($entity);
        $import->setFile($this->_file);
        $import->setFilename($this->_filename);
        $import->setOriginalName($this->_filename);
        $import->setErrors($this->_errors);
        $import->setMessages($this->_messages);

        return $import;
    }
}
