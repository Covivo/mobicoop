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
use App\Payment\Repository\BankTransfertRepository;

/**
 * Bank Transfert emitter.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertEmitter
{
    private $_bankTransferts;
    private $_bankTransfertRepository;
    private $_bankTransfertEmitterValidator;

    public function __construct(BankTransfertRepository $bankTransfertRepository, BankTransfertEmitterValidator $bankTransfertEmitterValidator)
    {
        $this->_bankTransfertRepository = $bankTransfertRepository;
        $this->_bankTransfertEmitterValidator = $bankTransfertEmitterValidator;
    }

    public function emit(int $batchId)
    {
        if (!$this->_bankTransferts = $this->_bankTransfertRepository->findBy(['batchId' => $batchId])) {
            throw new BankTransfertException(BankTransfertException::EMITTER_NO_TRANSFERT_FOR_THIS_BATCH_ID);
        }

        $this->_bankTransfertEmitterValidator->setBankTransferts($this->_getOnlyInitiatedTransfert());
        $this->_bankTransfertEmitterValidator->validate();
    }

    private function _getOnlyInitiatedTransfert(): array
    {
        $bankTransfertsToEmit = [];
        foreach ($this->_bankTransferts as $bankTransfert) {
            if (BankTransfert::STATUS_INITIATED == $bankTransfert->getStatus()) {
                $bankTransfertsToEmit[] = $bankTransfert;
            }
        }

        return $bankTransfertsToEmit;
    }
}
