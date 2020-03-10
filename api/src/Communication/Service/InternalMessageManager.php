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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Communication\Event\InternalMessageReceivedEvent;
use App\Communication\Exception\MessageNotFoundException;
use App\Communication\Interfaces\MessagerInterface;
use App\Communication\Repository\MessageRepository;

/**
 * Internal message manager
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class InternalMessageManager
{
    private $entityManager;
    private $mediumRepository;
    private $messageRepository;
    private $eventDispatcher;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, MediumRepository $mediumRepository, LoggerInterface $logger, MessageRepository $messageRepository)
    {
        $this->entityManager = $entityManager;
        $this->mediumRepository = $mediumRepository;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Sends an internal message to recipients, related to an object (the message itself already exists and is linked to the object)
     *
     * @param array $recipients             The recipients
     * @param MessagerInterface $object     The object linked
     * @return void
     */
    public function sendForObject(array $recipients, MessagerInterface $object)
    {
        foreach ($recipients as $userRecipient) {
            $recipient = new Recipient();
            $recipient->setUser($userRecipient);
            $recipient->setStatus(Recipient::STATUS_PENDING);
            $object->getMessage()->addRecipient($recipient);
            $this->entityManager->persist($recipient);
            // dispatch en event
            $event = new InternalMessageReceivedEvent($recipient);
            $this->eventDispatcher->dispatch(InternalMessageReceivedEvent::NAME, $event);
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
    public function send(User $sender, array $recipients, string $text, ?string $title=null, ?Message $reply)
    {
        $message = $this->createMessage($sender, $recipients, $text, $title, $reply);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // the message has been sent, we browse the recipients again to send the event
        foreach ($message->getRecipients() as $recipient) {
            // dispatch en event
            $event = new InternalMessageReceivedEvent($recipient);
            $this->eventDispatcher->dispatch(InternalMessageReceivedEvent::NAME, $event);
        }
    }

    /**
     * @param User $sender
     * @param array $recipients
     * @param string $text
     * @param string|null $title
     * @param Message|null $reply
     * @return Message
     * @throws \Exception
     */
    public function createMessage(User $sender, array $recipients, string $text, ?string $title=null, ?Message $reply)
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
            $orecipient->setStatus(Recipient::STATUS_PENDING);
            $orecipient->setSentDate(new \DateTime());
            $message->addRecipient($orecipient);
        }

        return $message;
    }

    /**
     * Get a complete message
     */
    public function getCompleteThread($idMessage)
    {
        $message = $this->messageRepository->find($idMessage);
        if (empty($message)) {
            throw new MessageNotFoundException("Message not found");
        }
        return  array_merge([$message], $message->getMessages());
    }
}
