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
 *    along with this program.  If not, see <gnu.oruse Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;g/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Article\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * Controller class for articles actions.
 *
 */
class ArticleController extends AbstractController
{
    /**
     * Display of the project page
     * 
     */
    public function project()
    {
        return $this->render('@Mobicoop/article/project.html.twig');
    }

     /**
     * Display of the CGU page
     * 
     */
    public function cgu()
    {
        return $this->render('@Mobicoop/article/cgu.html.twig');
    }

     /**
     * Display of the news page
     * 
     */
    public function news()
    {
        return $this->render('@Mobicoop/article/news.html.twig');
    }
}
