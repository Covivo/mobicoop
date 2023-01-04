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

/**
 * Bank Transfert Builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertCollectionBuilder
{
    public const CSV_DELIMITER = ';';

    /**
     * @var string
     */
    private $_filepath;

    /**
     * @var array
     */
    private $_bankTransferts;

    /**
     * @var BankTransfertBuilder
     */
    private $_bankTransfertBuilder;

    public function __construct(BankTransfertBuilder $bankTransfertBuilder)
    {
        $this->_bankTransferts = [];
        $this->_bankTransfertBuilder = $bankTransfertBuilder;
    }

    public function setFilePath(string $filepath): self
    {
        $this->_filepath = $filepath;

        return $this;
    }

    public function getBankTransferts(): array
    {
        return $this->_bankTransferts;
    }

    public function build()
    {
        try {
            $file = fopen($this->_filepath, 'r');
        } catch (\Exception $e) {
            throw new BankTransfertException(BankTransfertException::ERROR_OPENING_FILE.' '.$this->_filepath);
        }

        while (!feof($file)) {
            $line = fgetcsv($file, 0, self::CSV_DELIMITER);
            if ($line) {
                $this->_bankTransfertBuilder->setData($line);
                if (!is_null($bankTransfert = $this->_bankTransfertBuilder->build())) {
                    $this->_bankTransferts[] = $bankTransfert;
                }
            }
        }

        fclose($file);
    }
}
