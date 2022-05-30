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

namespace App\Action\EventSubscriber;

use App\Action\Event\ActionEvent;
use App\Action\Service\ActionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\User\Event\LoginDelegateEvent;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ActionSubscriber implements EventSubscriberInterface
{
    private $actionManager;

    public function __construct(ActionManager $actionManager)
    {
        $this->actionManager = $actionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginDelegateEvent::NAME => 'onLoginDelegate',
            ActionEvent::NAME => 'onAction'
        ];
    }

    public function onLoginDelegate(LoginDelegateEvent $event)
    {
        $this->actionManager->handleAction(LoginDelegateEvent::NAME, $event);
    }

    /**
     * Generic action handler
     *
     * @param ActionEvent $event
     * @return void
     */
    public function onAction(ActionEvent $event): void
    {
        $this->actionManager->onAction($event);
    }
}
