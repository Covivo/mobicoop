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

namespace Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Controller;

use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Service\MessageManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    use HydraControllerTrait;

    private $messageManager;
    private $userManager;
    private $operatorIdentifier;

    public function __construct(MessageManager $messageManager, UserManager $userManager, string $operatorIdentifier)
    {
        $this->messageManager = $messageManager;
        $this->userManager = $userManager;
        $this->operatorIdentifier = $operatorIdentifier;
    }

    public function sendExternalMessage(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $message = new Message();
            $to = new User();
            $from = new User();

            $from->setExternalId($this->userManager->getLoggedUser()->getId());
            $from->setAlias($this->userManager->getLoggedUser()->getGivenName().' '.$this->userManager->getLoggedUser()->getShortFamilyName());
            $from->setFirstName($this->userManager->getLoggedUser()->getGivenName());
            $from->setLastName($this->userManager->getLoggedUser()->getFamilyName());
            $from->setOperator($this->operatorIdentifier);

            $message->setFrom($from);

            $to->setExternalId($data['to']['externalId']);
            $to->setAlias($data['to']['alias']);
            $to->setOperator($data['to']['operator']);

            $message->setTo($to);
            $message->setMessage($data['text']);

            $message->setRecipientCarpoolerType($data['recipientCarpoolerType']);
            $message->setBookingId($data['bookingId']);
            $message->setDriverJourneyId($data['driverJourneyId']);
            $message->setPassengerJourneyId($data['passengerJourneyId']);

            return new Response($this->messageManager->postCarpoolStandardMessage($message));
        }

        return new Response(json_encode('Not a post'));
    }
}
