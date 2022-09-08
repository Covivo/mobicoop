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

declare(strict_types=1);

namespace App\Command\Command;

use App\Communication\Event\InternalMessageReceivedRelaunch1Event;
use App\Communication\Repository\MessageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommunicationInternalMessageReceivedRelaunch1Command extends Command
{
    public const RELAUNCH_DELAY = 2;
    private $messageRepository;
    private $eventDispatcher;

    public function __construct(MessageRepository $messageRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:communication-internal-message-received-relaunch1')
            ->setDescription('CommunicationInternalMessageReceivedRelaunch1Command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messagesIds = $this->messageRepository->findNotAnsweredMessagesSinceXDays(self::RELAUNCH_DELAY);

        if (count($messagesIds) > 0) {
            foreach ($messagesIds as $messageId) {
                $message = $this->messageRepository->find(intval($messageId['id']));
                foreach ($message->getRecipients() as $recipient) {
                    $event = new InternalMessageReceivedRelaunch1Event($recipient);
                    $this->eventDispatcher->dispatch(InternalMessageReceivedRelaunch1Event::NAME, $event);
                }
            }
        }

        return 0;
    }
}
