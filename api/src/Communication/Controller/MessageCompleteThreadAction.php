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

namespace App\Communication\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Communication\Service\InternalMessageManager;
use App\Communication\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Controller class for user threads (list of messages as sender or recipient).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MessageCompleteThreadAction
{
    private $internalMessageManager;

    public function __construct(InternalMessageManager $internalMessageManager)
    {
        $this->internalMessageManager = $internalMessageManager;
    }

    /**
     * This method is invoked when the complete thread is asked.
     *
     * @param Message $message
     * @return array
     */
    public function __invoke(Message $message): ?array
    {
        // we search the messages
        //$data->setThreads($this->userManager->getThreads($data));
        return $this->internalMessageManager->getThreadMessages($message);
    }
}
