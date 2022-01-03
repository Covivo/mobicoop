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

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Sso management service.
 */
class SsoManager extends AbstractController
{
    private $dataProvider;
    private $user;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

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
        return new RedirectResponse('http://www.google.com');
        if (!is_null($this->user->getSsoProvider()) && $this->user->getSsoProvider() !== "") {
            $this->dataProvider->setClass(User::class);
            $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
            $response = $this->dataProvider->getSpecialCollection("logout_sso");
            if ($response->getCode()==200) {
                foreach ($response->getValue() as $logoutUrls) {
                    foreach ($logoutUrls as $provider => $logoutUrl) {
                        if ($provider == $this->user->getSsoProvider()) {
                            return $this->redirect($logoutUrl);
                        }
                    }
                }
            }
        }
    }
}
