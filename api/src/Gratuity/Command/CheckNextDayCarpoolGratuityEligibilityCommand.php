<?php

/**
 * Copyright (c) 2026, MOBICOOP. All rights reserved.
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

namespace App\Gratuity\Command;

use App\Carpool\Entity\Ask;
use App\Carpool\Repository\AskRepository;
use App\Communication\Service\NotificationManager;
use App\Gratuity\Service\GratuityCampaignActionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check next day carpools for gratuity eligibility and send reminder notifications.
 */
class CheckNextDayCarpoolGratuityEligibilityCommand extends Command
{
    private $askRepository;
    private $gratuityCampaignActionManager;
    private $notificationManager;

    public function __construct(
        AskRepository $askRepository,
        GratuityCampaignActionManager $gratuityCampaignActionManager,
        NotificationManager $notificationManager
    ) {
        parent::__construct();

        $this->askRepository = $askRepository;
        $this->gratuityCampaignActionManager = $gratuityCampaignActionManager;
        $this->notificationManager = $notificationManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:check-next-day-carpool-gratuity-eligibility')
            ->setDescription('Sends push notifications to users with carpools scheduled for tomorrow that are eligible for gratuity campaigns')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tomorrow = new \DateTime('tomorrow');

        $output->writeln('Checking carpools for date: '.$tomorrow->format('Y-m-d'));

        $asks = $this->askRepository->findAcceptedAsksForPeriod($tomorrow, $tomorrow);

        $output->writeln('Found '.count($asks).' accepted asks for tomorrow');

        $notificationsSent = 0;
        $processedAskIds = [];

        foreach ($asks as $ask) {
            // Avoid processing the same ask twice (in case of duplicates)
            if (in_array($ask->getId(), $processedAskIds)) {
                continue;
            }
            $processedAskIds[] = $ask->getId();

            $addresses = $this->extractAddressesFromAsk($ask);

            if (empty($addresses)) {
                continue;
            }

            $driver = $this->getDriverFromAsk($ask);
            $passenger = $this->getPassengerFromAsk($ask);

            if (null === $driver || null === $passenger) {
                continue;
            }

            $campaigns = $this->gratuityCampaignActionManager->findCampaignsByAddresses($addresses, $driver);

            if (!empty($campaigns)) {
                $this->notificationManager->notifies('gratuity_remind_to_certify_next_day_carpool', $driver);
                $this->notificationManager->notifies('gratuity_remind_to_certify_next_day_carpool', $passenger);
                $notificationsSent += 2;

                $output->writeln('Notification sent for ask #'.$ask->getId().' to driver #'.$driver->getId().' and passenger #'.$passenger->getId());
            }
        }

        $output->writeln('Total notifications sent: '.$notificationsSent);

        return 0;
    }

    private function extractAddressesFromAsk(Ask $ask): array
    {
        $addresses = [];
        $waypoints = $ask->getWaypoints();

        foreach ($waypoints as $waypoint) {
            $address = $waypoint->getAddress();
            if (null !== $address && null !== $address->getLatitude() && null !== $address->getLongitude()) {
                $addresses[] = [
                    'latitude' => $address->getLatitude(),
                    'longitude' => $address->getLongitude(),
                ];
            }
        }

        return $addresses;
    }

    private function getDriverFromAsk(Ask $ask)
    {
        if (Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus()) {
            return $ask->getUserRelated();
        }

        return $ask->getUser();
    }

    private function getPassengerFromAsk(Ask $ask)
    {
        if (Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus()) {
            return $ask->getUser();
        }

        return $ask->getUserRelated();
    }
}
