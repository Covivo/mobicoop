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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Login Entity
 *
 * @author Sylvain Briat <Sylvain.Briat@covivo.eu>
 */
class Login
{
    private $username;
    private $password;

    /**
     * @Assert\NotBlank()
     * @return mixed
     */
    public function getUsername ()
    {
        return $this->username;
    }

    /**
     * @Assert\NotBlank()
     * @return mixed
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     * @param mixed $username
     */
    public function setUsername ($username)
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword ($password)
    {
        $this->password = $password;
    }

}
