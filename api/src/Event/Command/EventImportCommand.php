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
 */

namespace App\Event\Command;

use App\Event\Service\EventManager;
use App\Import\Admin\Controller\ImportEventsAction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate carpool payment items from the accepted asks.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class EventImportCommand extends Command
{
    public const EVENT_PROVIDER_FILE_TYPE = 'file';
    public const EVENT_PROVIDER_API_TYPE = 'API';
    private $eventManager;
    private $eventImportEnabled;
    private $eventProviderType;
    private $_importEventsAction;

    public function __construct(EventManager $eventManager, bool $eventImportEnabled, string $eventProviderType, ImportEventsAction $importEventsAction)
    {
        $this->eventManager = $eventManager;
        $this->eventImportEnabled = $eventImportEnabled;
        $this->eventProviderType = $eventProviderType;
        $this->_importEventsAction = $importEventsAction;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:events:import')
            ->setDescription('Import events from provider')
            ->setHelp('Create events from external provider')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (false == $this->eventImportEnabled) {
            return 0;
        }

        switch ($this->eventProviderType) {
            case self::EVENT_PROVIDER_API_TYPE:
                $this->eventManager->importEvents();

                return 0;

                break;

            case self::EVENT_PROVIDER_FILE_TYPE:
                $this->_importEventsAction->importEventsFromFile();

                return 0;

                break;
        }
    }
}
