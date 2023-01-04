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

namespace App\Payment\Service;

use App\Payment\Exception\BankTransfertException;

/**
 * Bank Transfert Manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertManager
{
    public const PATH_TO_FILES = __DIR__.'/../../../public/upload/bankTransferts';
    public const FILES_EXTENTION = 'csv';

    private $_file;

    private function __openFile(string $file)
    {
        try {
            $this->_file = fopen($file, 'r');
        } catch (\Exception $e) {
            throw new BankTransfertException(BankTransfertException::ERROR_OPENING_FILE.' '.$file);
        }
    }

    private function __checkSeparator(): bool
    {
        $firstLine = fgets($this->_file);
        fclose($this->_file);
        if (false !== strpos($firstLine, ';')) {
            return true;
        }

        throw new BankTransfertException(BankTransfertException::BAD_SEPARATOR);
    }

    public function makeBankTransferts(): bool
    {
        $files = glob(self::PATH_TO_FILES.'/*.'.self::FILES_EXTENTION);
        foreach ($files as $file) {
            echo 'Opening : '.$file.PHP_EOL;
            $this->__openFile($file);
            $this->__checkSeparator();
            echo '-> Valid'.PHP_EOL;
        }

        return true;
    }
}
