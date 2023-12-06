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

namespace App\Gratuity\EventSubscriber;

use App\Action\Event\ActionEvent;
use App\Gratuity\Service\GratuityCampaignActionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ActionSubscriber implements EventSubscriberInterface
{
    private $_gratuityCampaignActionManager;
    private $_gratuityActive;

    public function __construct(GratuityCampaignActionManager $gratuityCampaignActionManager, bool $gratuityActive)
    {
        $this->_gratuityCampaignActionManager = $gratuityCampaignActionManager;
        $this->_gratuityActive = $gratuityActive;
    }

    public static function getSubscribedEvents()
    {
        return [
            ActionEvent::NAME => 'onActionEvent',
        ];
    }

    public function onActionEvent(ActionEvent $event)
    {
        if ($this->_gratuityActive && 'user_home_address_updated' == $event->getAction()->getName()) {
            $this->_gratuityCampaignActionManager->handleAction($event->getUser());
        }
    }
}
