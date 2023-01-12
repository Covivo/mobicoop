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
use App\Payment\Service\PaymentDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

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
    private $_bankTransfertEmitterVerifier;
    private $_paymentProvider;
    private $_logger;
    private $_entityManager;

    /**
     * @var string
     */
    private $_batchId;

    public function __construct(
        BankTransfertRepository $bankTransfertRepository,
        BankTransfertEmitterValidator $bankTransfertEmitterValidator,
        BankTransfertEmitterVerifier $bankTransfertEmitterVerifier,
        PaymentDataProvider $paymentProvider,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->_bankTransfertRepository = $bankTransfertRepository;
        $this->_bankTransfertEmitterValidator = $bankTransfertEmitterValidator;
        $this->_bankTransfertEmitterVerifier = $bankTransfertEmitterVerifier;
        $this->_logger = $logger;
        $this->_paymentProvider = $paymentProvider;
        $this->_entityManager = $entityManager;
    }

    public function emit(string $batchId)
    {
        $this->_batchId = $batchId;
        $this->_bankTransfertEmitterValidator->setBankTransferts($this->_getOnlyInitiatedTransfert());
        $this->_bankTransfertEmitterValidator->validate();
        $this->_emittTransferts();
    }

    private function _getOnlyInitiatedTransfert(): array
    {
        if (!$bankTransfertsToEmit = $this->_bankTransfertRepository->findBy(['batchId' => $this->_batchId, 'status' => BankTransfert::STATUS_INITIATED])) {
            throw new BankTransfertException(BankTransfertException::EMITTER_NO_TRANSFERT_FOR_THIS_BATCH_ID);
        }

        return $bankTransfertsToEmit;
    }

    private function _emittTransferts()
    {
        $this->_logger->info('[BatchId : '.$this->_batchId.'] Starting Bank Transferts');
        foreach ($this->_getOnlyInitiatedTransfert() as $bankTransfert) {
            $recipient = [
                'userId' => [
                    'user' => $bankTransfert->getRecipient(),
                    'amount' => (float) $bankTransfert->getAmount(),
                ],
            ];

            $return = $this->_paymentProvider->processElectronicPayment($this->_bankTransfertEmitterValidator->getHolder(), $recipient);

            $this->_logger->info('[BatchId : '.$this->_batchId.'] Transfering '.$bankTransfert->getAmount().' from User '.$this->_bankTransfertEmitterValidator->getHolder()->getId().' to User '.$bankTransfert->getRecipient()->getId());
            $this->_updateTransfertStatus($bankTransfert, BankTransfert::STATUS_EMITTED);
            $this->_bankTransfertEmitterVerifier->verify($bankTransfert, $return);
        }
        $this->_logger->info('[BatchId : '.$this->_batchId.'] End Bank Transferts');
    }

    private function _updateTransfertStatus(BankTransfert $bankTransfert, int $status)
    {
        $bankTransfert->setStatus($status);
        $this->_entityManager->persist($bankTransfert);
        $this->_entityManager->flush();
    }
}
