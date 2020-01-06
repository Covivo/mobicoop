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

namespace Mobicoop\Bundle\MobicoopBundle\Article\Controller;

use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Mobicoop\Bundle\MobicoopBundle\Article\Service\ArticleManager;

/**
 * Controller class for articles actions.
 *
 */
class ArticleController extends AbstractController
{
    use HydraControllerTrait;
    
    const CGU = 1;
    const NEWS = 2;
    const PROJECT = 3;
    const DATA_POLICY = 4;
    const INSURANCE_POLICY = 5;
    const HISTORY = 6;
    const ACTORS = 7;
    const SOLIDARY_CARPOOL = 8;
    const BECOME_PARTNER = 9;
    const FAQ = 10;

    /**
     * Display of the project page
     *
     */
    public function showProject(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::PROJECT);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the CGU page
     *
     */
    public function showCgu(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::CGU);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the news page
     *
     */
    public function showNews(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::NEWS);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the data policy page
     *
     */
    public function showDataPolicy(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::DATA_POLICY);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the insurance policy page
     *
     */
    public function showInsurancePolicy(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::INSURANCE_POLICY);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the history page
     *
     */
    public function showHistroy(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::HISTORY);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the actors page
     *
     */
    public function showActors(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::ACTORS);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the solidary carpool page
     *
     */
    public function showSolidaryCarpool(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::SOLIDARY_CARPOOL);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the become a partner page
     *
     */
    public function showBecomePartner(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::BECOME_PARTNER);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Display of the FAQ page
     *
     */
    public function showFAQ(ArticleManager $articleManager)
    {
        $article = $articleManager->getArticle(self::FAQ);
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }
}
