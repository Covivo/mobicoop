<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\User\Command;

use App\Payment\Entity\CarpoolItem;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\DataProvider\ConsumptionFeedbackDataProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Send a consumption feedback for a given CarpoolItem
 * FOR DEV PURPOSE ONLY (the real sending occured during capool item generation).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserSendConsumptionFeedbackCommand extends Command
{
    private $_consumptionFeedbackDataProvider;
    private $_carpoolItemRepository;

    public function __construct(ConsumptionFeedbackDataProvider $consumptionFeedbackDataProvider, CarpoolItemRepository $carpoolItemRepository)
    {
        $this->_consumptionFeedbackDataProvider = $consumptionFeedbackDataProvider;
        $this->_carpoolItemRepository = $carpoolItemRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:user:test-consumption-feedback')
            ->addArgument('carpoolItemId', InputArgument::REQUIRED, 'The CarpoolItem id to generate feedback')
            ->setDescription('Send a consumption feedback for a given CarpoolItem. FOR DEV PURPOSE ONLY (the real sending occured during capool item generation)')
            ->setHelp('Send a consumption feedback for a given CarpoolItem. FOR DEV PURPOSE ONLY (the real sending occured during capool item generation)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->_consumptionFeedbackDataProvider->isActive()) {
            return;
        }
        $this->_consumptionFeedbackDataProvider->auth();

        if ($carpoolItem = $this->_carpoolItemRepository->find($input->getArgument('carpoolItemId'))) {
            $this->_consumptionFeedbackDataProvider->setConsumptionCarpoolItem($carpoolItem);
            $this->_consumptionFeedbackDataProvider->sendConsumptionFeedback();
        }
    }
}
