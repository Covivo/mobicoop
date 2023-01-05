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

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Geography\Entity\Territory;
use App\Geography\Service\TerritoryManager;
use App\User\Entity\User;
use App\User\Service\UserManager;

/**
 * Bank Transfert Builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertValidator
{
    public const COL_USER_ID = 0;
    public const COL_AMOUNT = 1;
    public const COL_TERRITORY_ID = 2;
    public const COL_CARPOOL_PROOF_ID = 3;
    public const COL_DETAILS_MIN = 4;

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
     * @var bool
     */
    private $_valid;

    private $_userManager;
    private $_territoryManager;
    private $_carpoolProofRepository;

    public function __construct(UserManager $userManager, TerritoryManager $territoryManager, CarpoolProofRepository $carpoolProofRepository)
    {
        $this->_userManager = $userManager;
        $this->_territoryManager = $territoryManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
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

    public function getValid(): ?bool
    {
        return $this->_valid;
    }

    public function valid()
    {
        $this->_init();
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
        $this->_valid = true;
    }

    private function _checkAmount()
    {
        if (is_null($this->_data[self::COL_AMOUNT]) || '' == trim($this->_data[self::COL_AMOUNT])) {
            echo 'Empty Amount : '.$this->_data[self::COL_AMOUNT].PHP_EOL;
            $this->_valid = false;
        }

        $this->_amount = str_replace(',', '.', $this->_data[self::COL_AMOUNT]);
    }

    private function _checkRecipient()
    {
        if (!$user = $this->_userManager->getUser($this->_data[self::COL_USER_ID])) {
            echo 'Unknown Recipient : '.$this->_data[self::COL_USER_ID].PHP_EOL;
            $this->_valid = false;
        }

        $this->_user = $user;
    }

    private function _checkTerritory()
    {
        if (is_null($this->_data[self::COL_TERRITORY_ID]) || '' === trim($this->_data[self::COL_TERRITORY_ID])) {
            return null;
        }

        if (!$territory = $this->_territoryManager->getTerritory($this->_data[self::COL_TERRITORY_ID])) {
            $this->_valid = false;
            echo 'Unknown Territory : '.$this->_data[self::COL_TERRITORY_ID].PHP_EOL;
        }

        $this->_territory = $territory;
    }

    private function _checkCarpoolProof()
    {
        if (is_null($this->_data[self::COL_CARPOOL_PROOF_ID]) || '' === trim($this->_data[self::COL_CARPOOL_PROOF_ID])) {
            return null;
        }

        $dataCarpoolProof = explode('_', $this->_data[self::COL_CARPOOL_PROOF_ID]);

        if (!isset($dataCarpoolProof[1]) || !is_numeric($dataCarpoolProof[1])) {
            $this->_valid = false;
            echo 'CarpoolProof Invalid: '.$this->_data[self::COL_CARPOOL_PROOF_ID].PHP_EOL;
        } else {
            $carpoolProofId = $dataCarpoolProof[1];
            if (!$carpoolProof = $this->_carpoolProofRepository->find($carpoolProofId)) {
                $this->_valid = false;
                echo 'Unknown CarpoolProof : '.$carpoolProofId.PHP_EOL;

                return null;
            }

            if ($carpoolProof->getDriver()->getId() !== $this->_user->getId() && $carpoolProof->getPassenger()->getId() !== $this->_user->getId()) {
                $this->_valid = false;
                echo 'User '.$this->_user->getId().' not involve in CarpoolProof : '.$carpoolProof->getId().PHP_EOL;
            }

            $this->_carpoolProof = $carpoolProof;
        }
    }

    private function _checkOptionalColumns()
    {
        $options = [];
        for ($i = self::COL_DETAILS_MIN; $i < count($this->_data); ++$i) {
            $options[] = $this->_data[$i];
        }

        $this->_optionalColumns = implode('|', $options);
    }
}
