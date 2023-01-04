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

use App\Payment\Entity\BankTransfert;
use App\Payment\Exception\BankTransfertException;

/**
 * Bank Transfert Builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertBuilder
{
    public const COL_USER_ID = 0;
    public const COL_AMOUNT = 1;
    public const COL_TERRITORY_ID = 2;
    public const COL_CARPOOL_PROOF_ID = 3;
    public const COL_DETAILS_MIN = 4;

    /**
     * @var array
     */
    private $_data;

    /**
     * @var array
     */
    private $_bankTransferts;

    public function setData(array $data): self
    {
        $this->_data = $data;

        return $this;
    }

    public function build(): BankTransfert
    {
        if (is_null($this->_data)) {
            throw new BankTransfertException(BankTransfertException::BT_BUILDER_NO_DATA);
        }

        $bankTransfert = new BankTransfert();
        $bankTransfert->setAmount(str_replace(',', '.', $this->_data[self::COL_AMOUNT]));

        return $bankTransfert;
    }
}
