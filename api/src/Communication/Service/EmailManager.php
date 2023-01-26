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
 */

namespace App\Communication\Service;

use App\Communication\Entity\Email;
use App\Communication\Ressource\ContactType;

use function GuzzleHttp\json_decode;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Email sending service via Swift_Mailer.
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class EmailManager
{
    public const LANG = 'fr';
    private $mailer;
    private $templating;
    private $emailSenderDefault;
    private $emailSenderNameDefault;
    private $emailReplyToDefault;
    private $emailReplyToNameDefault;
    private $logger;
    private $translator;
    private $emailAdditionalHeaders;
    private $emailSupport;

    /**
     * EmailManager constructor.
     */
    public function __construct(\Swift_Mailer $mailer, Environment $templating, LoggerInterface $logger, TranslatorInterface $translator, string $emailSender, string $emailSenderName, string $emailReplyTo, string $emailReplyToName, string $emailAdditionalHeaders)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->emailSenderDefault = $emailSender;
        $this->emailSenderNameDefault = $emailSenderName;
        $this->emailReplyToDefault = $emailReplyTo;
        $this->emailReplyToNameDefault = $emailReplyToName;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->emailAdditionalHeaders = $emailAdditionalHeaders;
    }

    /**
     * Send an email.
     *
     * @param Email  $mail        the email to send
     * @param string $template    the email's template
     * @param array  $context     optional array of parameters that can be included in the template
     * @param mixed  $lang
     * @param array  $attachments Array of string path to files. The files will be send as attachements
     *
     * @return string
     */
    public function send(Email $mail, $template, $context = [], $lang = 'fr', $attachments = [])
    {
        $failures = '';

        // reply
        if (is_null($mail->getReturnEmail()) || '' === trim($mail->getReturnEmail())) {
            $replyToEmail = $this->emailReplyToDefault;
        } else {
            $replyToEmail = $mail->getReturnEmail();
        }

        $sessionLocale = $this->translator->getLocale();

        if (self::LANG == $lang) {
            $this->translator->setLocale($lang);
        } else {
            $this->translator->setLocale($lang->getCode());
        }

        $senderName = ('' !== $this->emailSenderNameDefault) ? $this->emailSenderNameDefault : $this->emailSenderDefault;
        $message = (new \Swift_Message($mail->getObject()))
            ->setFrom($this->emailSenderDefault, $senderName)
            ->setTo($mail->getRecipientEmail())
            ->setReplyTo($replyToEmail)
            ->setBody(
                $this->templating->render(
                    $template.'.html.twig',
                    [
                        'context' => $context,
                        'message' => str_replace(["\r\n", "\r", "\n"], '<br />', $mail->getMessage()),
                    ]
                ),
                'text/html'
            )
        ;

        // We check if we have to send the email to Bcc or Cc recipients
        if ($mail->getRecipientEmailBcc()) {
            $message->setBcc($mail->getRecipientEmailBcc());
        }
        if ($mail->getRecipientEmailCc()) {
            $message->setCc($mail->getRecipientEmailCc());
        }

        // we send the email with a specific textheader if the reciepient is the support's email and if specific header is present
        if ($this->emailAdditionalHeaders && isset($context['contact']) && !is_null($context['contact']) && !is_null($context['contact']->getContactType()) && ContactType::TYPE_SUPPORT == $context['contact']->getContactType()->getDemand()) {
            $headers = json_decode($this->emailAdditionalHeaders, true);
            foreach ($headers as $key => $value) {
                if ('senderEmail' == $this->translator->trans($value)) {
                    $data = $mail->getSenderEmail();
                } elseif ('senderName' == $this->translator->trans($value)) {
                    $data = $mail->getSenderName().' '.$mail->getSenderFirstName();
                } else {
                    $data = $this->translator->trans($value);
                }
                $message->getHeaders()->addTextHeader($this->translator->trans($key), $data);
            }
        }

        foreach ($attachments as $attachment) {
            $message->attach(\Swift_Attachment::fromPath($attachment));
        }

        $this->translator->setLocale($sessionLocale);

        return $this->mailer->send($message, $failures);
    }
}
