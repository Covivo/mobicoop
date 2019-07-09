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

namespace App\Communication\Service;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Communication\Entity\Medium;
use App\Communication\Repository\MediumRepository;
use App\User\Entity\User;

/**
 * Internal message manager
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class InternalMessageManager
{
    private $entityManager;
    private $mediumRepository;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, MediumRepository $mediumRepository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->mediumRepository = $mediumRepository;
        $this->logger = $logger;
    }

    /**
     * Sends an internal message to recipients, related to an object (the message itself already exists and is linked to the object)
     *
     * @param array $recipients     The recipients
     * @param object $object        The object linked
     * @return void
     */
    public function sendForObject(array $recipients, object $object)
    {
        if (method_exists($object, "getMessage")) {
            if ($object->getMessage() instanceof Message) {
                $this->sendExisting($recipients, $object->getMessage());
            }
        }
    }

    /**
     * Sends an already created internal message from a sender to recipients
     *
     * @param array $recipients     The recipients
     * @param Message $message      The message
     * @return void
     */
    private function sendExisting(array $recipients, Message $message)
    {
        $medium = $this->mediumRepository->find(Medium::MEDIUM_MESSAGE);
        foreach ($recipients as $userRecipient) {
            $recipient = new Recipient();
            $recipient->setMedium($medium);
            $recipient->setUser($userRecipient);
            $recipient->setStatus(Recipient::STATUS_PENDING);
            $message->addRecipient($recipient);
            $this->entityManager->persist($recipient);
        }
    }

    /**
     * Sends an new internal message from a sender to recipients
     *
     * @param User          $sender         The sender
     * @param array         $recipients     The recipients
     * @param string        $text           The text of the message
     * @param string|null   $title          The title of the message
     * @param Message|null  $reply          The original message if the current message is a reply
     * @return void
     */
    private function send(User $sender, array $recipients, string $text, ?string $title=null, ?Message $reply)
    {
        $message = new Message();
        $message->setUser($sender);
        $message->setText($text);
        if ($title) {
            $message->setTitle($title);
        }
        if ($reply) {
            $message->setMessage($reply);
        }
        foreach ($recipients as $recipient) {
            $orecipient = new Recipient();
            $orecipient->setUser($recipient);
            
        }
    }
}
