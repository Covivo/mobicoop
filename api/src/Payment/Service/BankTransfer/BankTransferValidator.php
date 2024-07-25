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

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Geography\Entity\Territory;
use App\Geography\Service\TerritoryManager;
use App\Payment\Entity\BankTransfer;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Psr\Log\LoggerInterface;

/**
 * Bank Transfert Builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransferValidator
{
    public const HEADERS_USER_ID = ['id_usager', 'userId', 'user_id'];
    public const HEADERS_AMOUNT = ['amount', 'montant'];
    public const HEADERS_TERRITORY_ID = ['territoryId', 'id_territoire', 'territoryId(opt)', 'territory_id'];
    public const HEADERS_CARPOOL_PROOF_ID = ['carpoolProofId', 'carpool_proof_id', 'carpoolProofId(opt)'];

    public const HEADERS = [
        self::HEADERS_USER_ID,
        self::HEADERS_AMOUNT,
        self::HEADERS_TERRITORY_ID,
        self::HEADERS_CARPOOL_PROOF_ID,
    ];

    /**
     * @var array
     */
    private $_data;

    /**
     * @var ?float
     */
    private $_amount;

    /**
     * @var ?User
     */
    private $_user;

    /**
     * @var ?Territory
     */
    private $_territory;

    /**
     * @var ?CarpoolProof
     */
    private $_carpoolProof;

    /**
     * @var ?string
     */
    private $_optionalColumns;

    /**
     * @var int
     */
    private $_status;

    /**
     * @var string
     */
    private $_batchId;

    private $_userManager;
    private $_territoryManager;
    private $_carpoolProofRepository;
    private $_logger;

    public function __construct(UserManager $userManager, TerritoryManager $territoryManager, CarpoolProofRepository $carpoolProofRepository, LoggerInterface $logger)
    {
        $this->_userManager = $userManager;
        $this->_territoryManager = $territoryManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_logger = $logger;
        $this->_init();
    }

    public function setData(array $data): self
    {
        $this->_data = $data;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->_user;
    }

    public function getAmount(): ?float
    {
        return $this->_amount;
    }

    public function getTerritory(): ?Territory
    {
        return $this->_territory;
    }

    public function getCarpoolProof(): ?CarpoolProof
    {
        return $this->_carpoolProof;
    }

    public function getOptionalColumns(): ?string
    {
        return $this->_optionalColumns;
    }

    public function getStatus(): ?int
    {
        return $this->_status;
    }

    public function valid(string $batchId)
    {
        $this->_init();
        $this->_batchId = $batchId;
        $this->_checkAmount();
        $this->_checkRecipient();
        $this->_checkTerritory();
        $this->_checkCarpoolProof();
        $this->_checkOptionalColumns();
    }

    private function _init()
    {
        $this->_user = null;
        $this->_territory = null;
        $this->_carpoolProof = null;
        $this->_amount = null;
        $this->_optionalColumns = null;
        $this->_status = BankTransfer::STATUS_INITIATED;
    }

    private function _findValue(array $headers)
    {
        $value = null;
        foreach ($headers as $header) {
            if (isset($this->_data[$header])) {
                return $this->_data[$header];
            }
        }

        return $value;
    }

    private function _checkAmount()
    {
        $value = $this->_findValue(self::HEADERS_AMOUNT);
        if (is_null($value) || '' == trim($value)) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] Empty Amount : '.$value);
            $this->_status = BankTransfer::STATUS_NO_AMOUNT;
        }

        $amount = str_replace(',', '.', $value);
        if (!is_numeric($amount)) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] Amount must be numeric : '.$value);
            $this->_status = BankTransfer::STATUS_NO_AMOUNT;
        }

        if (0 == (float) $amount) {
            $this->_status = BankTransfer::STATUS_AMOUNT_AT_ZERO;
        }

        $this->_amount = $amount;
    }

    private function _checkRecipient()
    {
        $value = $this->_findValue(self::HEADERS_USER_ID);

        $this->_user = null;

        if (!is_numeric($value)) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] User Id must be an int : '.$value);
            $this->_status = BankTransfer::STATUS_UNKNOWN_RECIPIENT;

            return;
        }
        if (!$user = $this->_userManager->getUser($value)) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] Unknown Recipient : '.$value);
            $this->_status = BankTransfer::STATUS_UNKNOWN_RECIPIENT;

            return;
        }
        $this->_user = $user;
    }

    private function _checkTerritory()
    {
        $value = $this->_findValue(self::HEADERS_TERRITORY_ID);

        if (is_null($value) || '' === trim($value)) {
            return null;
        }

        if (!$territory = $this->_territoryManager->getTerritory($value)) {
            $this->_status = BankTransfer::STATUS_UNKNOWN_TERRITORY;
            $this->_logger->error('[BatchId : '.$this->_batchId.'] Unknown Territory : '.$value);
        }

        $this->_territory = $territory;
    }

    private function _checkCarpoolProof()
    {
        $value = $this->_findValue(self::HEADERS_CARPOOL_PROOF_ID);

        if (is_null($value) || '' === trim($value)) {
            return null;
        }

        $dataCarpoolProof = explode('_', $value);

        if (!isset($dataCarpoolProof[1]) || !is_numeric($dataCarpoolProof[1])) {
            $this->_status = BankTransfer::STATUS_INVALID_CARPOOL_PROOF;
            $this->_logger->error('[BatchId : '.$this->_batchId.'] CarpoolProof Invalid: '.$value);
        } else {
            $carpoolProofId = $dataCarpoolProof[1];
            if (!$carpoolProof = $this->_carpoolProofRepository->find($carpoolProofId)) {
                $this->_status = BankTransfer::STATUS_UNKNOWN_CARPOOL_PROOF;
                $this->_logger->error('[BatchId : '.$this->_batchId.'] Unknown CarpoolProof : '.$carpoolProofId);

                return null;
            }

            if ($carpoolProof->getDriver()->getId() !== $this->_user->getId() && $carpoolProof->getPassenger()->getId() !== $this->_user->getId()) {
                $this->_status = BankTransfer::STATUS_USER_NOT_INVOLVE_CARPOOL_PROOF;
                $this->_logger->error('[BatchId : '.$this->_batchId.'] User '.$this->_user->getId().' not involve in CarpoolProof : '.$carpoolProof->getId());
            }

            $this->_carpoolProof = $carpoolProof;
        }
    }

    private function _inSubArray(string $needle, array $haystack): bool
    {
        foreach ($haystack as $subArray) {
            if (in_array($needle, $subArray)) {
                return true;
            }
        }

        return false;
    }

    private function _checkOptionalColumns()
    {
        $options = [];

        foreach ($this->_data as $key => $data) {
            if (!$this->_inSubArray($key, self::HEADERS)) {
                $options[] = $data;
            }
        }

        $this->_optionalColumns = (0 != count($options)) ? json_encode($options, JSON_UNESCAPED_UNICODE) : null;
    }
}
