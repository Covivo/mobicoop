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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Bank Transfert emitter validator.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertEmitterValidator
{
    private $_bankTransferts;
    private $_totalAmount;
    private $_logger;
    private $_entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->_logger = $logger;
        $this->_entityManager = $entityManager;
    }

    public function setBankTransferts(array $bankTransferts): self
    {
        $this->_bankTransferts = $bankTransferts;

        return $this;
    }

    public function validate()
    {
        if (is_null($this->_bankTransferts)) {
            throw new BankTransfertException(BankTransfertException::EMITTER_VALIDATOR_NO_TRANSFERT);
        }

        $this->_computeTotalAmount();
        if (!$this->_checkFundsAvailability()) {
            $this->_logger->error('[BatchId : '.$this->_bankTransferts[0]->getBatchId().'] Not enough funds');

            throw new BankTransfertException(BankTransfertException::FUNDS_UNAVAILABLE);
        }
        echo $this->_totalAmount;
    }

    private function _computeTotalAmount()
    {
        $this->_totalAmount = 0;
        foreach ($this->_bankTransferts as $bankTransfert) {
            $this->_totalAmount += $bankTransfert->getAmount();
        }
    }

    private function _updateAllTransfertsStatus(int $status)
    {
        foreach ($this->_bankTransferts as $bankTransfert) {
            $bankTransfert->setStatus($status);
            $this->_entityManager->persist($bankTransfert);
        }
        $this->_entityManager->flush();
    }

    private function _checkFundsAvailability(): bool
    {
        $this->_updateAllTransfertsStatus(BankTransfert::STATUS_ABANDONNED_FUNDS_UNAVAILABLE);

        return false;
    }
}
