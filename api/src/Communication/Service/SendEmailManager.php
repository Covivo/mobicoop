<?php

namespace App\Communication\Service;

use App\Communication\Entity\Email;

/**
 * Sending email service via Swift_Mailer
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class SendEmailManager
{
    private $mailer;
    private $templating;
    private $emailSenderDefault;
    private $emailReplyToDefault;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating, string $emailSender, string $emailReplyTo)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->emailSenderDefault = $emailSender;
        $this->emailReplyToDefault = $emailReplyTo;
    }



    /**
     * Send an email
     * @param Email $mail the email to send
     * @param string $template the email's template
     * @param array $varOpt optionnal informations that can be included in the template
     * @return string
     */
    public function sendEmail(Email $mail, $template, $varOpt=[])
    {
        $failures = "";

        // Traitement de l'expéditeur
        if (is_null($mail->getSenderEmail()) || trim($mail->getSenderEmail()) === "") {
            $senderEmail = $this->emailSenderDefault;
        } else {
            $senderEmail = $mail->getSenderEmail();
        }

        // Traitement du reply
        if (is_null($mail->getReturnEmail()) || trim($mail->getReturnEmail()) === "") {
            $replyToEmail = $this->emailReplyToDefault;
        } else {
            $replyToEmail = $mail->getReturnEmail();
        }

        $message = (new \Swift_Message($mail->getObject()))
            ->setFrom($senderEmail)
            ->setTo($mail->getRecipientEmail())
            ->setReplyTo($replyToEmail)
            ->setBody(
                $this->templating->render(
                    'Emails/'.$template,
                    array(
                        'varOpt' => $varOpt,
                        'message' => str_replace(array("\r\n", "\r", "\n"), "<br />", $mail->getMessage()),
                    )
                ),
                'text/html'
            );

        $failures = $this->mailer->send($message, $failures);


        return $failures;
    }
}
