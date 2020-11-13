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

namespace App\Carpool\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Carpool\Ressource\Ad;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Carpool\Ressource\MyAd;
use App\Carpool\Service\MyAdManager;
use Symfony\Component\HttpFoundation\RequestStack;

class MyAdVoter extends Voter
{
    const MY_AD_READ_SELF = 'my_ad_read_self';
    const MY_AD_LIST_SELF = 'my_ad_list_self';
    
    private $request;
    private $authManager;
    private $myAdManager;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, MyAdManager $myAdManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->authManager = $authManager;
        $this->myAdManager = $myAdManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::MY_AD_READ_SELF,
            self::MY_AD_LIST_SELF
            ])) {
            return false;
        }

        // only vote on Ad objects inside this voter
        if (!in_array($attribute, [
            self::MY_AD_READ_SELF,
            self::MY_AD_LIST_SELF
            ]) && !($subject instanceof Paginator) && !($subject instanceof MyAd)) {
            return false;
        }

        // MyAd is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::MY_AD_READ_SELF:
                // $myAd = $this->myAdManager->getMyAd($this->request->get('id'));
                // return $this->canReadMyAd($myAd);
            case self::MY_AD_LIST_SELF:
                return $this->canListAd();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadAd(MyAd $myAd)
    {
        return $this->authManager->isAuthorized(self::MY_AD_READ_SELF, ['myAd'=>$myAd]);
    }

    private function canListAd()
    {
        return $this->authManager->isAuthorized(self::MY_AD_LIST_SELF);
    }
}
