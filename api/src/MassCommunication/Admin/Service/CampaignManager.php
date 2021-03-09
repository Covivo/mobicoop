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
 **************************/

namespace App\MassCommunication\Admin\Service;

use App\Communication\Entity\Medium;
use App\Communication\Repository\MediumRepository;
use App\User\Entity\User;
use App\MassCommunication\Entity\Campaign;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Campaign manager service in administration context.
 */
class CampaignManager
{
    private $entityManager;
    private $mediumRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MediumRepository $mediumRepository
    ) {
        $this->entityManager = $entityManager;
        $this->mediumRepository = $mediumRepository;
    }

    /**
     * Add a campaign
     *
     * @param Campaign $campaign    The campaign to add
     * @param User $user            The user that adds the campaign
     * @return Campaign             The created campaign
     */
    public function addCampaign(Campaign $campaign, User $user)
    {
        $campaign->setMedium($this->mediumRepository->find(Medium::MEDIUM_EMAIL));
        $campaign->setUser($user);
        $campaign->setEmail($user->getEmail());
        $campaign->setReplyTo($user->getEmail());
        $campaign->setFromName("Mobicoop");
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();
        
        return $campaign;
    }

    /**
     * Patch a campaign.
     *
     * @param Campaign $campaign    The campaign to update
     * @param array $fields         The updated fields
     * @return Campaign             The campaign updated
     */
    public function patchCampaign(Campaign $campaign, array $fields)
    {
        // persist the campaign
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();
        
        // return the campaign
        return $campaign;
    }

    /**
     * Delete a campaign
     *
     * @param Campaign $campaign  The campaign to delete
     * @return void
     */
    public function deleteCampaign(Campaign $campaign)
    {
        $this->entityManager->remove($campaign);
        $this->entityManager->flush();
    }
}
