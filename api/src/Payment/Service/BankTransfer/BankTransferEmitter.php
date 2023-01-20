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
use App\Payment\Repository\BankTransferRepository;
use App\Payment\Service\PaymentDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Bank Transfert emitter.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransferEmitter
{
    private $_BankTransfers;
    private $_BankTransferRepository;
    private $_BankTransferEmitterValidator;
    private $_BankTransferEmitterVerifier;
    private $_paymentProvider;
    private $_logger;
    private $_entityManager;

    /**
     * @var string
     */
    private $_batchId;

    public function __construct(
        BankTransferRepository $BankTransferRepository,
        BankTransferEmitterValidator $BankTransferEmitterValidator,
        BankTransferEmitterVerifier $BankTransferEmitterVerifier,
        PaymentDataProvider $paymentProvider,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->_BankTransferRepository = $BankTransferRepository;
        $this->_BankTransferEmitterValidator = $BankTransferEmitterValidator;
        $this->_BankTransferEmitterVerifier = $BankTransferEmitterVerifier;
        $this->_logger = $logger;
        $this->_paymentProvider = $paymentProvider;
        $this->_entityManager = $entityManager;
    }

    public function emit(string $batchId)
    {
        $this->_batchId = $batchId;
        $this->_BankTransferEmitterValidator->setBankTransfers($this->_getOnlyInitiatedTransfert());
        $this->_BankTransferEmitterValidator->validate();
        $this->_emittTransferts();
    }

    private function _getOnlyInitiatedTransfert(): array
    {
        if (!$BankTransfersToEmit = $this->_BankTransferRepository->findBy(['batchId' => $this->_batchId, 'status' => BankTransfer::STATUS_INITIATED])) {
            throw new BankTransferException(BankTransferException::EMITTER_NO_TRANSFERT_FOR_THIS_BATCH_ID);
        }

        return $BankTransfersToEmit;
    }

    private function _emittTransferts()
    {
        $this->_logger->info('[BatchId : '.$this->_batchId.'] Starting Bank Transferts');
        foreach ($this->_getOnlyInitiatedTransfert() as $BankTransfer) {
            $recipient = [
                'userId' => [
                    'user' => $BankTransfer->getRecipient(),
                    'amount' => (float) $BankTransfer->getAmount(),
                ],
            ];

            $return = $this->_paymentProvider->processElectronicPayment($this->_BankTransferEmitterValidator->getHolder(), $recipient);

            $this->_logger->info('[BatchId : '.$this->_batchId.'] Transfering '.$BankTransfer->getAmount().' from User '.$this->_BankTransferEmitterValidator->getHolder()->getId().' to User '.$BankTransfer->getRecipient()->getId());
            $this->_updateTransfertStatus($BankTransfer, BankTransfer::STATUS_EMITTED);
            $this->_BankTransferEmitterVerifier->verify($BankTransfer, $return);
        }
        $this->_logger->info('[BatchId : '.$this->_batchId.'] End Bank Transferts');
    }

    private function _updateTransfertStatus(BankTransfer $BankTransfer, int $status)
    {
        $BankTransfer->setStatus($status);
        $this->_entityManager->persist($BankTransfer);
        $this->_entityManager->flush();
    }
}
