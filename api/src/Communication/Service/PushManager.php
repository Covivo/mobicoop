<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

use App\Communication\Entity\Push;
use App\DataProvider\Entity\FirebaseProvider;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Push notification sending service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PushManager
{
    public const LANG = 'fr';
    private $templating;
    private $logger;
    private $pushProvider;
    private $translator;

    /**
     * PushManager constructor.
     *
     * @param Environment         $templating   The templating system
     * @param LoggerInterface     $logger       The logger
     * @param TranslatorInterface $translator   The translation system
     * @param string              $templatePath The templates path
     * @param string              $pushProvider The name of the push provider
     * @param string              $apiToken     The api token
     * @param string              $senderId     The sender id
     */
    public function __construct(Environment $templating, LoggerInterface $logger, TranslatorInterface $translator, string $pushProvider, string $apiToken, string $senderId)
    {
        $this->templating = $templating;
        $this->logger = $logger;
        $this->translator = $translator;

        switch ($pushProvider) {
            case 'Firebase':
            default:
                $this->pushProvider = new FirebaseProvider($apiToken, $senderId);

                break;
        }
    }

    /**
     * Send a push notification.
     *
     * @param Push   $push     The push notification to send
     * @param string $template The push notification template to use
     * @param array  $context  The optional array of parameters that can be included in the template
     * @param mixed  $lang
     */
    public function send(Push $push, $template, $context = [], $lang = 'fr')
    {
        $sessionLocale = $this->translator->getLocale();
        if (self::LANG == $lang) {
            $this->translator->setLocale($lang);
        } else {
            $this->translator->setLocale($lang->getCode());
        }
        $push->setMessage(
            $this->templating->render(
                $template.'.html.twig',
                [
                    'context' => $context,
                    'message' => str_replace(["\r\n", "\r", "\n"], '<br />', $push->getMessage()),
                ]
            ),
            'text/html'
        );
        $this->translator->setLocale($sessionLocale);

        // send the push notification
        $this->pushProvider->postCollection($push);
    }
}
