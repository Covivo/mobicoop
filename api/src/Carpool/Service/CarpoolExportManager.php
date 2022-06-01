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

namespace App\Carpool\Service;

use App\Carpool\Entity\CarpoolExport;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use App\Utility\Service\PdfManager;
use DateTime;
use Symfony\Component\Security\Core\Security;

/**
 * CarpoolExport manager service.
 *
 * @author Remi Wortemann <remi.wortemann@covivo.eu>
 */
class CarpoolExportManager
{
    private $security;
    private $pdfManager;
    private $carpoolItemRepository;
    private $carpoolExportUri;
    private $carpoolExportPath;
    private $carpoolExportPlatformName;
    private $paymentActive;
    private $paymentActiveDate;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        Security $security,
        PdfManager $pdfManager,
        CarpoolItemRepository $carpoolItemRepository,
        string $carpoolExportUri,
        string $carpoolExportPath,
        string $carpoolExportPlatformName,
        string $paymentActive
    ) {
        $this->security = $security;
        $this->pdfManager = $pdfManager;
        $this->carpoolItemRepository = $carpoolItemRepository;
        $this->carpoolExportUri = $carpoolExportUri;
        $this->carpoolExportPath = $carpoolExportPath;
        $this->carpoolExportPlatformName = $carpoolExportPlatformName;
        $this->paymentActive = false;
        if ($this->paymentActiveDate = DateTime::createFromFormat('Y-m-d', $paymentActive)) {
            $this->paymentActiveDate->setTime(0, 0);
            $this->paymentActive = true;
        }
    }

    /**
     * Method to get all carpoolExports for a user.
     */
    public function getCarpoolExports(): string
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        // we get all carpoolItems of a user as debtor or creditor
        $carpoolItems = $this->carpoolItemRepository->findByUser($user);

        $carpoolExports = [];
        $sumPaid = null;
        $sumReceived = null;
        // we create an array of carpoolExport
        foreach ($carpoolItems as $carpoolItem) {
            // Check if the User is debtor or creditor
            $isCreditor = false;
            if ($carpoolItem->getCreditorUser()->getId() == $user->getId()) {
                $isCreditor = true;
            }

            $carpoolExport = new CarpoolExport();
            $carpoolExport->setId($carpoolItem->getId());
            $carpoolExport->setDate($carpoolItem->getItemDate());
            $carpoolExport->setAmount($carpoolItem->getAmount());
            //    we set the payment mode
            if (0 !== $carpoolItem->getItemStatus()) {
                // We check the status of the right role
                if ($isCreditor) {
                    switch ($carpoolItem->getCreditorStatus()) {
                        case CarpoolItem::CREDITOR_STATUS_DIRECT:
                            $carpoolExport->setMode(CarpoolExport::MODE_DIRECT);

                        break;

                        case CarpoolItem::CREDITOR_STATUS_ONLINE:
                        case CarpoolItem::CREDITOR_STATUS_PENDING_ONLINE:
                            $carpoolExport->setMode(CarpoolExport::MODE_ONLINE);

                        break;
                    }
                } else {
                    switch ($carpoolItem->getDebtorStatus()) {
                        case CarpoolItem::DEBTOR_STATUS_DIRECT:
                        case CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT:
                            $carpoolExport->setMode(CarpoolExport::MODE_DIRECT);

                        break;

                        case CarpoolItem::DEBTOR_STATUS_ONLINE:
                        case CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE:
                            $carpoolExport->setMode(CarpoolExport::MODE_ONLINE);

                        break;
                    }
                }
            }
            //    we set the role and the carpooler
            if ($isCreditor) {
                $carpoolExport->setRole(CarpoolExport::ROLE_DRIVER);
                $carpoolExport->setCarpooler($carpoolItem->getDebtorUser());
                if (0 !== $carpoolItem->getItemStatus()) {
                    $sumReceived = $sumReceived + $carpoolItem->getAmount();
                }
            } else {
                $carpoolExport->setRole(CarpoolExport::ROLE_PASSENGER);
                $carpoolExport->setCarpooler($carpoolItem->getCreditorUser());
                if (0 !== $carpoolItem->getItemStatus()) {
                    $sumPaid = $sumPaid + $carpoolItem->getAmount();
                }
            }
            if (is_null($carpoolItem->getAsk())) {
                $carpoolExports[] = $carpoolExport;

                continue;
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
                    // if ($carpoolProof->getPickUpPassengerDate() == $carpoolItem->getItemDate()) {
                    $carpoolExport->setCertification($carpoolProof->getType());
                    // }
                }
            }

            $carpoolExports[] = $carpoolExport;
        }

        // we put all infos needed in an array to build pdf
        $infoForPdf = [];
        $now = new DateTime();
        $infoForPdf['date'] = $now->format('l d F Y');
        $infoForPdf['year'] = new DateTime();
        $infoForPdf['twigPath'] = 'carpool/export/carpool_export.html.twig';
        // we sanitize username to use it in the fileName
        $sanitizeUserName = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
            ->transliterate($user->getGivenName().$user->getFamilyName())
        ;
        $infoForPdf['fileName'] = $now->format('YmdHis').$sanitizeUserName.'ListeDesCovoiturages.pdf';
        $infoForPdf['filePath'] = $this->carpoolExportPath;
        $infoForPdf['returnUrl'] = $this->carpoolExportUri.$infoForPdf['fileName'];
        $infoForPdf['userName'] = $user->getGivenName().' '.$user->getFamilyName();
        $infoForPdf['appName'] = $this->carpoolExportPlatformName;
        $infoForPdf['paid'] = $sumPaid;
        $infoForPdf['received'] = $sumReceived;
        $infoForPdf['tax'] = $sumReceived > 300 ? true : false;
        $infoForPdf['carpoolExports'] = $carpoolExports;
        $infoForPdf['paymentActive'] = $this->paymentActive;

        return $this->pdfManager->generatePDF($infoForPdf);
    }
}
