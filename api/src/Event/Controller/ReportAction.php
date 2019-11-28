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

namespace App\Event\Controller;

use App\Communication\Entity\Email;
use App\Communication\Service\EmailManager;
use App\Event\Entity\Event;
use App\Event\Service\EventManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class ReportAction
{
    use TranslatorTrait;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @var string
     */
    private $emailTemplatePath;

    /**
     * @var Email
     */
    private $emailConfiguration;

    public function __construct(EventManager $eventManager, EmailManager $emailManager, string $supportEmail, string $senderEmail, string $emailTemplatePath)
    {
        $this->eventManager = $eventManager;
        $this->emailManager = $emailManager;
        $this->emailTemplatePath = $emailTemplatePath;

        $this->emailConfiguration = new Email();
        $this->emailConfiguration->setRecipientEmail($supportEmail);
        $this->emailConfiguration->setSenderEmail($senderEmail);
        $this->emailConfiguration->setObject("Signalement d'un événement");
    }

    public function __invoke(Request $request, int $id)
    {
        // CHECK EVENT
        $event = $this->eventManager->getEvent($id);
        if (is_null($event)) {
            throw new \InvalidArgumentException($this->translator->trans('bad event id is provided'));
        }

        $object = json_decode($request->getContent());
        if ($this->eventManager->canReport() && property_exists($object, 'email') && property_exists($object, 'description')) {
            // SEND MAIL
            $this->emailManager->send($this->emailConfiguration, $this->emailTemplatePath."event_report", [
                'eventName' => $event->getName(),
                'email' => $object->email,
                'description' => $object->description
            ]);

            return new Response();
        }

        return new Response('Unauthorized', 403);
    }
}
