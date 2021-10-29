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

namespace Mobicoop\Bundle\MobicoopBundle\Community\Controller;

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Image\Service\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller class for community related actions.
 *
 */
class CommunityController extends AbstractController
{
    use HydraControllerTrait;

    const DEFAULT_NB_COMMUNITIES_PER_PAGE = 10; // Nb items per page by default

    private $createFromFront;
    private $communityUserDirectMessage;

    /**
     * Constructor
     * @param string $createFromFront
     */
    public function __construct(bool $createFromFront, bool $communityUserDirectMessage)
    {
        $this->createFromFront = $createFromFront;
        $this->communityUserDirectMessage = $communityUserDirectMessage;
    }

    /**
     * Create a community
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
                $address->setLayer($communityAddress['layer']);
                $address->setAddressCountry($communityAddress['addressCountry']);
                $address->setAddressLocality($communityAddress['addressLocality']);
                $address->setCountryCode($communityAddress['countryCode']);
                $address->setCounty($communityAddress['county']);
                $address->setLatitude($communityAddress['latitude']);
                $address->setLocalAdmin($communityAddress['localAdmin']);
                $address->setLongitude($communityAddress['longitude']);
                $address->setMacroCounty($communityAddress['macroCounty']);
                $address->setMacroRegion($communityAddress['macroRegion']);
                $address->setPostalCode($communityAddress['postalCode']);
                $address->setRegion($communityAddress['region']);
                $address->setStreet($communityAddress['street']);
                $address->setHouseNumber($communityAddress['houseNumber']);
                $address->setStreetAddress($communityAddress['streetAddress']);
                $address->setSubLocality($communityAddress['subLocality']);
                $address->setDisplayLabel($communityAddress['displayLabel']);

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
     * Communities list controller
     */
    public function communityList()
    {
        $this->denyAccessUnlessGranted('list', new Community());

        return $this->render('@Mobicoop/community/communities.html.twig', [
            'defaultItemsPerPage' => self::DEFAULT_NB_COMMUNITIES_PER_PAGE
        ]);
    }

    /**
     * Get all communities (AJAX)
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
                'canCreate' => $this->createFromFront
            ]);
        } else {
            return new JsonResponse("bad method");
        }
    }


    /**
     * Show a community
     */
    public function communityShow($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        // retreive community;
        $community = $communityManager->getCommunity($id);

        $this->denyAccessUnlessGranted('show', $community);

        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/community/community.html.twig', [
            'community' => $community,
            'user' => $user,
            'searchRoute' => "covoiturage/recherche",
            'error' => (isset($error)) ? $error : false,
            'communityUserStatus' => $community->getMemberStatus(),
            'communityUserDirectMessage' => $this->communityUserDirectMessage
        ]);
    }

    /**
     * Show the register form for a secured community
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

        if ($request->isMethod('POST')) {
            // If it's a post, we know that's a secured community credential
            $communityUser = new CommunityUser();
            $communityUser->setUser($user);
            $communityUser->setCommunity($community);
            $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED_AS_MEMBER);

            // the credentials
            $communityUser->setLogin($request->request->get("credential1"));
            $communityUser->setPassword($request->request->get("credential2"));
            $communityUser = $communityManager->joinCommunity($communityUser);
            if (null === $communityUser) {
                $error = true;
            } else {
                $error = false;
                $session = $this->get('session');
                $session->remove(Community::SESSION_VAR_NAME); // To reload communities list in the header
                $communityUser = [$communityUser]; // To fit the getCommunityUser behavior we need to have an array

                // Redirect to the community
                return $this->redirectToRoute('community_show', ['id'=>$id]);
            }
        }

        return $this->render('@Mobicoop/community/community_secured_register.html.twig', [
            'communityId' => $id,
            'communityName' => $community->getName(),
            'userId' => (!is_null($user)) ? $user->getId() : null,
            'error' => (isset($error)) ? $error : false
        ]);
    }

    /**
     * Join a community
     */
    public function communityJoin($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = $communityManager->getCommunity($id);

        $this->denyAccessUnlessGranted('join', $community);

        $user = $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        //test if the user logged is not already a member of the community
        if ($user && '' !== $user && !$community->isMember()) {
            $communityUser = new CommunityUser();
            $communityUser->setCommunity($community);
            $communityUser->setUser($user);
            $data = $communityManager->joinCommunity($communityUser);
            $session = $this->get('session');
            $session->remove(Community::SESSION_VAR_NAME); // To reload communities list in the header

            return new JsonResponse($data);
        }

        return new JsonResponse();
    }

    /**
     * Leave a community.
     */
    public function communityLeave($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = $communityManager->getCommunity($id);

        $this->denyAccessUnlessGranted('leave', $community);

        if ($userManager->getLoggedUser()) {
            $data = $communityManager->leaveCommunity($communityUserToDelete);
        }

        return new Response();
    }

    /**
     * Get the communityUser of a User
     *
     * @param CommunityManager $communityManager
     * @param UserManager $userManager
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

        return new Response;
    }


    /**
     * Get all users of a community
     * Ajax
     *
     * @param integer $id
     * @param CommunityManager $communityManager
     * @return void
     */
    public function communityMemberList(Request $request, CommunityManager $communityManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $params = [
                "page" => $data['page'],
                "perPage" => $data['perPage']
            ];

            $communityMembersList = $communityManager->communityMembers($data['id'], $params);

            return new JsonResponse([
                "users"=>json_decode($communityMembersList)->members,
                "totalItems"=>(int)json_decode($communityMembersList)->totalMembers
            ]);
        } else {
            return new JsonResponse();
        }
    }

    public function communityMapsAds(int $id, CommunityManager $communityManager)
    {
        return new Response($communityManager->communityMapsAds($id));
    }

    /**
     * Show a community widget.
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
            'searchRoute' => 'covoiturage/recherche'
        ]);
    }

    /**
     * Show a community widget page to get the widget code.
     */
    public function communityGetWidget($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $community = $communityManager->getCommunity($id);
        
        //$this->denyAccessUnlessGranted('show', $community);
        return $this->render('@Mobicoop/community/community-get-widget.html.twig', [
            'community' => $community
        ]);
    }

    public function communityLastUsers(int $id, CommunityManager $communityManager)
    {
        return new Response($communityManager->getLastUsers($id));
    }

    /******************
     *                *
     * Refactor start *
     *                *
     ******************/

    /**
     * Get all communities for registration (AJAX)
     */
    public function getCommunityListForRegistration(CommunityManager $communityManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            return new JsonResponse($communityManager->getCommunityListForRegistration($data['email']));
        } else {
            return new JsonResponse("bad method");
        }
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
            return [] ;
        }
    }
}
