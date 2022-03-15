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

use App\Auth\Repository\AuthItemRepository;
use App\Geography\Repository\TerritoryRepository;
use App\Scammer\Entity\Scammer;
use App\Service\FormatDataManager;
use App\User\Repository\UserRepository;
use App\User\Service\IdentityProofManager;
use App\User\Service\UserManager as ServiceUserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Scammer manager service for administration.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class ScammerManager
{
    private $entityManager;
    private $authItemRepository;
    private $territoryRepository;
    private $encoder;
    private $eventDispatcher;
    private $security;
    private $userManager;
    private $userRepository;
    private $formatDataManager;
    private $identityProofManager;

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
        AuthItemRepository $authItemRepository,
        TerritoryRepository $territoryRepository,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ServiceUserManager $userManager,
        UserRepository $userRepository,
        FormatDataManager $formatDataManager,
        IdentityProofManager $identityProofManager
    ) {
        $this->entityManager = $entityManager;
        $this->authItemRepository = $authItemRepository;
        $this->territoryRepository = $territoryRepository;
        $this->encoder = $encoder;
        $this->eventDispatcher = $dispatcher;
        $this->security = $security;
        $this->userManager = $userManager;
        $this->userRepository = $userRepository;
        $this->formatDataManager = $formatDataManager;
        $this->identityProofManager = $identityProofManager;
    }

    /**
     * Add a scammer.
     *
     * @param Scammer $scammer The scammer to add
     *
     * @return Scammer The scammer added
     */
    public function addScammer(Scammer $scammer)
    {
        $this->entityManager->persist($scammer);
        $this->entityManager->flush();

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
