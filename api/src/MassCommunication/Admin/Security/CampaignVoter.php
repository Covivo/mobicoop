<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\MassCommunication\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\MassCommunication\Entity\Campaign;

class CampaignVoter extends Voter
{
    const ADMIN_CAMPAIGN_CREATE = 'admin_campaign_create';
    const ADMIN_CAMPAIGN_READ = 'admin_campaign_read';
    const ADMIN_CAMPAIGN_UPDATE = 'admin_campaign_update';
    const ADMIN_CAMPAIGN_DELETE = 'admin_campaign_delete';
    const ADMIN_CAMPAIGN_LIST = 'admin_campaign_list';
    const ADMIN_CAMPAIGN_SEND = 'admin_campaign_send';
    const ADMIN_CAMPAIGN_TEST = 'admin_campaign_test';
    const CAMPAIGN_CREATE = 'campaign_create';
    const CAMPAIGN_READ = 'campaign_read';
    const CAMPAIGN_UPDATE = 'campaign_update';
    const CAMPAIGN_DELETE = 'campaign_delete';
    const CAMPAIGN_LIST = 'campaign_list';
    
    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_CAMPAIGN_CREATE,
            self::ADMIN_CAMPAIGN_READ,
            self::ADMIN_CAMPAIGN_UPDATE,
            self::ADMIN_CAMPAIGN_DELETE,
            self::ADMIN_CAMPAIGN_LIST,
            self::ADMIN_CAMPAIGN_SEND,
            self::ADMIN_CAMPAIGN_TEST
            ])) {
            return false;
        }

        // only vote on Campaign objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_CAMPAIGN_CREATE,
            self::ADMIN_CAMPAIGN_READ,
            self::ADMIN_CAMPAIGN_UPDATE,
            self::ADMIN_CAMPAIGN_DELETE,
            self::ADMIN_CAMPAIGN_LIST,
            self::ADMIN_CAMPAIGN_SEND,
            self::ADMIN_CAMPAIGN_TEST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Campaign)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_CAMPAIGN_CREATE:
                return $this->canCreateCampaign();
            case self::ADMIN_CAMPAIGN_READ:
                return $this->canReadCampaign($subject);
            case self::ADMIN_CAMPAIGN_UPDATE:
                return $this->canUpdateCampaign($subject);
            case self::ADMIN_CAMPAIGN_DELETE:
                return $this->canDeleteCampaign($subject);
            case self::ADMIN_CAMPAIGN_LIST:
                return $this->canListCampaign();
            case self::ADMIN_CAMPAIGN_SEND:
                return $this->canSendCampaign($subject);
            case self::ADMIN_CAMPAIGN_TEST:
                return $this->canTestCampaign($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateCampaign()
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_CREATE);
    }

    private function canReadCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_READ, ['campaign'=>$campaign]);
    }

    private function canUpdateCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_UPDATE, ['campaign'=>$campaign]);
    }

    private function canDeleteCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_DELETE, ['campaign'=>$campaign]);
    }

    private function canListCampaign()
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_LIST);
    }

    private function canSendCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_READ, ['campaign'=>$campaign]);
    }

    private function canTestCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_READ, ['campaign'=>$campaign]);
    }
}
