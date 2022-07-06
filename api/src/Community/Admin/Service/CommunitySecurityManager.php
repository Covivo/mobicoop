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

namespace App\Community\Admin\Service;

use App\Community\Admin\Repository\CommunitySecurityRepository;
use App\Community\Entity\Community;
use App\Community\Entity\CommunitySecurity;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Community Security manager.
 *
 * This service contains methods related to community security file management.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CommunitySecurityManager
{
    private $communityManager;
    private $entityManager;
    private $communitySecurityRepository;

    public function __construct(
        CommunityManager $communityManager,
        EntityManagerInterface $entityManager,
        CommunitySecurityRepository $communitySecurityRepository
    )
    {
        $this->communityManager = $communityManager;
        $this->entityManager = $entityManager;
        $this->communitySecurityRepository = $communitySecurityRepository;
    }

    public function createSecurity(File $file, int $communityId): CommunitySecurity
    {
        if ($community = $this->communityManager->getCommunity($communityId)) {
            // remove old securities
            $this->removeOldCommunitySecurities($community);

            $communitySecurity = new CommunitySecurity();
            $communitySecurity->setFile($file);
            $communitySecurity->setFileName(time().'-'.$communityId);
            $communitySecurity->setCommunity($community);
            $this->entityManager->persist($communitySecurity);
            $this->entityManager->flush();

            return $communitySecurity;
        }

        throw new LogicException('Community '.$communityId.' not found');
    }

    public function removeOldCommunitySecurities(Community $community)
    {
        $communitySecurities = $this->getCommunitySecurities($community);
        foreach ($communitySecurities as $communitySecurity) {
            $this->entityManager->remove($communitySecurity);
        }
        $this->entityManager->flush();
    }

    public function getCommunitySecurities(Community $community): array
    {
        return $this->communitySecurityRepository->findBy(['community' => $community]);
    }
}
