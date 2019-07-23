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

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Diactoros\Response\EmptyResponse;

class DefaultController extends AbstractController
{
    /**
     * HomePage
     * @Route("/", name="home")
     *
     */
    public function index()
    {
        $baseUri = $_ENV['API_URI'];
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $baseUri,
                'metaDescription' => 'Homepage of Mobicoop'
            ]
        );
    }
 
 /**
	* HomePage
	* @Route("/testmail", name="testmail")
	*
	*/
 public function indexMail(\Swift_Mailer $mailer)
 {
//	ini_set( 'display_errors', 1 );
//	error_reporting( E_ALL );
//	$from = "emailtest@YOURDOMAIN";
//	$to = "ngouffodoric@gmail.com";
//	$subject = "PHP Mail Test script";
//	$message = "This is a test to check the PHP Mail functionality";
//	$headers = "From:" . $from;
//	echo mail($to,$subject,$message, $headers);
	$message = (new \Swift_Message('Hello Email'))
		->setFrom('send@example.com')
		->setTo('recipient@example.com')
		->setBody('test');

	$mailer->send($message);
	 return  new Response(
		 'Content',
		 Response::HTTP_OK,
		 ['content-type' => 'text/html']
	 );
 }
}
