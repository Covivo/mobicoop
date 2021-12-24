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

namespace Mobicoop\Bundle\MobicoopBundle\User\Service;

use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Sso management service.
 */
class SsoManager
{
    private $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Guess and return the parameters for a SSO connection
     *
     * @param array $params
     * @return array
     */
    public function guessSsoParameters(array $params)
    {
        $return = [];
        if (isset($params['state'])) {
            switch ($params['state']) {
                case "GLConnect":
                case "PassMobilite":
                    $return = ['ssoId'=>$params['code'], 'ssoProvider'=>$params['state']];
                break;
            }
        }

        return $return;
    }

    public function logOut()
    {
        // call logout route api
    }
}
