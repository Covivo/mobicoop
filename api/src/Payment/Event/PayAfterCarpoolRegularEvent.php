<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\Payment\Event;

use App\Payment\Entity\CarpoolItem;
use Symfony\Contracts\EventDispatcher\Event;

class PayAfterCarpoolRegularEvent extends Event
{
    public const NAME = 'pay_after_carpool_regular';

    /**
     * @var CarpoolItem
     */
    private $carpoolItem;


    public function __construct(CarpoolItem $carpoolItem)
    {
        $this->carpoolItem = $carpoolItem;
    }

    /**
     * @return CarpoolItem
     */
    public function getCarpoolItem(): CarpoolItem
    {
        return $this->carpoolItem;
    }
}
