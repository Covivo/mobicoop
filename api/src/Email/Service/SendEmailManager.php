<?php

namespace App\Email\Service;

use App\Email\Entity\Email;

/**
 * Service d'envoi d'email via Swift_Mailer
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class SendEmailManager
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }



    /**
     * Envoi un mail
     * @param Mail $email le mail à envoyer
     * @param $template le template du mail
     * @param $varOpt tableau array pour des infos en plus
     * @return string
     */
    public function sendEmail(Email $mail, $template, $varOpt=[])
    {
        $failures = "";

        $message = (new \Swift_Message($mail->getObject()))
            ->setFrom($mail->getSenderEmail())
            ->setTo($mail->getRecipientEmail())
            ->setBody(
                $this->templating->render(
                    'emails/'.$template,
                    array(
                        'email' => $mail->getSenderEmail(),
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
