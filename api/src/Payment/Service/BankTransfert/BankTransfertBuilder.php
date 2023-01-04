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
    /**
     * @var array
     */
    private $_data;

    private $_bankTransfertValidator;

    public function __construct(BankTransfertValidator $bankTransfertValidator)
    {
        $this->_bankTransfertValidator = $bankTransfertValidator;
    }

    public function setData(array $data): self
    {
        $this->_data = $data;

        return $this;
    }

    public function build(): ?BankTransfert
    {
        if (is_null($this->_data)) {
            throw new BankTransfertException(BankTransfertException::BT_BUILDER_NO_DATA);
        }

        return $this->_build();
    }

    private function _build(): ?BankTransfert
    {
        $this->_bankTransfertValidator->setData($this->_data);
        $this->_bankTransfertValidator->valid();

        $bankTransfert = new BankTransfert();
        $bankTransfert->setAmount($this->_bankTransfertValidator->getAmount());
        $bankTransfert->setRecipient($this->_bankTransfertValidator->getRecipient());
        $bankTransfert->setTerritory($this->_bankTransfertValidator->getTerritory());
        $bankTransfert->setCarpoolProof($this->_bankTransfertValidator->getCarpoolProof());
        $bankTransfert->setDetails($this->_bankTransfertValidator->getOptionalColumns());
        $bankTransfert->setStatus($this->_bankTransfertValidator->getValid() ? BankTransfert::STATUS_INITIATED : BankTransfert::STATUS_INVALID);

        return $bankTransfert;
    }
}
