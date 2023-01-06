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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Bank Transfert emitter verifier.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertEmitterVerifier
{
    public const WALLET_TRANSFERT_TYPE = 'TRANSFER';
    public const WALLET_TRANSFERT_STATUS = 'SUCCEEDED';
    private $_logger;
    private $_entityManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->_logger = $logger;
        $this->_entityManager = $entityManager;
    }

    public function verify(BankTransfert $bankTransfert, array $return)
    {
        if (!$this->_checkWalletToWallet($return[0])) {
            $bankTransfert->setStatus(BankTransfert::STATUS_FAILED_WALLET_TO_WALLET);
            $this->_updateTransfert($bankTransfert);

            return;
        }
        if (!$this->_checkPayout($return[1])) {
            $bankTransfert->setStatus(BankTransfert::STATUS_FAILED_PAYOUT);
            $this->_updateTransfert($bankTransfert);

            return;
        }
        $bankTransfert->setStatus(BankTransfert::STATUS_EXECUTED);
        $this->_updateTransfert($bankTransfert);
    }

    private function _checkWalletToWallet(string $return): bool
    {
        return false;
    }

    private function _checkPayout(string $return): bool
    {
        return false;
    }

    private function _updateTransfert(BankTransfert $bankTransfert)
    {
        $this->_entityManager->persist($bankTransfert);
        $this->_entityManager->flush();
    }
}
