<?php
/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Communication\EventSubscriber;

use App\Communication\Service\NotificationManager;
use App\Scammer\Event\ScammerAddedEvent;
use App\User\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScammerSubscriber implements EventSubscriberInterface
{
    private $notificationManager;
    private $userRepository;

    public function __construct(NotificationManager $notificationManager, UserRepository $userRepository)
    {
        $this->notificationManager = $notificationManager;
        $this->userRepository = $userRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            ScammerAddedEvent::NAME => 'onScammerAdded',
        ];
    }

    /**
     * Executed when a scammer is added.
     */
    public function onScammerAdded(ScammerAddedEvent $event)
    {
        $scammer = $event->getScammer();
        $scammerVictims = $event->getScammerVictims();

        // get all users with an ask in common with the scammer
        foreach ($scammerVictims as $scammerVictim) {
            $this->notificationManager->notifies(ScammerAddedEvent::NAME, $this->userRepository->find($scammerVictim), $scammer);
        }
    }
}
