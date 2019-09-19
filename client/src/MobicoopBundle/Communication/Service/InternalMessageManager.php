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

namespace Mobicoop\Bundle\MobicoopBundle\Communication\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider as MobicoopDataProvider;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Recipient;

/**
 * Internal message management service.
 */
class InternalMessageManager
{
    private $dataProvider;
    private $userManager;

    /**
    * Constructor.
    * @param DataProvider $dataProvider The data provider that provides the Message
    */
    public function __construct(DataProvider $dataProvider, UserManager $userManager)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Message::class);
        $this->userManager = $userManager;
    }

    /**
     * Get a message
     *
     * @param int $idMessage Id of the message
     *
     */
    public function getMessage(int $idMessage)
    {
        $response = $this->dataProvider->getItem($idMessage);
        return $response->getValue();
    }
    
    /**
     * Get complete thread from a message
     *
     * @param int $id       The first message id
     * @param int $format   The format to use
     *
     * @return array|null The complete thread
     */
    public function getThread(int $id, int $format=null)
    {
        if ($format!==null) {
            $this->dataProvider->setFormat($format);
        }
        $response = $this->dataProvider->getSubCollection($id, Message::class, "thread");

        return $response->getValue();

        return null;
    }

    /**
     * Send an internal message
     *
     * @param Message   $message    The message to send
     * @param int       $format     The format to use
     *
     */
    public function sendInternalMessage(Message $message, int $format=null)
    {
        if ($format!==null) {
            $this->dataProvider->setFormat($format);
        }
        $response = $this->dataProvider->post($message);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Create an internal message
     *
     * @return Message
     */
    public function createInternalMessage(User $sender, int $idUserRecipient, $title, $text, $idThreadMessage=null)
    {
        $messageToSend = new Message();
        $messageToSend->setUser($sender);

        $recipient = new Recipient();
        $recipient->setUser(new User($idUserRecipient));

        $recipient->setStatus(Recipient::STATUS_PENDING);
        $recipient->setSentDate(new \DateTime());
        $messageToSend->addRecipient($recipient);

        $messageToSend->setTitle($title);
        $messageToSend->setText($text);
        
        if ($idThreadMessage!==null) {
            $messageToSend->setMessage(new Message($idThreadMessage));
        }

        return $messageToSend;
    }
}
