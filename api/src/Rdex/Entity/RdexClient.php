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

namespace App\Rdex\Entity;

/**
 * An RDEX client.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RdexClient
{
    /**
     * @var string The name of the client.
     */
    private $name;

    /**
     * @var string The public key of the client.
     */
    private $publicKey;

    /**
     * @var string The private key of the client.
     */
    private $privateKey;

    public function __construct($name, $publicKey, $privateKey)
    {
        $this->setName($name);
        $this->setPublicKey($publicKey);
        $this->setPrivateKey($privateKey);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function setPrivateKey(string $privateKey)
    {
        $this->privateKey = $privateKey;
    }
}
