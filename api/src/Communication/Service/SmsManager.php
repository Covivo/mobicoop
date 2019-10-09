<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

use App\Communication\Entity\Sms;
use App\DataProvider\Entity\SmsEnvoiProvider;
use Psr\Log\LoggerInterface;

/**
 * Sms sending service via SmsEnvoi
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class SmsManager
{
    private $templating;
    private $templatePath;
    private $logger;
  
    /**
     * SmsManager constructor.
     *
     * @param \Twig_Environment $templating
     * @param LoggerInterface $logger
     * @param SmsEnvoiProvider $smsEnvoiProvider
     * @param string $templatePath
     */
    public function __construct(\Twig_Environment $templating, LoggerInterface $logger, SmsEnvoiProvider $smsEnvoiProvider, string $templatePath)
    {
        $this->templating = $templating;
        $this->templatePath = $templatePath;
        $this->logger = $logger;
        $this->smsEnvoiProvider= $smsEnvoiProvider;
    }

    /**
     * Send a sms
     * @param Sms $sms the sms to send
     * @param string $template the sms's template
     * @param array $context optional array of parameters that can be included in the template
     * @return string
     */
    public function send(Sms $sms, $template, $context=[])
    {
        $sms->setMessage(
            $this->templating->render(
                $this->templatePath.$template.'.html.twig',
                array(
                    'context' => $context,
                    'message' => str_replace(array("\r\n", "\r", "\n"), "<br />", $sms->getMessage()),
                )
            ),
            'text/html'
        );

        // to do send sms via smsEnvoi
        
        $this->logger->info('on est ici');
        

        return;
    }
}
