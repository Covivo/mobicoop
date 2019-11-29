<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Import\Service;

use App\Carpool\Service\ProposalManager;
use App\Import\Entity\UserImport;
use App\Import\Repository\UserImportRepository;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Import manager service.
 * Used to import external data into the platform.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ImportManager
{
    private $entityManager;
    private $userImportRepository;
    private $proposalManager;
    private $userManager;
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserImportRepository $userImportRepository, ProposalManager $proposalManager, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userImportRepository = $userImportRepository;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
    }

    /**
     * Treat imported users
     *
     * @return array    The users imported
     */
    public function treatUserImport()
    {
        set_time_limit(3600);
        $pool = 0;

        // batch for users
        $batch = 5000;
        
        // we have to treat all the users that have just been imported
        // first pass : user related treatment
        $importedUsers = $this->userImportRepository->findBy(['status'=>UserImport::STATUS_IMPORTED]);
        foreach ($importedUsers as $import) {
            $import->setStatus(UserImport::STATUS_PENDING);
            $import->setTreatmentUserStartDate(new \DateTime());
            $this->entityManager->persist($import);

            // we treat the user
            $this->userManager->treatUser($import->getUser());

            $import->setStatus(UserImport::STATUS_USER_TREATED);
            $import->setTreatmentUserEndDate(new \DateTime());
            $this->entityManager->persist($import);

            // batch
            $pool++;
            if ($pool>=$batch) {
                $this->entityManager->flush();
                $pool = 0;
            }
        }
        // final flush for pending persists
        $this->entityManager->flush();
        
        // new batch for proposals
        $batch = 500;
        
        // reinit the pool
        $pool = 0;

        // second pass : journey related treatment
        // in this pass we just compute the directions of the proposals, to get the zones and limit the future matching
        $importedUsers = $this->userImportRepository->findBy(['status'=>UserImport::STATUS_USER_TREATED]);

        // we create an array of all proposals to treat
        $proposals = [];
        foreach ($importedUsers as $import) {
            // $import->setStatus(UserImport::STATUS_PENDING);
            // $import->setTreatmentUserStartDate(new \DateTime());
            // $this->entityManager->persist($import);
            foreach ($import->getUser()->getProposals() as $proposal) {
                $proposals[] = $proposal;
            }
            // batch
            // $pool++;
            // if ($pool>=$batch) {
            //     $this->entityManager->flush();
            //     $pool = 0;
            // }
        }
        // final flush for pending persists
        // $this->entityManager->flush();

        // reinit the pool
        $pool = 0;

        // creation of the directions
        $proposals = $this->proposalManager->setDirectionsForProposals($proposals, $batch);

        // creation of the defaults
        $proposals = $this->proposalManager->setDefaultsForProposals($proposals, $batch);

        // creation of the matchings
        //$proposals = $this->proposalManager->createMatchingsForProposals($proposals);

        // treat the return and opposite
        //$proposals = $this->proposalManager->createLinkedAndOppositesForProposals($proposals);

        return $importedUsers;
    }
}
