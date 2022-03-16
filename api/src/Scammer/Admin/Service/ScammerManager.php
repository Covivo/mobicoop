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

namespace App\Scammer\Admin\Service;

use App\Scammer\Entity\Scammer;
use App\Scammer\Event\ScammerAddedEvent;
use App\User\Admin\Service\UserManager;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Scammer manager service for administration.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class ScammerManager
{
    private $entityManager;
    private $eventDispatcher;
    private $userManager;
    private $userRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     * @param mixed                  $chat
     * @param mixed                  $smoke
     * @param mixed                  $music
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        UserRepository $userRepository,
        UserManager $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $dispatcher;
        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
    }

    /**
     * Add a scammer.
     *
     * @param Scammer $scammer The scammer to add
     *
     * @return Scammer The scammer added
     */
    public function addScammer(Scammer $scammer, User $user)
    {
        $scammer->setUser($user);
        $this->entityManager->persist($scammer);
        $this->entityManager->flush();

        //  we dispatch the event associated
        $event = new ScammerAddedEvent($scammer);
        $this->eventDispatcher->dispatch($event, ScammerAddedEvent::NAME);

        // we delete the user reported
        $this->userManager->deleteUser($this->userRepository->findOneBy(['email' => $scammer->getEmail()]));

        return $scammer;
    }

    /**
     * Delete a scammer.
     *
     * @param Scammer $scammer The scammer to delete
     */
    public function deleteScammer(Scammer $scammer)
    {
        $this->scammerManager->deleteScammer($scammer);

        return $scammer;
    }
}
