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

use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Article;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Mobicoop\Bundle\MobicoopBundle\Article\Service\ArticleManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        return $this->showArticle($articleManager->getArticle(self::PROJECT));
    }

    /**
     * Display of the CGU page
     *
     */
    public function showCgu(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CGU));
    }

    /**
     * Display of the news page
     *
     */
    public function showNews(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::NEWS));
    }

    /**
     * Display of the data policy page
     *
     */
    public function showDataPolicy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::DATA_POLICY));
    }

    /**
     * Display of the insurance policy page
     *
     */
    public function showInsurancePolicy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::INSURANCE_POLICY));
    }

    /**
     * Display of the history page
     *
     */
    public function showHistroy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::HISTORY));
    }

    /**
     * Display of the actors page
     *
     */
    public function showActors(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::ACTORS));
    }

    /**
     * Display of the solidary carpool page
     *
     */
    public function showSolidaryCarpool(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::SOLIDARY_CARPOOL));
    }

    /**
     * Display of the become a partner page
     *
     */
    public function showBecomePartner(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::BECOME_PARTNER));
    }

    /**
     * Display of the FAQ page
     *
     */
    public function showFAQ(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::FAQ));
    }

    /**
     * Show an article
     *
     * @param Article $article The article to show
     * @return void
     */
    private function showArticle(Article $article)
    {
        $reponseofmanager= $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('article_show', $article);
        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Get the last external articles
     */
    public function lastExternalArticles(Request $request, ArticleManager $articleManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $nbArticles = Article::NB_EXTERNAL_ARTICLES_DEFAULT;
            if (isset($data['nbArticles']) && is_numeric($data['nbArticles'])) {
                $nbArticles = $data['nbArticles'];
            }
            return new JsonResponse($articleManager->getLastExternalArticles($nbArticles));
        }
        return new JsonResponse();
    }
}
