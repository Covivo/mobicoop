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

use App\Communication\Entity\Email;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function GuzzleHttp\json_decode;

/**
 * Email sending service via Swift_Mailer
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class EmailManager
{
    private $mailer;
    private $templating;
    private $emailSenderDefault;
    private $emailSenderNameDefault;
    private $emailReplyToDefault;
    private $emailReplyToNameDefault;
    private $templatePath;
    private $logger;
    private $translator;
    private $emailAdditionalHeaders;
    private $emailSupport;
 
    /**
       * EmailManager constructor.
       * @param \Swift_Mailer $mailer
       * @param Environment $templating
       * @param LoggerInterface $logger
       * @param TranslatorInterface $translator
       * @param string $emailSender
       * @param string $emailSenderName
       * @param string $emailReplyTo
       * @param string $emailReplyToName
       * @param string $templatePath
       * @param string $emailAdditionalHeaders
       * @param string $emailSupport
       */
    public function __construct(\Swift_Mailer $mailer, Environment $templating, LoggerInterface $logger, TranslatorInterface $translator, string $emailSender, string $emailSenderName, string $emailReplyTo, string $emailReplyToName, string $templatePath, string $emailAdditionalHeaders, string $emailSupport)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->emailSenderDefault = $emailSender;
        $this->emailSenderNameDefault = $emailSenderName;
        $this->emailReplyToDefault = $emailReplyTo;
        $this->emailReplyToNameDefault = $emailReplyToName;
        $this->templatePath = $templatePath;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->emailAdditionalHeaders = $emailAdditionalHeaders;
        $this->emailSupport = $emailSupport;
    }

    /**
     * Send an email
     * @param Email $mail the email to send
     * @param string $template the email's template
     * @param array $context optional array of parameters that can be included in the template
     * @return string
     */
    public function send(Email $mail, $template, $context=[], $lang='fr_FR')
    {
        $failures = "";
        // sender
        if (is_null($mail->getSenderEmail()) || trim($mail->getSenderEmail()) === "") {
            $senderEmail = $this->emailSenderDefault;
        } else {
            $senderEmail = $mail->getSenderEmail();
        }

        // reply
        if (is_null($mail->getReturnEmail()) || trim($mail->getReturnEmail()) === "") {
            $replyToEmail = $this->emailReplyToDefault;
        } else {
            $replyToEmail = $mail->getReturnEmail();
        }
        
        $sessionLocale= $this->translator->getLocale();
        $this->translator->setLocale($lang);
       
        
        $senderName = ($this->emailSenderNameDefault!=="") ? $this->emailSenderNameDefault : $senderEmail;
        $senderReplyToName = ($this->emailReplyToNameDefault!=="") ? $this->emailReplyToNameDefault : $replyToEmail;
        $message = (new \Swift_Message($mail->getObject()))
            ->setFrom($senderEmail, $senderName)
            ->setTo($mail->getRecipientEmail())
            ->setReplyTo($replyToEmail, $senderReplyToName)
            ->setBody(
                $this->templating->render(
                    $this->templatePath.$template.'.html.twig',
                    array(
                        'context' => $context,
                        'message' => str_replace(array("\r\n", "\r", "\n"), "<br />", $mail->getMessage()),
                    )
                ),
                'text/html'
            );
        // we send the email with a specific textheader if the reciepient is the support's email and if specific header is present
        if ($this->emailAdditionalHeaders && $mail->getRecipientEmail() == $this->emailSupport) {
            $headers = json_decode($this->emailAdditionalHeaders, true);
            foreach ($headers as $key => $value) {
                if ($this->translator->trans($value) == "senderEmail") {
                    $data = $mail->getSenderEmail();
                } elseif ($this->translator->trans($value) == "senderName") {
                    $data = $mail->getSenderName()." ".$mail->getSenderFirstName();
                } else {
                    $data = $this->translator->trans($value);
                }
                $message->getHeaders()->addTextHeader($this->translator->trans($key), $data);
            }
        }
       
        $this->translator->setLocale($sessionLocale);
        $failures = $this->mailer->send($message, $failures);
        
        return $failures;
    }
}
