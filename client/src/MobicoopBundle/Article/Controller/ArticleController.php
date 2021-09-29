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
    const TOOLBOX = 11;
    const COMMUNITYINFOS = 12;
    const LOM = 13;
    const GOODPRACTICES = 14;
    const MOREABOUT = 15;
    const COOKIES = 16;
    const COVID19 = 17;
    const PRIVACYPOLICY = 18;
    const COVENTION = 19;
    const HOW_IT_WORKS = 20;
    const CARPOOL = 21;
    const CARPOOLING = 22;
    const CARPOOLING_AREAS = 23;
    const PDM = 24;
    const TALK_ABOUT_US = 25;
    const FEES = 26;
    const MEDIAS = 27;
    const USEFUL_LINKS = 28;
    const MOBILE_APP = 29;
    const ACCESSIBILITY = 30;
    const ABOUT_US = 31;
    const MOBILITY = 32;
    const LEGAL_NOTICE = 33;
    const I_AM_PRIVATE_PERSON = 34;
    const I_AM_SOCIETY = 35;
    const GUARANTEED_RETURN = 36;

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
    public function showHistory(ArticleManager $articleManager)
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
    * Display of the cookie page
    *
    */
    public function showCookie(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COOKIES));
    }

    /**
     * Display of the TOOLBOX page
     *
     */
    public function showToolbox()
    {
        // Not an 'article' page.
        return $this->render('@Mobicoop/article/toolbox.html.twig', []);
    }

    /**
     * Display of the COMMUNITYINFOS page
     *
     */
    public function showCommunityInfos(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COMMUNITYINFOS));
    }

    /**
     * Display of the LOM page
     *
     */
    public function showLOM(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::LOM));
    }

    /**
     * Display of the GOODPRACTICES page
     *
     */
    public function showGoodPractices(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::GOODPRACTICES));
    }

    /**
    * Display of the MOREABOUT page
    *
    */
    public function showMoreAbout(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MOREABOUT));
    }


    /**
    * Display of the COVID-19 page
    *
    */
    public function showCovid19(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COVID19));
    }

    /**
    * Display of the PRIVACY POLICY page
    *
    */
    public function showPrivacyPolicy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::PRIVACYPOLICY));
    }

    /**
    * Display of the COVENTION page
    *
    */
    public function showCovention(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COVENTION));
    }

    /**
    * Display of the HOW_IT_WORKS page
    *
    */
    public function showHowItWorks(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::HOW_IT_WORKS));
    }

    /**
    * Display of the CARPOOL page
    *
    */
    public function showCarpool(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CARPOOL));
    }

    /**
    * Display of the CARPOOLING page
    *
    */
    public function showCarpooling(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CARPOOLING));
    }

    /**
    * Display of the CARPOOLING_AREAS page
    *
    */
    public function showCarpoolingAreas(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CARPOOLING_AREAS));
    }

    /**
    * Display of the PDM page
    *
    */
    public function showPDM(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::PDM));
    }

    /**
    * Display of the TALK_ABOUT_US page
    *
    */
    public function showTalkAboutUs(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::TALK_ABOUT_US));
    }

    

    /**
    * Display of the ABOUT_US page
    *
    */
    public function showAboutUs(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::ABOUT_US));
    }

    /**
    * Display of the MOBILE_APP page
    *
    */
    public function showFees(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::FEES));
    }

    /**
    * Display of the MEDIAS page
    *
    */
    public function showMedias(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MEDIAS));
    }

    /**
    * Display of the USEFUL_LINKS page
    *
    */
    public function showUsefulLinks(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::USEFUL_LINKS));
    }

    /**
    * Display of the MOBILE_APP page
    *
    */
    public function showMobileApp(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MOBILE_APP));
    }

    /**
    * Display of the ACCESSIBILITY page
    *
    */
    public function showAccessibility(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::ACCESSIBILITY));
    }

    /**
    * Display of the ACCESSIBILITY page
    *
    */
    public function showMobility(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MOBILITY));
    }

    /**
    * Display of the LEGAL NOTICE page
    *
    */
    public function showLegalNotice(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::LEGAL_NOTICE));
    }

    /**
    * Display of the I'M A PRIVATE PERSON page
    *
    */
    public function showIAmPrivatePerson(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::I_AM_PRIVATE_PERSON));
    }

    /**
    * Display of the I'M A SOCIETY page
    *
    */
    public function showIAmSociety(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::I_AM_SOCIETY));
    }


    /**
    * Display of the GUARANTEED_RETURN page
    *
    */
    public function showGuaranteedReturn(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::GUARANTEED_RETURN));
    }

    /**
    * Display of the GUARANTEED_RETURN page
    *
    */
    public function showGuaranteedReturn(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::GUARANTEED_RETURN));
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

    /**
     * AJAX get article
     *
     * @param Request $request
     * @param ArticleManager $articleManager
     * @return void
     */
    public function article(Request $request, ArticleManager $articleManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['articleId']) && is_numeric($data['articleId'])) {
                $id = $data['articleId'];
            }
            return new JsonResponse($articleManager->getArticle($id));
        }
        return new JsonResponse();
    }

    /**
     * Simple get article (useful for redirections)
     *
     * @param int               $id             The article id
     * @param ArticleManager    $articleManager The article manager
     * @return JsonResponse
     */
    public function articleGet(int $id, ArticleManager $articleManager)
    {
        if ($article = $articleManager->getArticle($id)) {
            return $this->showArticle($article);
        }
        return new JsonResponse();
    }

    /**
     * Rss feeds list controller
     *
     * @param string            $context             The context
     * @param ArticleManager    $articleManager The article manager
     * @return JsonResponse
     */
    public function getRssFeedList(ArticleManager $articleManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($articleManager->getArticles(Article::CONTEXT_HOME));
        }
        return new JsonResponse();
    }
}
