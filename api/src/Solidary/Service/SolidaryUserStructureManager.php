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

namespace App\Solidary\Service;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Event\SolidaryUserStructureAcceptedEvent;
use App\Solidary\Event\SolidaryUserStructureRefusedEvent;
use App\Solidary\Exception\SolidaryException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryUserStructureManager
{
    private $entityManager;
    private $authItemRepository;
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, AuthItemRepository $authItemRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->authItemRepository = $authItemRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle an update of a SolidaryUserStructure.
     *
     * @return SolidaryUserStructure
     */
    public function updateSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure): SolidaryUserStructure
    {
        // If we accept or refuse a candidate for this SolidaryUserStructure we need to se the rights correctly
        $user = $solidaryUserStructure->getSolidaryUser()->getUser();

        if (SolidaryUserStructure::STATUS_ACCEPTED == $solidaryUserStructure->getStatus()) {
            if ($solidaryUserStructure->getSolidaryUser()->isVolunteer()) {
                $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_VOLUNTEER);
            } elseif ($solidaryUserStructure->getSolidaryUser()->isBeneficiary()) {
                $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_BENEFICIARY);
            } else {
                throw new SolidaryException(SolidaryException::NO_ROLE);
            }
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $user->addUserAuthAssignment($userAuthAssignment);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // dispatch the event
            $event = new SolidaryUserStructureAcceptedEvent($solidaryUserStructure);
            $this->eventDispatcher->dispatch($event, SolidaryUserStructureAcceptedEvent::NAME);
        } elseif (SolidaryUserStructure::STATUS_REFUSED == $solidaryUserStructure->getStatus()) {
            // just dispatch the event
            $event = new SolidaryUserStructureRefusedEvent($solidaryUserStructure);
            $this->eventDispatcher->dispatch($event, SolidaryUserStructureRefusedEvent::NAME);
        } elseif (SolidaryUserStructure::STATUS_PENDING == $solidaryUserStructure->getStatus()) {
            // TO DO
            // What do we do ?
        } else {
            throw new SolidaryException(SolidaryException::BAD_SOLIDARYUSERSTRUCTURE_STATUS);
        }

        return $solidaryUserStructure;
    }
}
