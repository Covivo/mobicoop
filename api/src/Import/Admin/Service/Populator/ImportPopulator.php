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

namespace App\Import\Admin\Service\Populator;

use App\Import\Admin\Interfaces\PopulatorInterface;
use App\User\Admin\Service\UserManager;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
abstract class ImportPopulator implements PopulatorInterface
{
    private $_userManager;
    private $_messages;

    public function __construct(UserManager $userManager)
    {
        $this->_userManager = $userManager;
        $this->_messages = [];
    }

    public function populate(File $file, bool $columnHeadersFirstLine = false): array
    {
        $openedFile = fopen($file, 'r');

        $isFirstLine = true;
        while (!feof($openedFile)) {
            $line = fgetcsv($openedFile, 0, ';');

            if ($columnHeadersFirstLine && $isFirstLine) {
                $isFirstLine = false;

                continue;
            }

            if ($line) {
                $this->_addEntity($line);
            }
        }

        fclose($openedFile);

        return $this->getMessages();
    }

    abstract public function getEntity(): string;

    abstract public function getMessages(): array;

    abstract public function addMessage(string $message): array;

    abstract protected function _addEntity(array $line);
}
