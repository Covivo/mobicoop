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

namespace App\Communication\Controller;

use App\Communication\Entity\Sms;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\DataProvider\Entity\SmsEnvoiProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * WARNING : Controller class for API communication testing purpose ONLY
 * DO NOT EXPOSE ANY ROUTE
 * WATCH AND AMEND THE CODE CAREFULLY TO BUILD CUSTOM TESTS
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
class TestController extends AbstractController
{
    private $templating;
    private $translator;
    private $smsProvider;
    
    
    public function __construct(Environment $templating, TranslatorInterface $translator, array $params)
    {
        $this->templating = $templating;
        $this->translator = $translator;
        $this->smsProvider = new SmsEnvoiProvider($params['smsUsername'], $params['smsPassword'], $params['smsSender']);
    }
    
    /**
     * Send a sms for testing purpose
     * @Route("/rd/communication/sms/{mobile}", name="testSMS")
     * @var string $mobile  The recipient mobile number
     * @return Response
     */
    public function testSMS(string $mobile): Response
    {
        $this->translator->setLocale("fr_FR");

        $templatePath = "sms/notification";

        $template = "test_sms";


        $context['message'] = "C'est un test";
        
        $sms = new Sms();
        $sms->setRecipientTelephone($mobile);
        $sms->setMessage(
            $this->templating->render(
                $templatePath.'/'.$template.'.html.twig',
                array(
                    'context' => $context,
                    'message' => str_replace(array("\r\n", "\r", "\n"), "<br />", $sms->getMessage()),
                )
            ),
            'text/html'
        );
        
        return $this->smsProvider->postCollection($sms);
    }
}
