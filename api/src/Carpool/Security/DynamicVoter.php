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
use App\Carpool\Entity\Dynamic;
use App\Carpool\Service\DynamicManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class DynamicVoter extends Voter
{
    const DYNAMIC_AD_READ = 'dynamic_ad_read';
    const DYNAMIC_AD_CREATE = 'dynamic_ad_create';
    const DYNAMIC_AD_UPDATE = 'dynamic_ad_update';
    const DYNAMIC_AD_DELETE = 'dynamic_ad_delete';
    const DYNAMIC_AD_LIST = 'dynamic_ad_list';
    const DYNAMIC_ASK_READ = 'dynamic_ask_read';
    const DYNAMIC_ASK_CREATE = 'dynamic_ask_create';
    const DYNAMIC_ASK_UPDATE = 'dynamic_ask_update';
    const DYNAMIC_ASK_DELETE = 'dynamic_ask_delete';
    
    private $security;
    private $request;
    private $authManager;
    private $dynamicManager;

    public function __construct(RequestStack $requestStack, Security $security, AuthManager $authManager, DynamicManager $dynamicManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->authManager = $authManager;
        $this->dynamicManager = $dynamicManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::DYNAMIC_AD_READ,
            self::DYNAMIC_AD_CREATE,
            self::DYNAMIC_AD_UPDATE,
            self::DYNAMIC_AD_DELETE,
            self::DYNAMIC_AD_LIST,
            self::DYNAMIC_ASK_READ,
            self::DYNAMIC_ASK_CREATE,
            self::DYNAMIC_ASK_UPDATE,
            self::DYNAMIC_ASK_DELETE
            ])) {
            return false;
        }

        // Dynamic is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::DYNAMIC_AD_CREATE:
                return $this->canCreateDynamicAd();
            case self::DYNAMIC_AD_READ:
                if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
                    return $this->canReadDynamicAd($subject);
                }
                return false;
            case self::DYNAMIC_AD_UPDATE:
                if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
                    return $this->canUpdateDynamicAd($dynamic);
                }
                return false;
            case self::DYNAMIC_AD_DELETE:
                if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
                    return $this->canDeleteDynamicAd($subject);
                }
                return false;
            case self::DYNAMIC_AD_LIST:
                return $this->canListDynamicAd();
            case self::DYNAMIC_ASK_CREATE:
                return $this->canCreateDynamicAsk();
            case self::DYNAMIC_ASK_READ:
                if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
                    return $this->canReadDynamicAsk($subject);
                }
                return false;
            case self::DYNAMIC_ASK_UPDATE:
                if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
                    return $this->canUpdateDynamicAsk($subject);
                }
                return false;
            case self::DYNAMIC_ASK_DELETE:
                if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
                    return $this->canDeleteDynamicAsk($subject);
                }
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateDynamicAd()
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_AD_CREATE);
    }

    private function canReadDynamicAd(Dynamic $dynamic)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_AD_READ, ['dynamic' => $dynamic]);
    }

    private function canUpdateDynamicAd(Dynamic $dynamic)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_AD_UPDATE, ['dynamic' => $dynamic]);
    }

    private function canDeleteDynamicAd(Dynamic $dynamic)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_AD_DELETE, ['dynamic' => $dynamic]);
    }

    private function canListDynamicAd()
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_AD_LIST);
    }

    private function canCreateDynamicAsk()
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_CREATE);
    }

    private function canReadDynamicAsk(Dynamic $dynamic)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_READ, ['dynamic' => $dynamic]);
    }

    private function canUpdateDynamicAsk(Dynamic $dynamic)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_UPDATE, ['dynamic' => $dynamic]);
    }

    private function canDeleteDynamicAsk(Dynamic $dynamic)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_DELETE, ['dynamic' => $dynamic]);
    }
}
