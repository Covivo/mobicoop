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

namespace App\MassCommunication\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\MassCommunication\Entity\Campaign;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CampaignVoter extends Voter
{
    public const CAMPAIGN_CREATE = 'campaign_create';
    public const CAMPAIGN_READ = 'campaign_read';
    public const CAMPAIGN_UPDATE = 'campaign_update';
    public const CAMPAIGN_DELETE = 'campaign_delete';
    public const CAMPAIGN_LIST = 'campaign_list';

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CAMPAIGN_CREATE,
            self::CAMPAIGN_READ,
            self::CAMPAIGN_UPDATE,
            self::CAMPAIGN_DELETE,
            self::CAMPAIGN_LIST,
        ])) {
            return false;
        }

        // only vote on Campaign objects inside this voter
        if (!in_array($attribute, [
            self::CAMPAIGN_CREATE,
            self::CAMPAIGN_READ,
            self::CAMPAIGN_UPDATE,
            self::CAMPAIGN_DELETE,
            self::CAMPAIGN_LIST,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Campaign)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::CAMPAIGN_CREATE:
                return $this->canCreateCampaign();

            case self::CAMPAIGN_READ:
                return $this->canReadCampaign($subject);

            case self::CAMPAIGN_UPDATE:
                return $this->canUpdateCampaign($subject);

            case self::CAMPAIGN_DELETE:
                return $this->canDeleteCampaign($subject);

            case self::CAMPAIGN_LIST:
                return $this->canListCampaign();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateCampaign()
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_CREATE);
    }

    private function canReadCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_READ, ['campaign' => $campaign]);
    }

    private function canUpdateCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_UPDATE, ['campaign' => $campaign]);
    }

    private function canDeleteCampaign(Campaign $campaign)
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_DELETE, ['campaign' => $campaign]);
    }

    private function canListCampaign()
    {
        return $this->authManager->isAuthorized(self::CAMPAIGN_LIST);
    }
}
