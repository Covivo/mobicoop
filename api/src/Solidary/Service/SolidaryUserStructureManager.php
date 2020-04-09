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

namespace App\Solidary\Service;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Exception\SolidaryException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * Handle an update of a SolidaryUserStructure
     *
     * @param SolidaryUserStructure $solidaryUserStructure
     * @return SolidaryUserStructure
     */
    public function updateSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure)
    {

        // If we accept or refuse a candidate for this SolidaryUserStructure we need to se the rights correctly
        $user = $solidaryUserStructure->getSolidaryUser()->getUser();

        if ($solidaryUserStructure->getStatus() == SolidaryUserStructure::STATUS_ACCEPTED) {
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
            $event = new SolidaryUserStructureAccepted($solidaryUserStructure);
            $this->eventDispatcher->dispatch(SolidaryUserStructureAccepted::NAME, $event);
        } elseif ($solidaryUserStructure->getStatus() == SolidaryUserStructure::STATUS_REFUSED) {
            // just dispatch the event
            $event = new SolidaryUserStructureRefused($solidaryUserStructure);
            $this->eventDispatcher->dispatch(SolidaryUserStructureRefused::NAME, $event);
        } elseif ($solidaryUserStructure->getStatus() == SolidaryUserStructure::STATUS_PENDING) {
            // TO DO
            // What do we do ?
        } else {
            throw new SolidaryException(SolidaryException::BAD_SOLIDARYUSERSTRUCTURE_STATUS);
        }


        return $solidaryUserStructure;
    }
}
