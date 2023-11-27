<?php
/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Serializer;

use App\Gratuity\Repository\GratuityCampaignRepository;
use App\User\Entity\User;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GratuityNotificationNormalizer
{
    private $_gratuityCampaignRepository;
    private $_user;
    private $_data;

    public function __construct(GratuityCampaignRepository $gratuityCampaignRepository)
    {
        $this->_gratuityCampaignRepository = $gratuityCampaignRepository;
    }

    public function setUser(User $user): self
    {
        $this->_user = $user;

        return $this;
    }

    public function normalize(array $data): array
    {
        if (!$this->_user->hasGratuity()) {
            return $data;
        }

        $this->_data = $data;
        $this->_setPendingGamificationNotification();

        return $this->_data;
    }

    private function _isEligible(): bool
    {
        return true;
    }

    private function _setPendingGamificationNotification()
    {
        $pendingNotifications = $this->_getPendingGamificationNotification();
        if (count($pendingNotifications) > 0) {
            $this->_data['gratuityNotifications'] = [];
            foreach ($pendingNotifications as $pendingNotification) {
                if ($this->_isEligible()) {
                    $notification = [];
                    $notification['id'] = $pendingNotification->getId();
                    $notification['name'] = $pendingNotification->getName();
                    $notification['template'] = $pendingNotification->getTemplate();
                    $this->_data['gratuityNotifications'][] = $notification;
                }
            }
        }
    }

    private function _getPendingGamificationNotification()
    {
        return $this->_gratuityCampaignRepository->findPendingForUser($this->_user);
    }
}
