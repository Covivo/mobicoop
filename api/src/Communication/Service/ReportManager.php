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
 **************************/


namespace App\Communication\Service;

use App\Communication\Entity\Email;
use App\Communication\Ressource\ContactType;
use App\Communication\Ressource\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ReportManager
{
    const LANG = "fr_FR";
    
    private $emailManager;
    private $contactManager;
    private $templating;
    private $supportEmailAddress;
    private $emailTemplatePath;
    private $emailTitleTemplatePath;
    private $translator;
    private $lang;

    public function __construct(
        TranslatorInterface $translator,
        EmailManager $emailManager,
        ContactManager $contactManager,
        Environment $templating,
        string $emailTemplatePath,
        string $emailTitleTemplatePath
    ) {
        $this->translator = $translator;
        $this->emailManager = $emailManager;
        $this->contactManager = $contactManager;
        $this->templating = $templating;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->emailTitleTemplatePath = $emailTitleTemplatePath;

        $this->lang = self::LANG;
        $this->translator->setLocale($this->lang);
    }
    
    /**
     * Create a Report
     *
     * @param Report $report    The report to create
     * @return Report
     */
    public function createReport(Report $report): Report
    {
        if (!is_null($report->getUser())) {
            $this->reportUser($report);
        }
        if (!is_null($report->getEvent())) {
            $this->reportEvent($report);
        }
        
        return $report;
    }

    /**
     * Report a User
     *
     * @param Report $report    The report to create
     * @return Report
     */
    private function reportUser(Report $report): Report
    {
        $bodyContext = ['text'=>$report->getText(), 'reporterEmail'=> $report->getReporterEmail(), 'user' => $report->getUser()];

        $this->sendEmailReport("reportUser", "reportUser", [], $bodyContext);

        return $report;
    }

    /**
     * Report an Event
     *
     * @param Report $report    The report to create
     * @return Report
     */
    private function reportEvent(Report $report): Report
    {
        $bodyContext = ['text'=>$report->getText(), 'reporterEmail'=> $report->getReporterEmail(), 'eventName' => $report->getEvent()->getName()];

        $this->sendEmailReport("reportEvent", "reportEvent", [], $bodyContext);

        return $report;
    }

    /**
     * Send an Email report
     *
     * @param string $templateTitle Name of the twig template of the body
     * @param string $templateBody  Name of the twig template of the titla
     * @param array $titleContext Title context
     * @param array $bodyContext  Body context
     * @return void
     */
    private function sendEmailReport(string $templateTitle, string $templateBody, array $titleContext=[], array $bodyContext=[])
    {
        $email = new Email();

        // Get the support emails
        $contactType = $this->contactManager->getEmailsByType(ContactType::TYPE_SUPPORT);

        // Recipients
        if (is_array($contactType->getTo()) && count($contactType->getTo())>0) {
            $email->setRecipientEmail($contactType->getTo());
        }
        if (is_array($contactType->getCc()) && count($contactType->getCc())>0) {
            $email->setRecipientEmailCc($contactType->getCc());
        }
        if (is_array($contactType->getBcc()) && count($contactType->getBcc())>0) {
            $email->setRecipientEmailBcc($contactType->getBcc());
        }
        
        $email->setObject($this->templating->render(
            $this->emailTitleTemplatePath . $templateTitle.'.html.twig',
            [
                'context' => $titleContext
            ]
        ));

        // if a template is associated with the action in the notification, we us it; otherwise we try the name of the action as template name
        $this->emailManager->send($email, $this->emailTemplatePath . $templateBody, $bodyContext, $this->lang);
    }
}
