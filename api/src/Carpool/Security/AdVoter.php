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

namespace App\Carpool\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Carpool\Ressource\Ad;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Matching;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Service\AdManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class AdVoter extends Voter
{
    public const AD_CREATE = 'ad_create';
    public const AD_READ = 'ad_read';
    public const AD_READ_EXTERNAL = 'ad_read_external';
    public const AD_UPDATE = 'ad_update';
    public const AD_DELETE = 'ad_delete';
    public const AD_LIST = 'ad_list';
    public const AD_ASK_CREATE = 'ad_ask_create';
    public const AD_ASK_READ = 'ad_ask_read';
    public const AD_ASK_UPDATE = 'ad_ask_update';
    public const AD_SEARCH_CREATE = 'ad_search_create';
    public const AD_CLAIM = 'ad_claim';

    private $request;
    private $authManager;
    private $matchingRepository;
    private $adManager;
    private $askRepository;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, MatchingRepository $matchingRepository, AdManager $adManager, AskRepository $askRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->authManager = $authManager;
        $this->matchingRepository = $matchingRepository;
        $this->adManager = $adManager;
        $this->askRepository = $askRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::AD_CREATE,
            self::AD_READ,
            self::AD_READ_EXTERNAL,
            self::AD_UPDATE,
            self::AD_DELETE,
            self::AD_LIST,
            self::AD_ASK_CREATE,
            self::AD_ASK_READ,
            self::AD_ASK_UPDATE,
            self::AD_SEARCH_CREATE,
            self::AD_CLAIM
            ])) {
            return false;
        }

        // only vote on Ad objects inside this voter
        if (!in_array($attribute, [
            self::AD_CREATE,
            self::AD_READ,
            self::AD_READ_EXTERNAL,
            self::AD_UPDATE,
            self::AD_DELETE,
            self::AD_LIST,
            self::AD_ASK_CREATE,
            self::AD_ASK_READ,
            self::AD_ASK_UPDATE,
            self::AD_SEARCH_CREATE,
            self::AD_CLAIM
            ]) && !($subject instanceof Paginator) && !($subject instanceof Ad)) {
            return false;
        }

        // Ad is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // echo $attribute;die;
        // var_dump($subject);die;

        switch ($attribute) {
            case self::AD_CREATE:
                return $this->canCreateAd();
            case self::AD_READ:
                $ad = $this->adManager->getAd($this->request->get('id'), null, null, null, false);
                return $this->canReadAd($ad);
            case self::AD_READ_EXTERNAL:
                $ad = $this->adManager->getAdFromExternalId($this->request->get('id'));
                return $this->canReadAd($ad);
            case self::AD_UPDATE:
                $ad = $this->adManager->getAd($this->request->get('id'));
                return $this->canUpdateAd($ad);
            case self::AD_DELETE:
                $ad = $this->adManager->getAd($this->request->get('id'));
                return $this->canDeleteAd($ad);
            case self::AD_LIST:
                return $this->canListAd();
            case self::AD_ASK_CREATE:
                $matching = $this->matchingRepository->find($subject->getMatchingId());
                return $this->canCreateAskFromAd($matching);
            case self::AD_ASK_READ:
                $ask = $this->askRepository->find($this->request->get('id'));
                return $this->canReadAskFromAd($ask);
            case self::AD_ASK_UPDATE:
                $ask = $this->askRepository->find($this->request->get('id'));
                return $this->canUpdateAskFromAd($ask);
            case self::AD_SEARCH_CREATE:
                return $this->canCreateSearchAd();
            case self::AD_CLAIM:
                $ad = $this->adManager->getAd($this->request->get('id'), null, null, null, false);
                return $this->canClaimAd($ad);
        }

        throw new \LogicException('This code should not be reached!');
    }


    private function canCreateAd()
    {
        return $this->authManager->isAuthorized(self::AD_CREATE);
    }

    private function canReadAd(Ad $ad)
    {
        return $this->authManager->isAuthorized(self::AD_READ, ['ad'=>$ad]);
    }

    private function canUpdateAd(Ad $ad)
    {
        return $this->authManager->isAuthorized(self::AD_UPDATE, ['ad'=>$ad]);
    }

    private function canDeleteAd(Ad $ad)
    {
        return $this->authManager->isAuthorized(self::AD_DELETE, ['ad'=>$ad]);
    }

    private function canListAd()
    {
        return $this->authManager->isAuthorized(self::AD_LIST);
    }

    private function canCreateAskFromAd(Matching $matching)
    {
        return $this->authManager->isAuthorized(self::AD_ASK_CREATE, ['matching'=>$matching]);
    }

    private function canReadAskFromAd(Ask $ask)
    {
        return $this->authManager->isAuthorized(self::AD_ASK_READ, ['ask'=>$ask]);
    }

    private function canUpdateAskFromAd(Ask $ask)
    {
        return $this->authManager->isAuthorized(self::AD_ASK_UPDATE, ['ask'=>$ask]);
    }

    private function canCreateSearchAd()
    {
        return $this->authManager->isAuthorized(self::AD_SEARCH_CREATE);
    }

    private function canClaimAd(Ad $ad)
    {
        return $this->authManager->isAuthorized(self::AD_CLAIM, ['ad'=>$ad]);
    }
}
