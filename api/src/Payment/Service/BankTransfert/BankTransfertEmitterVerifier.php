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
    public const PAYOUT_TYPE = 'PAYOUT';
    public const PAYOUT_STATUS = 'CREATED';
    private $_logger;
    private $_entityManager;

    /**
     * @var BankTransfert
     */
    private $_bankTransfert;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->_logger = $logger;
        $this->_entityManager = $entityManager;
    }

    public function verify(BankTransfert $bankTransfert, array $return)
    {
        $this->_bankTransfert = $bankTransfert;
        if (!$this->_checkWalletToWallet($return[0])) {
            $this->_bankTransfert->setStatus(BankTransfert::STATUS_FAILED_WALLET_TO_WALLET);
            $this->_updateTransfert();

            return;
        }
        if (!$this->_checkPayout($return[1])) {
            $this->_bankTransfert->setStatus(BankTransfert::STATUS_FAILED_PAYOUT);
            $this->_updateTransfert();

            return;
        }
        $this->_bankTransfert->setStatus(BankTransfert::STATUS_EXECUTED);
        $this->_updateTransfert();
    }

    private function _checkWalletToWallet(string $return): bool
    {
        $trace = json_decode($return, true);

        if (isset($trace['Status'], $trace['Type'])
        && self::WALLET_TRANSFERT_STATUS == $trace['Status']
        && self::WALLET_TRANSFERT_TYPE == $trace['Type']) {
            $this->_logger->info($return);

            return true;
        }

        $this->_logger->error($return);
        $this->_bankTransfert->setError($return);

        return false;
    }

    private function _checkPayout(string $return): bool
    {
        $trace = json_decode($return, true);

        if (isset($trace['Status'], $trace['Type'])
        && self::PAYOUT_STATUS == $trace['Status']
        && self::PAYOUT_TYPE == $trace['Type']) {
            $this->_logger->info($return);

            return true;
        }

        $this->_logger->error($return);
        $this->_bankTransfert->setError($return);

        return false;
    }

    private function _updateTransfert()
    {
        $this->_entityManager->persist($this->_bankTransfert);
        $this->_entityManager->flush();
    }
}
