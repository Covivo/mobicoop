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

namespace App\Payment\Service\BankTransfer;

use App\Payment\Entity\BankTransfer;
use App\Payment\Exception\BankTransferException;

/**
 * Bank Transfert Builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransferBuilder
{
    /**
     * @var array
     */
    private $_data;

    /**
     * @var string
     */
    private $_batchId;

    private $_bankTransferValidator;

    public function __construct(BankTransferValidator $bankTransferValidator)
    {
        $this->_bankTransferValidator = $bankTransferValidator;
    }

    public function setData(array $data): self
    {
        $this->_data = $data;

        return $this;
    }

    public function build(string $batchId): ?BankTransfer
    {
        $this->_batchId = $batchId;

        if (is_null($this->_data)) {
            throw new BankTransferException(BankTransferException::BT_BUILDER_NO_DATA);
        }

        return $this->_build();
    }

    private function _build(): ?BankTransfer
    {
        $this->_bankTransferValidator->setData($this->_data);
        $this->_bankTransferValidator->valid($this->_batchId);

        $bankTransfer = new BankTransfer();
        $bankTransfer->setAmount($this->_bankTransferValidator->getAmount());
        $bankTransfer->setRecipient($this->_bankTransferValidator->getRecipient());
        $bankTransfer->setTerritory($this->_bankTransferValidator->getTerritory());
        $bankTransfer->setCarpoolProof($this->_bankTransferValidator->getCarpoolProof());
        $bankTransfer->setDetails($this->_bankTransferValidator->getOptionalColumns());
        $bankTransfer->setStatus($this->_bankTransferValidator->getStatus());
        $bankTransfer->setBatchId($this->_batchId);

        return $bankTransfer;
    }
}
