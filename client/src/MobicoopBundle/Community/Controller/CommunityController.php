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

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller class for community related actions.
 *
 */
class CommunityController extends AbstractController
{
    use HydraControllerTrait;

    const DEFAULT_NB_COMMUNITIES_PER_PAGE = 10; // Nb items per page by default

    private $createFromFront;

    /**
     * Constructor
     * @param string $createFromFront
     */
    public function __construct($createFromFront)
    {
        $this->createFromFront = ($createFromFront === 'true') ? true : false;
    }
    
    /**
     * Create a community
     */
    public function communityCreate(CommunityManager $communityManager, UserManager $userManager, Request $request, ImageManager $imageManager)
    {
        // Deny the creation of a community if the .env say so
        if ($this->createFromFront==="false") {
            return $this->redirectToRoute('home');
        }
        
        $community = new Community();
        $this->denyAccessUnlessGranted('create', $community);
        $user = new User($userManager->getLoggedUser()->getId());
        $communityUser = new CommunityUser();
        $address = new Address();
        
        if ($request->isMethod('POST')) {
            $data = $request->request;
            // Check if the community name is available (if yes continue)
            if ($communityManager->checkNameAvailability($data->get('name'))) {

                // set the user as a user of the community
                $communityUser->setUser($user);
                
                // set community address
                $communityAddress = json_decode($data->get('address'), true);
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
                    $communityManager->deleteCommunity($community->getId());
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

            $perPage = (isset($data['perPage']) && !is_null($data['perPage'])) ? $data['perPage'] : null;
            $page = (isset($data['page']) && !is_null($data['page'])) ? $data['page'] : null;
            $search = (isset($data['search']) && !is_null($data['search'])) ? $data['search'] : [];

            if ($user) {

                // We get all the communities
                $communities = $communityManager->getCommunities($user->getId(), $perPage, $page, $search);

                // We get de communities of the user
                $communityUsers = $communityManager->getAllCommunityUser($user->getId());
                $communitiesUser = [];
                $idCommunitiesUser = [];
                foreach ($communityUsers as $communityUser) {
                    $communitiesUser[] = $communityUser->getCommunity();
                    $idCommunitiesUser[] = $communityUser->getCommunity()->getId();
                }

                // we delete those who the user is already in
                // To Do : This code does'nt work anymore
                // $tempCommunities = [];
                // foreach ($communities as $key => $community) {
                //     if (!in_array($community->getId(), $idCommunitiesUser)) {
                //         $tempCommunities[] = $communities[$key];
                //     }
                // }
                // $communities = $tempCommunities;
            } else {
                $communitiesUser = [];
                $communities = $communityManager->getCommunities(null, $perPage, $page, $search);
            }

            return new JsonResponse([
                'communities' => $communities->getMember(),
                'communitiesUser' => $communitiesUser,
                'canCreate' => $this->createFromFront,
                'communitiesView' => $communities->getView(),
                'totalItems' => $communities->getTotalItems()
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
            (null === $communityUser) ? $error = true : $error = false;
        } else {
            (null !== $user) ? $communityUser = $communityManager->getCommunityUser($id, $user->getId()) : $communityUser = null;
        }

        // todo : move inside service ?
        // get the last 3 users and formate them to be used with vue
        $lastUsers = $communityManager->getLastUsers($id);
        $lastUsersFormated = [];
        foreach ($lastUsers as $key => $commUser) {
            $lastUsersFormated[$key]["name"]=ucfirst($commUser->getUser()->getGivenName())." ".ucfirst($commUser->getUser()->getFamilyName());
            $lastUsersFormated[$key]["acceptedDate"]=$commUser->getAcceptedDate()->format('d/m/Y');
        }

        // todo : move inside service ?
        // Get the proposals and waypoints
        $proposals = $communityManager->getProposals($community->getId());
        $ways = [];
        if ($proposals!==null) {
            foreach ($proposals as $proposal) {
                $currentProposal = [
                    "type"=>($proposal["type"]==Proposal::TYPE_ONE_WAY) ? 'one-way' : ($proposal["type"]==Proposal::TYPE_OUTWARD) ? 'outward' : 'return',
                    "frequency"=>($proposal["criteria"]["frequency"]==Criteria::FREQUENCY_PUNCTUAL) ? 'puntual' : 'regular',
                    "carpoolerFirstName" => $proposal["user"]["givenName"],
                    "carpoolerLastName" => $proposal["user"]["familyName"],
                    "waypoints"=>[]
                ];
                foreach ($proposal["waypoints"] as $waypoint) {
                    $currentProposal["waypoints"][] = [
                        // "title"=>(is_array($waypoint["address"]["displayLabel"])) ? implode(", ", $waypoint["address"]["displayLabel"]) : $waypoint["address"]["displayLabel"],
                        "title"=>$waypoint["address"]["addressLocality"],
                        "destination"=>$waypoint['destination'],
                        "latLng"=>["lat"=>$waypoint["address"]["latitude"],"lon"=>$waypoint["address"]["longitude"]]
                    ];
                }
                $ways[] = $currentProposal;
            }
        }

        return $this->render('@Mobicoop/community/community.html.twig', [
            'community' => $community,
            'user' => $user,
            'communityUser' => (isset($communityUser) && $communityUser!==null)?$communityUser:null,
            'searchRoute' => "covoiturage/recherche",
            'error' => (isset($error)) ? $error : false,
            'points' => $ways,
            'lastUsers' => $lastUsersFormated,
            'communityUserStatus' => (isset($communityUser) && $communityUser!==null)?$communityUser[0]->getStatus():-1
            
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
        $communityUsersId = [];
        foreach ($community->getCommunityUsers() as $communityUser) {
            // get all community users ID
            array_push($communityUsersId, $communityUser->getUser()->getId());
        }
        //test if the user logged is not already a member of the community
        if ($user && '' !== $user && !in_array($user->getId(), $communityUsersId)) {
            $communityUser = new CommunityUser();
            $communityUser->setCommunity($community);
            $communityUser->setUser($user);
            $data = $communityManager->joinCommunity($communityUser);
            $reponseofmanager = $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }

        return new Response();
    }

    /**
     * Leave a community.
     */
    public function communityLeave($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = $communityManager->getCommunity($id);

        $this->denyAccessUnlessGranted('leave', $community);

        $user = $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }

        // TEST IF USER IS LOGGED
        if (null !== $user) {
            $communityUserToDelete = null;
            foreach ($community->getCommunityUsers() as $communityUser) {
                if ($communityUser->getUser()->getId() == $user->getId()) {
                    $communityUserToDelete = $communityUser;
                    break;
                }
            }

            if ($communityUserToDelete) {
                $data = $communityManager->leaveCommunity($communityUserToDelete);
                $reponseofmanager = $this->handleManagerReturnValue($data);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
            }
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
     * Get last three users
     * Ajax
     *
     * @param [type] $id
     * @param CommunityManager $communityManager
     * @param UserManager $userManager
     * @return void
     */
    public function communityLastUsers(int $id, CommunityManager $communityManager)
    {
        $community = $communityManager->getCommunity($id);
        $this->denyAccessUnlessGranted('show', $community);

        // get the last 3 users and formate them to be used with vue
        $lastUsers = $communityManager->getLastUsers($id);
        $lastUsersFormated = [];
        foreach ($lastUsers as $key => $commUser) {
            $lastUsersFormated[$key]["name"]=ucfirst($commUser->getUser()->getGivenName())." ".ucfirst($commUser->getUser()->getFamilyName());
            $lastUsersFormated[$key]["acceptedDate"]=$commUser->getAcceptedDate()->format('d/m/Y');
        }
        return new Response(json_encode($lastUsersFormated));
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
            $community = $communityManager->getCommunity($data['id']);
            $reponseofmanager = $this->handleManagerReturnValue($community);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
            $this->denyAccessUnlessGranted('show', $community);

            $users = [];
            //test if the community has members
            if (count($community->getCommunityUsers()) > 0) {
                foreach ($community->getCommunityUsers() as $communityUser) {
                    if ($communityUser->getStatus() == 1 || $communityUser->getStatus() == 2) {
                        // get all community Users accepted_as_member or accepted_as_moderator
                        array_push($users, $communityUser->getUser());
                    }
                }
            }
            $totalItems = count($users);


            return new JsonResponse([
                "users"=>$users,
                "totalItems"=>$totalItems
            ]);
        } else {
            return new JsonResponse();
        }
    }

    /**
     * Get all proposals of a community
     * Ajax
     *
     * @param integer $id
     * @param CommunityManager $communityManager
     * @return void
     */
    public function communityProposals(int $id, CommunityManager $communityManager)
    {
        $community = $communityManager->getCommunity($id);
        $this->denyAccessUnlessGranted('show', $community);

        $proposals = $communityManager->getProposals($id);
        $ways = [];
        if (null !== $proposals) {
            foreach ($proposals as $proposal) {
                $currentProposal = [
                    "type"=>($proposal["type"]==Proposal::TYPE_ONE_WAY) ? 'one-way' : ($proposal["type"]==Proposal::TYPE_OUTWARD) ? 'outward' : 'return',
                    "frequency"=>($proposal["criteria"]["frequency"]==Criteria::FREQUENCY_PUNCTUAL) ? 'puntual' : 'regular',
                    "waypoints"=>[]
                ];
                foreach ($proposal["waypoints"] as $waypoint) {
                    $currentProposal["waypoints"][] = [
                        "title"=>(is_array($waypoint["address"]["displayLabel"])) ? $waypoint["address"]["displayLabel"][0] : $waypoint["address"]["displayLabel"],
                        "destination"=>$waypoint['destination'],
                        "latLng"=>["lat"=>$waypoint["address"]["latitude"],"lon"=>$waypoint["address"]["longitude"]]
                    ];
                }
                $ways[] = $currentProposal;
            }
        }

        return new Response(json_encode($ways));
    }

    /**
     * Get available communities for the logged user
     * Ajax
     *
     * @param UserManager $userManager
     * @param CommunityManager $communityManager
     * @return void
     */
    public function communityUserAvailable(int $userId, UserManager $userManager, CommunityManager $communityManager)
    {
        if ($user = $userManager->getUser($userId)) {
            $communities = $communityManager->getAvailableUserCommunities($user)->getMember();

            return new Response(json_encode($communities));
        }

        return new Response();
    }

    /**
     * Check if a user is an accepted member of a community
     * Ajax
     *
     * @param CommunityManager $communityManager
     * @param Request $request
     * @return boolean
     */
    public function isMember(CommunityManager $communityManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            // authorization control
            $community = $communityManager->getCommunity($data['communityId']);
            $this->denyAccessUnlessGranted('show', $community);

            return new Response(json_encode($communityManager->checkStatus($data['communityId'], $data['userId'], 1)));
        }

        return new Response();
    }
}
