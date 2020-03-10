<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Event\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Event\Entity\Event;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Event\Service\EventManager;
use Symfony\Component\HttpFoundation\RequestStack;

class EventVoter extends Voter
{
    const EVENT_CREATE = 'event_create';
    const EVENT_READ = 'event_read';
    const EVENT_UPDATE = 'event_update';
    const EVENT_DELETE = 'event_delete';
    const EVENT_REPORT = 'event_report';
    const EVENT_LIST = 'event_list';

    private $security;
    private $authManager;
    private $requestStack;
    private $eventManager;

    public function __construct(Security $security, AuthManager $authManager, RequestStack $requestStack, EventManager $eventManager)
    {
        $this->security = $security;
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->eventManager = $eventManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::EVENT_CREATE,
            self::EVENT_READ,
            self::EVENT_UPDATE,
            self::EVENT_DELETE,
            self::EVENT_REPORT,
            self::EVENT_LIST
            ])) {
            return false;
        }

        // only vote on Article objects inside this voter
        if (!in_array($attribute, [
            self::EVENT_CREATE,
            self::EVENT_READ,
            self::EVENT_UPDATE,
            self::EVENT_DELETE,
            self::EVENT_REPORT,
            self::EVENT_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Event)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::EVENT_CREATE:
                return $this->canCreateEvent();
            case self::EVENT_READ:
                return $this->canReadEvent($subject);
            case self::EVENT_UPDATE:
                return $this->canUpdateEvent($subject);
            case self::EVENT_DELETE:
                return $this->canDeleteEvent($subject);
            case self::EVENT_REPORT:
                // here we don't have the denormalized event, we need to get it from the request
                if ($event = $this->eventManager->getEvent($this->request->get('id'))) {
                    return $this->canReadEvent($event);
                }
                return false;
            case self::EVENT_LIST:
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
        return $this->authManager->isAuthorized(self::EVENT_READ, ['event'=>$event]);
    }

    private function canUpdateEvent(Event $event)
    {
        return $this->authManager->isAuthorized(self::EVENT_UPDATE, ['event'=>$event]);
    }
    
    private function canDeleteEvent(Event $event)
    {
        return $this->authManager->isAuthorized(self::EVENT_DELETE, ['event'=>$event]);
    }
    
    private function canListEvent()
    {
        return $this->authManager->isAuthorized(self::EVENT_LIST);
    }
}
