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

namespace App\Carpool\Service;

use App\Carpool\Ressource\CarpoolExport;
use App\Payment\Entity\CarpoolItem;
use Symfony\Component\Security\Core\Security;
use App\Payment\Repository\CarpoolItemRepository;
use App\Carpool\Entity\Waypoint;

/**
 * CarpoolExport manager service.
 *
 * @author Remi Wortemann <remi.wortemann@covivo.eu>
 */
class CarpoolExportManager
{
    private $security;
    private $carpoolItemRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        Security $security,
        CarpoolItemRepository $carpoolItemRepository
    ) {
        $this->security = $security;
        $this->carpoolItemRepository = $carpoolItemRepository;
    }

    /**
     * Method to get all carpoolExports for a user
     *
     * @return Array
     */
    public function getCarpoolExports()
    {
        $user = $this->security->getUser();
        // we get all carpoolItems of a user as debtor or creditor
        $carpoolItems = $this->carpoolItemRepository->findByUser($user);

        $carpoolExports = [];
        // we create an array of carpoolExport
        foreach ($carpoolItems as $carpoolItem) {
            $carpoolExport = new CarpoolExport();
            $carpoolExport->setId($carpoolItem->getId());
            $carpoolExport->setDate($carpoolItem->getItemDate());
            $carpoolExport->setAmount($carpoolItem->getAmount());
            //    we set the payment mode
            if ($carpoolItem->getCreditorStatus() == (CarpoolItem::CREDITOR_STATUS_DIRECT || CarpoolItem::DEBTOR_STATUS_DIRECT || CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT)) {
                $carpoolExport->setMode(CarpoolExport::MODE_DIRECT);
            } else {
                $carpoolExport->setMode(CarpoolExport::MODE_ONLINE);
            };
            //    we set the role and the carpooler
            if ($carpoolItem->getCreditorUser()->getId() == $user->getId()) {
                $carpoolExport->setRole(CarpoolExport::ROLE_DRIVER);
                $carpoolExport->setCarpooler($carpoolItem->getDebtorUser());
            } else {
                $carpoolExport->setRole(CarpoolExport::ROLE_PASSENGER);
                $carpoolExport->setCarpooler($carpoolItem->getCreditorUser());
            }
            //    we set the pickUp and dropOff
            $waypoints = $carpoolItem->getAsk()->getMatching()->getProposalRequest()->getWaypoints();
            $carpoolExport->setPickUp($waypoints[0]->getAddress());
            foreach ($waypoints as $waypoint) {
                if ($waypoint->isDestination()) {
                    $carpoolExport->setDropOff($waypoint->getAddress());
                }
            }
            //    we set the certification type
            if ($carpoolItem->getAsk()->getCarpoolProofs()) {
                foreach ($carpoolItem->getAsk()->getCarpoolProofs() as $carpoolProof) {
                    if ($carpoolProof->getPickUpPassengerDate() == $carpoolItem->getItemDate()) {
                        $carpoolExport->setCertification($carpoolProof->getType());
                    }
                }
            }
       
            $carpoolExports[] = $carpoolExport;
        }
        return $carpoolExports;
    }
}
