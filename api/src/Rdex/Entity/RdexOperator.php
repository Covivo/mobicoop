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
 */

namespace App\Rdex\Entity;

/**
 * An RDEX operator.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RdexOperator
{
    /**
     * @var string the name of the operator
     */
    private $name;

    /**
     * @var string the origin of the operator
     */
    private $origin;

    /**
     * @var string the base url of the operator
     */
    private $url;

    /**
     * @var array The routes for a given ad results in different languages
     */
    private $resultRoute;

    public function __construct($name, $origin, $url, $resultRoute)
    {
        $this->setName($name);
        $this->setOrigin($origin);
        $this->setUrl($url);
        $this->setResultRoute($resultRoute);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function setOrigin(string $origin)
    {
        $this->origin = $origin;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getResultRoute()
    {
        return $this->resultRoute;
    }

    public function setResultRoute(array $resultRoute)
    {
        $this->resultRoute = $resultRoute;
    }
}
