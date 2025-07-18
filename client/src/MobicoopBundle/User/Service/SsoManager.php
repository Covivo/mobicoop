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

namespace Mobicoop\Bundle\MobicoopBundle\User\Service;

/**
 * Sso management service.
 */
class SsoManager
{
    private const ALLOWED_PROVIDERS = ['GLConnect', 'mobConnect', 'mobConnectAuth', 'mobConnectBasic', 'PassMobilite', 'mobigo', 'Mobicoop'];
    private $carpoolTimezone;

    public function __construct(string $carpoolTimezone)
    {
        $this->carpoolTimezone = $carpoolTimezone;
    }

    /**
     * Guess and return the parameters for a SSO connection.
     *
     * @return array
     */
    public function guessSsoParameters(array $params)
    {
        $return = [];

        if (isset($params['state']) && in_array($params['state'], self::ALLOWED_PROVIDERS)) {
            if (isset($params['access_token'])) {
                $return = [
                    'ssoId' => $params['access_token'],
                    'ssoProvider' => $params['state'],
                ];
            } else {
                $return = [
                    'ssoId' => $params['code'],
                    'ssoProvider' => $params['state'],
                ];
            }
        }

        return $return;
    }
}
