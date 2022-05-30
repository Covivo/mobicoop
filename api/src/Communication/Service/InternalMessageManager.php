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
 */

namespace App\Communication\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Communication\Event\InternalMessageReceivedEvent;
use App\Communication\Exception\MessageException;
use App\Communication\Interfaces\MessagerInterface;
use App\Communication\Repository\MediumRepository;
use App\Communication\Repository\MessageRepository;
use App\Solidary\Entity\SolidaryAskHistory;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Internal message manager.
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
    private $storeReadDate;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        MediumRepository $mediumRepository,
        LoggerInterface $logger,
        MessageRepository $messageRepository,
        bool $storeReadDate
    ) {
        $this->entityManager = $entityManager;
        $this->mediumRepository = $mediumRepository;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageRepository = $messageRepository;
        $this->storeReadDate = $storeReadDate;
    }

    /**
     * Sends an internal message to recipients, related to an object (the message itself already exists and is linked to the object).
     *
     * @param array             $recipients The recipients
     * @param MessagerInterface $object     The object linked
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
            $this->eventDispatcher->dispatch($event, InternalMessageReceivedEvent::NAME);
        }
    }

    /**
     * Sends an new internal message from a sender to recipients.
     *
     * @param User         $sender     The sender
     * @param array        $recipients The recipients
     * @param string       $text       The text of the message
     * @param null|string  $title      The title of the message
     * @param null|Message $reply      The original message if the current message is a reply
     */
    public function send(User $sender, array $recipients, string $text, ?string $title = null, ?Message $reply)
    {
        $message = $this->createMessage($sender, $recipients, $text, $title, $reply);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // the message has been sent, we browse the recipients again to send the event
        foreach ($message->getRecipients() as $recipient) {
            // dispatch en event
            $event = new InternalMessageReceivedEvent($recipient);
            $this->eventDispatcher->dispatch($event, InternalMessageReceivedEvent::NAME);
        }
    }

    /**
     * @throws \Exception
     *
     * @return Message
     */
    public function createMessage(User $sender, array $recipients, string $text, ?string $title = null, ?Message $reply): Message
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
     * Get a complete message thread.
     *
     * @param int  $idMessage The message we want the thread
     * @param bool $checkRead If true, we check the current message as read (can be override in .env)
     * @param int  $userId    Id of the requester. Usefull if checkRead is true
     */
    public function getCompleteThread(int $idMessage, bool $checkRead = false, int $userId = null)
    {
        $message = $this->messageRepository->find($idMessage);
        if (empty($message)) {
            throw new MessageException(MessageException::NOT_FOUND);
        }
        $messages = array_merge([$message], $message->getMessages());

        // getCompleteThread is called in various ways that does'nt require that the read status be updated.
        // For example in, UserManager -> getProfileSummary
        if ($this->storeReadDate && $checkRead) {
            foreach ($messages as $currentMessage) {
                foreach ($currentMessage->getRecipients() as $recipient) {
                    if (is_null($userId)) {
                        throw new \LogicException('No user specified');
                    }
                    $userRecipientId = $recipient->getUser()->getId();
                    // We set a read date only if the recipient userid is the requester id
                    if ($userId == $userRecipientId) {
                        $recipient->setReadDate(new \DateTime('now'));
                    }
                    $this->entityManager->persist($currentMessage);
                }
            }
            $this->entityManager->flush();
        }

        return $messages;
    }

    public function getMessage($idMessage)
    {
        return $this->messageRepository->find($idMessage);
    }

    /**
     * Post a Message, taking care of Ask and SolidaryAsk if needed.
     *
     * @return Message
     */
    public function postMessage(Message $message): Message
    {
        // This message is related to an Ask
        if (null !== $message->getIdAsk()) {
            // We get the infos of the Ask
            $ask = $this->entityManager->getRepository(Ask::class)->find($message->getIdAsk());

            // Create the new AskHistory
            $askHistory = new AskHistory();

            $askHistory->setMessage($message);
            $askHistory->setAsk($ask);
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());
            $this->entityManager->persist($askHistory);

            // If there is a SolidaryAsk, we create the new SolidaryAskHistory
            if (!is_null($ask->getSolidaryAsk())) {
                $solidaryAskHistory = new SolidaryAskHistory();
                $solidaryAskHistory->setMessage($message);
                $solidaryAskHistory->setSolidaryAsk($ask->getSolidaryAsk());
                $solidaryAskHistory->setStatus($ask->getSolidaryAsk()->getStatus());
                $this->entityManager->persist($solidaryAskHistory);
            }
        } else {
            // No Ask, just persist the message
            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();

        return $message;
    }
}
