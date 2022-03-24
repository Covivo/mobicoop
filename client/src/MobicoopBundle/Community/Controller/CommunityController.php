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

namespace Mobicoop\Bundle\MobicoopBundle\Community\Controller;

use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Image\Service\ImageManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for community related actions.
 */
class CommunityController extends AbstractController
{
    use HydraControllerTrait;

    public const DEFAULT_NB_COMMUNITIES_PER_PAGE = 10; // Nb items per page by default

    private $createFromFront;
    private $communityUserDirectMessage;

    /**
     * Constructor.
     *
     * @param string $createFromFront
     */
    public function __construct(bool $createFromFront, bool $communityUserDirectMessage)
    {
        $this->createFromFront = $createFromFront;
        $this->communityUserDirectMessage = $communityUserDirectMessage;
    }

    /**
     * Create a community.
     */
    public function communityCreate(CommunityManager $communityManager, UserManager $userManager, Request $request, ImageManager $imageManager)
    {
        // Deny the creation of a community if the .env say so
        if (!$this->createFromFront) {
            return $this->redirectToRoute('home');
        }

        $community = new Community();
        $this->denyAccessUnlessGranted('create', $community);
        $user = $userManager->getLoggedUser();
        $communityUser = new CommunityUser();
        $address = new Address();

        if ($request->isMethod('POST')) {
            $data = $request->request;
            // Check if the community name is available (if yes continue)
            if (is_null($checkName = $communityManager->checkExists($data->get('name'))->getDescription())) {
                // set the user as a user of the community
                $communityUser->setUser($user);

                // set community address
                $communityAddress = json_decode($data->get('address'), true);
                $address->setLayer(isset($communityAddress['layer']) ? $communityAddress['layer'] : null);
                $address->setAddressCountry(isset($communityAddress['addressCountry']) ? $communityAddress['addressCountry'] : null);
                $address->setAddressLocality(isset($communityAddress['addressLocality']) ? $communityAddress['addressLocality'] : null);
                $address->setCountryCode(isset($communityAddress['countryCode']) ? $communityAddress['countryCode'] : null);
                $address->setCounty(isset($communityAddress['county']) ? $communityAddress['county'] : null);
                $address->setLatitude(isset($communityAddress['latitude']) ? $communityAddress['latitude'] : null);
                $address->setLocalAdmin(isset($communityAddress['localAdmin']) ? $communityAddress['localAdmin'] : null);
                $address->setLongitude(isset($communityAddress['longitude']) ? $communityAddress['longitude'] : null);
                $address->setMacroCounty(isset($communityAddress['macroCounty']) ? $communityAddress['macroCounty'] : null);
                $address->setMacroRegion(isset($communityAddress['macroRegion']) ? $communityAddress['macroRegion'] : null);
                $address->setPostalCode(isset($communityAddress['postalCode']) ? $communityAddress['postalCode'] : null);
                $address->setRegion(isset($communityAddress['region']) ? $communityAddress['region'] : null);
                $address->setStreet(isset($communityAddress['street']) ? $communityAddress['street'] : null);
                $address->setHouseNumber(isset($communityAddress['houseNumber']) ? $communityAddress['houseNumber'] : null);
                $address->setStreetAddress(isset($communityAddress['streetAddress']) ? $communityAddress['streetAddress'] : null);
                $address->setSubLocality(isset($communityAddress['subLocality']) ? $communityAddress['subLocality'] : null);
                $address->setDisplayLabel(isset($communityAddress['displayLabel']) ? $communityAddress['displayLabel'] : null);

                // set community infos
                $community->setUser($user);
                $community->setName($data->get('name'));
                $community->setDescription($data->get('description'));
                $community->setFullDescription($data->get('fullDescription'));
                $community->setAddress($address);
                $community->addCommunityUser($communityUser);
                $community->setDomain($data->get('domain'));

                // create community
                if ($community = $communityManager->createCommunity($community)) {
                    // Post avatar of the community
                    $image = new Image();
                    $image->setCommunityFile($request->files->get('avatar'));
                    $image->setCommunityId($community->getId());
                    $image->setName($community->getName());
                    if ($image = $imageManager->createImage($image)) {
                        return new Response();
                    }
                    //If an error occur on upload image, the community is already create, so we delete her
                    // $communityManager->deleteCommunity($community->getId());
                    // return error if image post didnt't work
                    return new Response(json_encode('error.image'));
                }
                // return error if community post didn't work
                return new Response(json_encode('error.community.create'));
            }
            // return error because name already exists
            return new Response(json_encode('error.community.name'));
        }

        return $this->render('@Mobicoop/community/createCommunity.html.twig', [
        ]);
    }

    /**
     * Communities list controller.
     */
    public function communityList()
    {
        $this->denyAccessUnlessGranted('list', new Community());

        return $this->render('@Mobicoop/community/communities.html.twig', [
            'defaultItemsPerPage' => self::DEFAULT_NB_COMMUNITIES_PER_PAGE,
        ]);
    }

    /**
     * Get all communities (AJAX).
     */
    public function getCommunityList(CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = $userManager->getLoggedUser();
            $data = json_decode($request->getContent(), true);

            $communities = $communityManager->getAllCommunities($user, $data);

            return new JsonResponse([
                'communities' => $communities['communitiesMember'],
                'totalItems' => $communities['communitiesTotalItems'],
                'communitiesUser' => $communities['communitiesUser'],
                'canCreate' => $this->createFromFront,
            ]);
        }

        return new JsonResponse('bad method');
    }

    /**
     * Show a community.
     *
     * @param mixed $id
     */
    public function communityShow($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        // retreive community;
        $community = $communityManager->getCommunity($id);
        if (is_numeric($community)) {
            if (400 == $community) {
                // secured community
                return $this->redirectToRoute('community_secured_register', ['id' => $id]);
            }
        }

        $this->denyAccessUnlessGranted('show', $community);

        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/community/community.html.twig', [
            'community' => $community,
            'user' => $user,
            'searchRoute' => 'covoiturage/recherche',
            'error' => (isset($error)) ? $error : false,
            'communityUserStatus' => $community->getMemberStatus(),
            'isMember' => $community->isMember(),
            'communityUserDirectMessage' => $this->communityUserDirectMessage,
        ]);
    }

    /**
     * Show the register form for a secured community.
     *
     * @param mixed $id
     */
    public function communitySecuredRegister($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        $community = $communityManager->getPublicInfos($id);
        // retreive logged user
        $user = $userManager->getLoggedUser();

        // This should be removed when denyAccessUnlessGranted is functionnal
        if (is_null($user)) {
            return $this->redirectToRoute('user_login');
        }
        $this->denyAccessUnlessGranted('show', $community);

        return $this->render('@Mobicoop/community/community_secured_register.html.twig', [
            'communityId' => $id,
            'communityName' => $community->getName(),
            'communityUrlKey' => $community->getUrlKey(),
            'userId' => (!is_null($user)) ? $user->getId() : null,
            'error' => (isset($error)) ? $error : false,
        ]);
    }

    /**
     * Join a community.
     *
     * @param mixed $id
     */
    public function communityJoin($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = new Community($id);

        $this->denyAccessUnlessGranted('join', $community);

        if ($userManager->getLoggedUser()) {
            return new JsonResponse($communityManager->joinCommunity($community));
        }

        return new JsonResponse();
    }

    /**
     * Join a secured community.
     *
     * @param mixed $id
     */
    public function communitySecuredRegisterJoin($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (
                !isset($data['credential1']) || '' == trim($data['credential1'])
                || !isset($data['credential2']) || '' == trim($data['credential2'])
            ) {
                return new JsonResponse();
            }

            $community = new Community($id);
            $community->setLogin($data['credential1']);
            $community->setPassword($data['credential2']);

            $this->denyAccessUnlessGranted('join', $community);

            if ($userManager->getLoggedUser()) {
                return new JsonResponse($communityManager->joinCommunity($community));
            }

            return new JsonResponse();
        }

        return new JsonResponse();
    }

    /**
     * Leave a community.
     *
     * @param mixed $id
     */
    public function communityLeave($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = new Community($id);

        $this->denyAccessUnlessGranted('leave', $community);

        if ($userManager->getLoggedUser()) {
            return new JsonResponse($communityManager->leaveCommunity($community));
        }

        return new Response();
    }

    /**
     * Get the communityUser of a User.
     *
     * @return Response
     */
    public function communityUser(CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            // Maybe to discuss I think that only a user can get access to the community user associate
            $user = $userManager->getUser($data['userId']);
            $this->denyAccessUnlessGranted('update', $user);

            return new Response(json_encode($communityManager->getCommunityUser($data['communityId'], $data['userId'], 1)));
        }

        return new Response();
    }

    /**
     * Get all users of a community
     * Ajax.
     *
     * @param int $id
     */
    public function communityMemberList(Request $request, CommunityManager $communityManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $params = [
                'page' => $data['page'],
                'perPage' => $data['perPage'],
            ];

            $communityMembersList = $communityManager->communityMembers($data['id'], $params);

            return new JsonResponse([
                'users' => json_decode($communityMembersList)->members,
                'totalItems' => (int) json_decode($communityMembersList)->totalMembers,
            ]);
        }

        return new JsonResponse();
    }

    public function communityMapsAds(int $id, CommunityManager $communityManager)
    {
        return new Response($communityManager->communityMapsAds($id));
    }

    /**
     * Show a community widget.
     *
     * @param mixed $id
     */
    public function communityWidget($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $community = $communityManager->getCommunity($id);

        //$this->denyAccessUnlessGranted('show', $community);

        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/community/community-widget.html.twig', [
            'community' => $community,
            'user' => $user,
            'searchRoute' => 'covoiturage/recherche',
        ]);
    }

    /**
     * Show a community widget page to get the widget code.
     *
     * @param mixed $id
     */
    public function communityGetWidget($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $community = $communityManager->getCommunity($id);

        //$this->denyAccessUnlessGranted('show', $community);
        return $this->render('@Mobicoop/community/community-get-widget.html.twig', [
            'community' => $community,
        ]);
    }

    public function communityLastUsers(int $id, CommunityManager $communityManager)
    {
        return new Response($communityManager->getLastUsers($id));
    }

    // Refactor start

    /**
     * Get all communities for registration (AJAX).
     */
    public function getCommunityListForRegistration(CommunityManager $communityManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($communityManager->getCommunityListForRegistration($data['email']));
        }

        return new JsonResponse('bad method');
    }

    /**
     *  Get all relay points map (AJAX).
     */
    public function getRelayPointsMap(CommunityManager $communityManager, Request $request)
    {
        // We get the current community
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (isset($data['communityId'])) {
                return new JsonResponse($communityManager->getRelayPointsMap($data['communityId']));
            }

            return [];
        }
    }
}
