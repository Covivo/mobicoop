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
use App\Payment\Entity\BankTransfert;
use App\Payment\Exception\BankTransfertException;
use App\User\Entity\User;
use App\User\Service\UserManager;

/**
 * Bank Transfert Builder.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertBuilder
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
     * @var array
     */
    private $_bankTransferts;

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

    private $_userManager;
    private $_territoryManager;
    private $_carpoolProofRepository;
    private $_valid;

    public function __construct(UserManager $userManager, TerritoryManager $territoryManager, CarpoolProofRepository $carpoolProofRepository)
    {
        $this->_userManager = $userManager;
        $this->_territoryManager = $territoryManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_valid = true;
    }

    public function setData(array $data): self
    {
        $this->_data = $data;

        return $this;
    }

    public function build(): ?BankTransfert
    {
        if (is_null($this->_data)) {
            throw new BankTransfertException(BankTransfertException::BT_BUILDER_NO_DATA);
        }

        $this->_user = $this->_checkRecipient();
        $this->_territory = $this->_checkTerritory();
        $this->_carpoolProof = $this->_checkCarpoolProof();

        return $this->_build();
    }

    private function _build(): ?BankTransfert
    {
        if ($this->_valid) {
            $bankTransfert = new BankTransfert();
            $bankTransfert->setAmount(str_replace(',', '.', $this->_data[self::COL_AMOUNT]));
            $bankTransfert->setRecipient($this->_user);
            $bankTransfert->setTerritory($this->_territory);
            $bankTransfert->setCarpoolProof($this->_carpoolProof);

            return $bankTransfert;
        }

        return null;
    }

    private function _checkRecipient(): ?User
    {
        if (!$user = $this->_userManager->getUser($this->_data[self::COL_USER_ID])) {
            echo 'Unknown Recipient : '.$this->_data[self::COL_USER_ID].PHP_EOL;
            $this->_valid = false;
        }

        return $user;
    }

    private function _checkTerritory(): ?Territory
    {
        if (is_null($this->_data[self::COL_TERRITORY_ID]) || '' === trim($this->_data[self::COL_TERRITORY_ID])) {
            return null;
        }

        if (!$territory = $this->_territoryManager->getTerritory($this->_data[self::COL_TERRITORY_ID])) {
            $this->_valid = false;
            echo 'Unknown Territory : '.$this->_data[self::COL_TERRITORY_ID].PHP_EOL;
        }

        return $territory;
    }

    private function _checkCarpoolProof(): ?CarpoolProof
    {
        if (is_null($this->_data[self::COL_CARPOOL_PROOF_ID]) || '' === trim($this->_data[self::COL_CARPOOL_PROOF_ID])) {
            return null;
        }

        if (!$carpoolProof = $this->_carpoolProofRepository->find($this->_data[self::COL_CARPOOL_PROOF_ID])) {
            $this->_valid = false;
            echo 'Unknown CarpoolProof : '.$this->_data[self::COL_CARPOOL_PROOF_ID].PHP_EOL;
        }

        return $carpoolProof;
    }
}
