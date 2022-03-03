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

namespace Mobicoop\Bundle\MobicoopBundle\Article\Controller;

use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Article;
use Mobicoop\Bundle\MobicoopBundle\Article\Service\ArticleManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class for articles actions.
 */
class ArticleController extends AbstractController
{
    use HydraControllerTrait;

    public const CGU = 1;
    public const NEWS = 2;
    public const PROJECT = 3;
    public const DATA_POLICY = 4;
    public const INSURANCE_POLICY = 5;
    public const HISTORY = 6;
    public const ACTORS = 7;
    public const SOLIDARY_CARPOOL = 8;
    public const BECOME_PARTNER = 9;
    public const FAQ = 10;
    public const TOOLBOX = 11;
    public const COMMUNITYINFOS = 12;
    public const LOM = 13;
    public const GOODPRACTICES = 14;
    public const MOREABOUT = 15;
    public const COOKIES = 16;
    public const COVID19 = 17;
    public const PRIVACYPOLICY = 18;
    public const COVENTION = 19;
    public const HOW_IT_WORKS = 20;
    public const CARPOOL = 21;
    public const CARPOOLING = 22;
    public const CARPOOLING_AREAS = 23;
    public const PDM = 24;
    public const TALK_ABOUT_US = 25;
    public const FEES = 26;
    public const MEDIAS = 27;
    public const USEFUL_LINKS = 28;
    public const MOBILE_APP = 29;
    public const ACCESSIBILITY = 30;
    public const ABOUT_US = 31;
    public const MOBILITY = 32;
    public const LEGAL_NOTICE = 33;
    public const I_AM_PRIVATE_PERSON = 34;
    public const I_AM_SOCIETY = 35;
    public const GUARANTEED_RETURN = 36;
    public const GOOD_PRACTICES_ALT = 37;
    public const FAQ_ALT = 38;
    public const CGU_ALT = 39;
    public const DATA_POLICY_ALT = 40;
    public const DATA_PROTECTION_ALT = 41;

    /**
     * Display of the project page.
     */
    public function showProject(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::PROJECT));
    }

    /**
     * Display of the CGU page.
     */
    public function showCgu(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CGU));
    }

    /**
     * Display of the news page.
     */
    public function showNews(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::NEWS));
    }

    /**
     * Display of the data policy page.
     */
    public function showDataPolicy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::DATA_POLICY));
    }

    /**
     * Display of the insurance policy page.
     */
    public function showInsurancePolicy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::INSURANCE_POLICY));
    }

    /**
     * Display of the history page.
     */
    public function showHistory(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::HISTORY));
    }

    /**
     * Display of the actors page.
     */
    public function showActors(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::ACTORS));
    }

    /**
     * Display of the solidary carpool page.
     */
    public function showSolidaryCarpool(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::SOLIDARY_CARPOOL));
    }

    /**
     * Display of the become a partner page.
     */
    public function showBecomePartner(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::BECOME_PARTNER));
    }

    /**
     * Display of the FAQ page.
     */
    public function showFAQ(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::FAQ));
    }

    /**
     * Display of the cookie page.
     */
    public function showCookie(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COOKIES));
    }

    /**
     * Display of the TOOLBOX page.
     */
    public function showToolbox()
    {
        // Not an 'article' page.
        return $this->render('@Mobicoop/article/toolbox.html.twig', []);
    }

    /**
     * Display of the COMMUNITYINFOS page.
     */
    public function showCommunityInfos(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COMMUNITYINFOS));
    }

    /**
     * Display of the LOM page.
     */
    public function showLOM(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::LOM));
    }

    /**
     * Display of the GOODPRACTICES page.
     */
    public function showGoodPractices(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::GOODPRACTICES));
    }

    /**
     * Display of the MOREABOUT page.
     */
    public function showMoreAbout(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MOREABOUT));
    }

    /**
     * Display of the COVID-19 page.
     */
    public function showCovid19(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COVID19));
    }

    /**
     * Display of the PRIVACY POLICY page.
     */
    public function showPrivacyPolicy(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::PRIVACYPOLICY));
    }

    /**
     * Display of the COVENTION page.
     */
    public function showCovention(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::COVENTION));
    }

    /**
     * Display of the HOW_IT_WORKS page.
     */
    public function showHowItWorks(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::HOW_IT_WORKS));
    }

    /**
     * Display of the CARPOOL page.
     */
    public function showCarpool(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CARPOOL));
    }

    /**
     * Display of the CARPOOLING page.
     */
    public function showCarpooling(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CARPOOLING));
    }

    /**
     * Display of the CARPOOLING_AREAS page.
     */
    public function showCarpoolingAreas(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CARPOOLING_AREAS));
    }

    /**
     * Display of the PDM page.
     */
    public function showPDM(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::PDM));
    }

    /**
     * Display of the TALK_ABOUT_US page.
     */
    public function showTalkAboutUs(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::TALK_ABOUT_US));
    }

    /**
     * Display of the ABOUT_US page.
     */
    public function showAboutUs(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::ABOUT_US));
    }

    /**
     * Display of the MOBILE_APP page.
     */
    public function showFees(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::FEES));
    }

    /**
     * Display of the MEDIAS page.
     */
    public function showMedias(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MEDIAS));
    }

    /**
     * Display of the USEFUL_LINKS page.
     */
    public function showUsefulLinks(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::USEFUL_LINKS));
    }

    /**
     * Display of the MOBILE_APP page.
     */
    public function showMobileApp(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MOBILE_APP));
    }

    /**
     * Display of the ACCESSIBILITY page.
     */
    public function showAccessibility(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::ACCESSIBILITY));
    }

    /**
     * Display of the ACCESSIBILITY page.
     */
    public function showMobility(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::MOBILITY));
    }

    /**
     * Display of the LEGAL NOTICE page.
     */
    public function showLegalNotice(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::LEGAL_NOTICE));
    }

    /**
     * Display of the I'M A PRIVATE PERSON page.
     */
    public function showIAmPrivatePerson(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::I_AM_PRIVATE_PERSON));
    }

    /**
     * Display of the I'M A SOCIETY page.
     */
    public function showIAmSociety(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::I_AM_SOCIETY));
    }


    /**
     * Display of the GUARANTEED_RETURN page.
     */
    public function showGuaranteedReturn(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::GUARANTEED_RETURN));
    }

    public function showGoodPracticesAlt(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::GOOD_PRACTICES_ALT));
    }

    public function showFAQAlt(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::FAQ_ALT));
    }

    public function showCguAlt(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::CGU_ALT));
    }

    public function showDataPolicyAlt(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::DATA_POLICY_ALT));
    }

    public function showDataProtectionAlt(ArticleManager $articleManager)
    {
        return $this->showArticle($articleManager->getArticle(self::DATA_PROTECTION_ALT));
    }

    /**
     * Get the last external articles.
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
     * AJAX get article.
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
     * Simple get article (useful for redirections).
     *
     * @param int            $id             The article id
     * @param ArticleManager $articleManager The article manager
     *
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
     * Rss feeds list controller.
     *
     * @param string         $context        The context
     * @param ArticleManager $articleManager The article manager
     *
     * @return JsonResponse
     */
    public function getRssFeedList(ArticleManager $articleManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($articleManager->getArticles(Article::CONTEXT_HOME));
        }

        return new JsonResponse();
    }

    /**
     * Show an article.
     *
     * @param Article $article The article to show
     */
    private function showArticle(Article $article)
    {
        $reponseofmanager = $this->handleManagerReturnValue($article);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('article_show', $article);

        return $this->render('@Mobicoop/article/article.html.twig', [
            'article' => $article,
        ]);
    }
}
