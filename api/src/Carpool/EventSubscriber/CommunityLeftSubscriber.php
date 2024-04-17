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

namespace App\Carpool\EventSubscriber;

use App\Carpool\Repository\ProposalRepository;
use App\Community\Event\CommunityLeftEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CommunityLeftSubscriber implements EventSubscriberInterface
{
    private $_entityManager;
    private $_proposalRepository;

    public function __construct(EntityManagerInterface $entityManager, ProposalRepository $proposalRepository)
    {
        $this->_entityManager = $entityManager;
        $this->_proposalRepository = $proposalRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            CommunityLeftEvent::NAME => '_onCommunityLeftEvent',
        ];
    }

    public function _onCommunityLeftEvent(CommunityLeftEvent $event)
    {
        $proposals = $this->_proposalRepository->findBy(['user' => $event->getUser(), 'private' => false]);
        foreach ($proposals as $proposal) {
            foreach ($proposal->getCommunities() as $community) {
                if ($community->getId() == $event->getCommunity()->getId()) {
                    $proposal->removeCommunity($community);
                    $this->_entityManager->persist($proposal);
                    $this->_entityManager->flush();
                }
            }
        }
    }
}
