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
use App\Payment\Entity\Wallet;
use App\Payment\Exception\BankTransferException;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Service\PaymentDataProvider;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Bank Transfert emitter validator.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransferEmitterValidator
{
    private $_BankTransfers;
    private $_totalAmount;
    private $_logger;
    private $_entityManager;
    private $_paymentProvider;
    private $_paymentActive;
    private $_holderId;
    private $_holder;
    private $_paymentProfileRepository;
    private $_userManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        PaymentDataProvider $paymentProvider,
        PaymentProfileRepository $paymentProfileRepository,
        UserManager $userManager,
        string $paymentActive,
        string $holderId
    ) {
        $this->_logger = $logger;
        $this->_entityManager = $entityManager;
        $this->_paymentProvider = $paymentProvider;
        $this->_paymentActive = $paymentActive;
        $this->_holderId = $holderId;
        $this->_paymentProfileRepository = $paymentProfileRepository;
        $this->_userManager = $userManager;
    }

    public function setBankTransfers(array $BankTransfers): self
    {
        $this->_BankTransfers = $BankTransfers;

        return $this;
    }

    public function getHolder(): User
    {
        return $this->_holder;
    }

    public function validate()
    {
        if (is_null($this->_BankTransfers)) {
            throw new BankTransferException(BankTransferException::EMITTER_VALIDATOR_NO_TRANSFERT);
        }

        $this->_getHolder();
        $this->_checkPaymentProvider();
        $this->_computeTotalAmount();
        $this->_checkFundsAvailability();
        $this->_checkRecipientsWallets();
    }

    public function _checkRecipientsWallets()
    {
        $recipientsIds = [];
        foreach ($this->_BankTransfers as $BankTransfer) {
            if (!in_array($BankTransfer->getRecipient()->getId(), $recipientsIds)) {
                $recipientsIds[] = $BankTransfer->getRecipient()->getId();
                $wallet = $this->_getUserWallet($BankTransfer->getRecipient());
                if (is_null($wallet)) {
                    $this->_updateTransfertStatus($BankTransfer, BankTransfer::STATUS_ABANDONNED_NO_RECIPIENT_WALLET);
                    $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] No recipient Wallet for User '.$BankTransfer->getRecipient()->getId());

                    continue;
                }
            }
        }
    }

    private function _updateTransfertStatus(BankTransfer $BankTransfer, int $status)
    {
        $BankTransfer->setStatus($status);
        $this->_entityManager->persist($BankTransfer);
        $this->_entityManager->flush();
    }

    private function _getHolder()
    {
        if (is_null($this->_holderId) || !is_numeric($this->_holderId)) {
            $this->_updateAllTransfertsStatus(BankTransfer::STATUS_ABANDONNED_NO_HOLDER_ID);
            $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] No Holder id');

            throw new BankTransferException(BankTransferException::NO_HOLDER_ID);
        }

        if (!$holderPaymenProfile = $this->_paymentProfileRepository->findOneBy(['identifier' => $this->_holderId])) {
            $this->_updateAllTransfertsStatus(BankTransfer::STATUS_ABANDONNED_NO_HOLDER_FOUND);
            $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] No Holder found');

            throw new BankTransferException(BankTransferException::NO_HOLDER_FOUND);
        }

        $this->_holder = $holderPaymenProfile->getUser();
    }

    private function _computeTotalAmount()
    {
        $this->_totalAmount = 0;
        foreach ($this->_BankTransfers as $BankTransfer) {
            $this->_totalAmount += $BankTransfer->getAmount();
        }
    }

    private function _updateAllTransfertsStatus(int $status)
    {
        foreach ($this->_BankTransfers as $BankTransfer) {
            $BankTransfer->setStatus($status);
            $this->_entityManager->persist($BankTransfer);
        }
        $this->_entityManager->flush();
    }

    private function _checkPaymentProvider(): bool
    {
        if (!$this->_paymentActive) {
            $this->_updateAllTransfertsStatus(BankTransfer::STATUS_ABANDONNED_NO_PAYMENT_PROVIDER);
            $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] No payment provider');

            throw new BankTransferException(BankTransferException::NO_PAYMENT_PROVIDER);
        }

        return false;
    }

    private function _getUserWallet(User $user): ?Wallet
    {
        $user = $this->_userManager->getPaymentProfile($user);

        if (!$wallets = $user->getWallets()) {
            return null;
        }

        return $wallets[0];
    }

    private function _checkFundsAvailability()
    {
        // get the wallet of the holder user
        $wallet = $this->_getUserWallet($this->_holder);
        if (is_null($wallet)) {
            $this->_updateAllTransfertsStatus(BankTransfer::STATUS_ABANDONNED_NO_HOLDER_WALLET);
            $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] No holder wallet');

            throw new BankTransferException(BankTransferException::NO_HOLDER_WALLET);
        }

        // check if enough found for $this->_totalAmount;
        if ($wallet->getBalance()->getAmount() / 100 < $this->_totalAmount) {
            $this->_updateAllTransfertsStatus(BankTransfer::STATUS_ABANDONNED_FUNDS_UNAVAILABLE);
            $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] Not enough funds');
            $this->_logger->error('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] '.$this->_totalAmount.' needed '.$wallet->getBalance()->getAmount().' available.');

            throw new BankTransferException(BankTransferException::FUNDS_UNAVAILABLE);
        }
        $this->_logger->info('[BatchId : '.$this->_BankTransfers[0]->getBatchId().'] Funds available : '.$this->_totalAmount.' needed '.($wallet->getBalance()->getAmount() / 100).' available.');
    }
}
