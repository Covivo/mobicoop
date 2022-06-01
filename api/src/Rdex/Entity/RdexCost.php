<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Rdex\Entity;

/**
 * An RDEX Cost.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexCost implements \JsonSerializable
{
    /**
     * @var float The fixed cost.
     */
    private $fixed;

    /**
     * @var float The variable cost.
     */
    private $variable;

    /**
     * @return number
     */
    public function getFixed(): number
    {
        return $this->fixed;
    }

    /**
     * @return number
     */
    public function getVariable(): number
    {
        return $this->variable;
    }

    /**
     * @param number $fixed
     */
    public function setFixed($fixed)
    {
        $this->fixed = $fixed;
    }

    /**
     * @param number $variable
     */
    public function setVariable($variable)
    {
        $this->variable = $variable;
    }

    public function jsonSerialize(): mixed
    {
        return
        [
            'fixed'     => $this->getFixed(),
            'variable'  => $this->getVariable()
        ];
    }
}
