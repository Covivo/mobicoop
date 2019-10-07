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

/**
 * Controller class for community related actions.
 *
 */
class CommunityController extends AbstractController
{
    use HydraControllerTrait;
    /**
     * Get all communities.
     */
    public function list(CommunityManager $communityManager)
    {
        $this->denyAccessUnlessGranted('list', new Community());
        return $this->render('@Mobicoop/community/communities.html.twig', [
            'communities' => $communityManager->getCommunities(),
        ]);
    }

    /**
     * Create a community
     */
    public function create(CommunityManager $communityManager, UserManager $userManager, Request $request, ImageManager $imageManager)
    {
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
                $communityUser->setStatus(1);
                
                // set community address
                $communityAddress=json_decode($data->get('address'), true);
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
                $community->setAutoValidation(true);

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
     * Show a community
     */
    public function show($id, CommunityManager $communityManager, UserManager $userManager, Request $request)
    {

        // retreive community;
        $community = $communityManager->getCommunity($id);

        //$this->denyAccessUnlessGranted('show', $community);

        // retreive logged user
        $user = $userManager->getLoggedUser();

        if ($request->isMethod('POST')) {
            // If it's a post, we know that's a secured community credential
            $communityUser = new CommunityUser();
            $communityUser->setUser($user);
            $communityUser->setCommunity($community);
            $communityUser->setStatus(CommunityUser::STATUS_ACCEPTED);

            // the credentials
            $communityUser->setLogin($request->request->get("credential1"));
            $communityUser->setPassword($request->request->get("credential2"));
            $communityUser = $communityManager->joinCommunity($communityUser);
            ($communityUser===null) ? $error = true : $error = false;
        } else {
            ($user!==null) ? $communityUser = $communityManager->getCommunityUser($id, $user->getId()) : $communityUser = null;
        }

        return $this->render('@Mobicoop/community/community.html.twig', [
            'community' => $community,
            'user' => $user,
            'communityUser' => (isset($communityUser) && $communityUser!==null)?$communityUser:null,
            'searchRoute' => "covoiturage/recherche",
            'error' => (isset($error)) ? $error : false
        ]);
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @param CommunityManager $communityManager
     * @param UserManager $userManager
     * @return void
     */
    public function communityUser(int $id, CommunityManager $communityManager, UserManager $userManager)
    {
        if ($userManager->getLoggedUser()) {
            $communityUser = $communityManager->getCommunityUser($id, $userManager->getLoggedUser()->getId());
            $reponseofmanager= $this->handleManagerReturnValue($communityUser);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
            return $this->json($communityUser);
        }
        
        return new Response;
    }


    /**
     * Join a community
     */
    public function joinCommunity($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = $communityManager->getCommunity($id);
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $communityUsersId = [];
        foreach ($community->getCommunityUsers() as $communityUser) {
            // get all community users ID
            array_push($communityUsersId, $communityUser->getUser()->getId());
        }
        //test if the user logged is not already a member of the community
        if ($user && $user !=='' && !in_array($user->getId(), $communityUsersId)) {
            $communityUser = new CommunityUser();
            if ($community->isAutoValidation()) {
                $communityUser->setCommunity(new Community($id));
                $communityUser->setUser($user);
                $communityUser->setStatus(1);
            } else {
                $communityUser->setCommunity(new Community($id));
                $communityUser->setUser($user);
                $communityUser->setStatus(0);
            }

            $data=$communityManager->joinCommunity($communityUser);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }
        return new Response();
    }

    /**
     * Get last three users
     *
     * @param int $id
     * @param CommunityManager $communityManager
     * @param UserManager $userManager
     * @return void
     */
    public function getCommunityLastUsers(int $id, CommunityManager $communityManager)
    {
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
     *
     * @param integer $id
     * @param CommunityManager $communityManager
     * @return void
     */
    public function getCommunityMemberList(int $id, CommunityManager $communityManager)
    {
        // retrive community;
        $community = $communityManager->getCommunity($id);
        $reponseofmanager= $this->handleManagerReturnValue($community);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $users = [];
        //test if the community has members
        if (count($community->getCommunityUsers()) > 0) {
            foreach ($community->getCommunityUsers() as $communityUser) {
                if ($communityUser->getStatus() == 1) {
                    // get all community Users
                    array_push($users, $communityUser->getUser());
                }
            }
        }
        return new Response(json_encode($users));
    }

    /**
     * Get all proposals of a community
     *
     * @param integer $id
     * @param CommunityManager $communityManager
     * @return void
     */
    public function getCommunityProposals(int $id, CommunityManager $communityManager)
    {
        $proposals = $communityManager->getProposals($id);
        $points = [];
        if ($proposals!==null) {
            foreach ($proposals as $proposal) {
                foreach ($proposal["waypoints"] as $waypoint) {
                    $points[] = [
                        "title"=>$waypoint["address"]["displayLabel"],
                        "latLng"=>["lat"=>$waypoint["address"]["latitude"],"lon"=>$waypoint["address"]["longitude"]]
                    ];
                }
            }
        }
        return new Response(json_encode($points));
    }
}
