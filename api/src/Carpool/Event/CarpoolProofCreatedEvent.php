<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Event;

use App\Carpool\Entity\CarpoolProof;
use Symfony\Contracts\EventDispatcher\Event;

class CarpoolProofCreatedEvent extends Event
{
    public const NAME = 'carpool_proof_created';

    protected $_carpoolProof;

    public function __construct(CarpoolProof $carpoolProof)
    {
        $this->_carpoolProof = $carpoolProof;
    }

    public function getCarpoolProof(): CarpoolProof
    {
        return $this->_carpoolProof;
    }
}