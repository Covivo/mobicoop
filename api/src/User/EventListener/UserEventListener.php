<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\User\EventListener;

use App\Communication\Entity\Medium;
use App\User\Event\UserPhoneValidatedEvent;
use App\User\Repository\UserNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User Event listener.
 */
class UserEventListener implements EventSubscriberInterface
{
    private const ACTIONS_TO_ENABLE = [4, 5, 6, 7];
    private $_userNotificationRepository;
    private $_entityManager;

    public function __construct(UserNotificationRepository $userNotificationRepository, EntityManagerInterface $entityManager)
    {
        $this->_userNotificationRepository = $userNotificationRepository;
        $this->_entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserPhoneValidatedEvent::NAME => 'onPhoneValidated',
        ];
    }

    public function onPhoneValidated(UserPhoneValidatedEvent $event)
    {
        $userNotifications = $this->_userNotificationRepository->findActiveByMedium(Medium::MEDIUM_SMS, $event->getUser());

        foreach ($userNotifications as $userNotification) {
            if (in_array($userNotification->getNotification()->getAction()->getId(), self::ACTIONS_TO_ENABLE)) {
                $userNotification->setActive(true);
                $this->_entityManager->persist($userNotification);
            }

            $this->_entityManager->persist($userNotification);
        }
        $this->_entityManager->flush();
    }
}
