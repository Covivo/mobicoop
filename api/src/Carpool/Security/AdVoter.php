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

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Carpool\Entity\Ad;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Service\AdManager;
use App\Right\Service\PermissionManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AdVoter extends Voter
{
    const AD_READ = 'ad_read';
    const ADS_READ = 'ads_read';
    const AD_CREATE = 'ad_create';
    const AD_UPDATE = 'ad_update';
    const AD_DELETE = 'ad_delete';
    const AD_ASK_POST = 'ad_ask_post';
    const AD_ASK_PUT = 'ad_ask_put';
    const AD_ASK_GET = 'ad_ask_get';
    
    private $security;
    private $request;
    private $adManager;
    private $permissionManager;
    private $matchingRepository;

    public function __construct(RequestStack $requestStack, Security $security, AdManager $adManager, PermissionManager $permissionManager, MatchingRepository $matchingRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->adManager = $adManager;
        $this->permissionManager = $permissionManager;
        $this->matchingRepository = $matchingRepository;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::AD_READ,
            self::ADS_READ,
            self::AD_CREATE,
            self::AD_UPDATE,
            self::AD_DELETE,
            self::AD_ASK_POST,
            self::AD_ASK_PUT,
            self::AD_ASK_GET
            ])) {
            return false;
        }

        // Ad is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::ADS_READ:
                return $this->canReadAds($requester, $this->request->get("userId"));
            case self::AD_READ:
                if (!$ad = $this->adManager->getAdForPermission($this->request->get("id"))) {
                    return false;
                }
                return $this->canReadAd($ad, $requester);
            case self::AD_CREATE:
                return $this->canCreateAd($requester);
            case self::AD_UPDATE:
                if (!$ad = $this->adManager->getAdForPermission($this->request->get("id"))) {
                    return false;
                }
                return $this->canUpdateAd($ad, $requester);
            case self::AD_DELETE:
                if (!$ad = $this->adManager->getAdForPermission($this->request->get("id"))) {
                    return false;
                }
                return $this->canDeleteAd($ad, $requester);
            case self::AD_ASK_POST:
                // an Ask post is in fact an Ad post, with the original Ad id inside => we have these information in the subject
                /**
                 * @var Ad $subject
                 */
                if (!$ad = $this->adManager->getAdForPermission($subject->getAdId())) {
                    return false;
                }
                // we check that the user id provided in the request is one of the matching proposals owners
                $matching = $this->matchingRepository->find($subject->getMatchingId());
                if ($matching->getProposalOffer()->getUser()->getId() == $this->request->get("userId") || $matching->getProposalRequest()->getUser()->getId() == $this->request->get("userId")) {
                    return true;
                }
                return false;
            case self::AD_ASK_GET:
            case self::AD_ASK_PUT:
                // we check that the user id provided in the request is one of the matching proposals owners
                $matching = $this->matchingRepository->find($this->request->get("id"));
                if ($matching->getProposalOffer()->getUser()->getId() == $this->request->get("userId") || $matching->getProposalRequest()->getUser()->getId() == $this->request->get("userId")) {
                    return true;
                }
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadAds(UserInterface $requester, ?int $userId=null)
    {
        return $this->permissionManager->checkPermission('ad_list', $requester, null, $userId);
    }

    private function canReadAd(Ad $ad, UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('ad_read', $requester, null, $ad->getId());
    }

    private function canUpdateAd(Ad $ad, UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('ad_update', $requester, null, $ad->getId());
    }

    private function canCreateAd(UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('ad_create', $requester);
    }

    private function canDeleteAd(Ad $ad, UserInterface $requester)
    {
        return $this->permissionManager->checkPermission('ad_delete', $requester, null, $ad->getId());
    }
}
