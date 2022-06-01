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
 */

namespace App\Event\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Event\Entity\Event;
use App\Event\Repository\EventRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const ADMIN_EVENT_CREATE = 'admin_event_create';
    public const ADMIN_EVENT_READ = 'admin_event_read';
    public const ADMIN_EVENT_UPDATE = 'admin_event_update';
    public const ADMIN_EVENT_DELETE = 'admin_event_delete';
    public const ADMIN_EVENT_LIST = 'admin_event_list';
    public const EVENT_CREATE = 'event_create';
    public const EVENT_READ = 'event_read';
    public const EVENT_UPDATE = 'event_update';
    public const EVENT_DELETE = 'event_delete';
    public const EVENT_LIST = 'event_list';

    private $authManager;
    private $request;
    private $eventRepository;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, EventRepository $eventRepository)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->eventRepository = $eventRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_EVENT_CREATE,
            self::ADMIN_EVENT_READ,
            self::ADMIN_EVENT_UPDATE,
            self::ADMIN_EVENT_DELETE,
            self::ADMIN_EVENT_LIST,
        ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_EVENT_CREATE,
            self::ADMIN_EVENT_READ,
            self::ADMIN_EVENT_UPDATE,
            self::ADMIN_EVENT_DELETE,
            self::ADMIN_EVENT_LIST,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Event)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ADMIN_EVENT_CREATE:
                return $this->canCreateEvent();

            case self::ADMIN_EVENT_READ:
                // this voter is used for direct event read, we have to check the type of subject
                if ($subject instanceof Event) {
                    return $this->canReadEvent($subject);
                }
                if ($event = $this->eventRepository->find($this->request->get('id'))) {
                    return $this->canReadEvent($event);
                }

                return false;

            case self::ADMIN_EVENT_UPDATE:
                return $this->canUpdateEvent($subject);

            case self::ADMIN_EVENT_DELETE:
                return $this->canDeleteEvent($subject);

            case self::ADMIN_EVENT_LIST:
                return $this->canListEvent();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateEvent()
    {
        return $this->authManager->isAuthorized(self::EVENT_CREATE);
    }

    private function canReadEvent(Event $event)
    {
        return $this->authManager->isAuthorized(self::EVENT_READ, ['event' => $event]);
    }

    private function canUpdateEvent(Event $event)
    {
        return $this->authManager->isAuthorized(self::EVENT_UPDATE, ['event' => $event]);
    }

    private function canDeleteEvent(Event $event)
    {
        return $this->authManager->isAuthorized(self::EVENT_DELETE, ['event' => $event]);
    }

    private function canListEvent()
    {
        return $this->authManager->isAuthorized(self::EVENT_LIST);
    }
}
