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

namespace Mobicoop\Bundle\MobicoopBundle\User\Controller;

use Herrera\Json\Exception\Exception;
use Http\Client\Exception\HttpException;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Geography\Service\AddressManager;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Image\Service\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use Mobicoop\Bundle\MobicoopBundle\Communication\Service\InternalMessageManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AskManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\AskHistory;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AskHistoryManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserController extends AbstractController
{
    use HydraControllerTrait;

    private $encoder;
    private $facebook_show;
    private $facebook_appid;
    private $required_home_address;
    private $news_subscription;

    /**
     * Constructor
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder, $facebook_show, $facebook_appid, $required_home_address, $news_subscription)
    {
        $this->encoder = $encoder;
        $this->facebook_show = $facebook_show;
        $this->facebook_appid = $facebook_appid;
        $this->required_home_address = $required_home_address;
        $this->news_subscription = $news_subscription;
    }

    /***********
     * PROFILE *
     ***********/

    /**
     * User login.
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $this->denyAccessUnlessGranted('login');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $errorMessage = "";
        if (!is_null($error)) {
            $errorMessage = $error->getMessage();
        }
        
        return $this->render('@Mobicoop/user/login.html.twig', [
            "errorMessage"=>$errorMessage,
            "facebook_show"=>($this->facebook_show==="true") ? true : false,
            "facebook_appid"=>$this->facebook_appid,
        ]);
    }

    /**
     * User registration.
     */
    public function userSignUp(UserManager $userManager, Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('register');

        $user = new User();
        $address = new Address();
        $error = false;

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            // add home address to user if it exists
            if (isset($data['address'])) {
                $address->setAddressCountry($data['address']['addressCountry']);
                $address->setAddressLocality($data['address']['addressLocality']);
                $address->setCountryCode($data['address']['countryCode']);
                $address->setCounty($data['address']['county']);
                $address->setLatitude($data['address']['latitude']);
                $address->setLocalAdmin($data['address']['localAdmin']);
                $address->setLongitude($data['address']['longitude']);
                $address->setMacroCounty($data['address']['macroCounty']);
                $address->setMacroRegion($data['address']['macroRegion']);
                $address->setName($translator->trans('homeAddress', [], 'signup'));
                $address->setPostalCode($data['address']['postalCode']);
                $address->setRegion($data['address']['region']);
                $address->setStreet($data['address']['street']);
                $address->setStreetAddress($data['address']['streetAddress']);
                $address->setSubLocality($data['address']['subLocality']);
                $address->setHome(true);
            }
            $user->addAddress($address);
            // pass front info into user form
            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setPassword($data['password']);
            $user->setGivenName($data['givenName']);
            $user->setFamilyName($data['familyName']);
            $user->setGender($data['gender']);
            //$user->setBirthYear($data->get('birthYear')); Replace only year by full birthday
            $user->setBirthDate(new DateTime($data['birthDay']));
            //$user->setNewsSubscription by default
            $user->setNewsSubscription(($this->news_subscription==="true") ? true : false);


            if (!is_null($data['idFacebook'])) {
                $user->setFacebookId($data['idFacebook']);
            }

            // Create token to valid inscription
            $datetime = new DateTime();
            $time = $datetime->getTimestamp();
            // For safety, we strip the slashes because this token can be passed in url
            $pwdToken = str_replace("/", "", $this->encoder->encodePassword($user, $user->getEmail() . rand() . $time . rand() . $user->getSalt()));
            $user->setValidatedDateToken($pwdToken);
            // create user in database
            $data = $userManager->createUser($user);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }
 
        return $this->render('@Mobicoop/user/signup.html.twig', [
                'error' => $error,
                "facebook_show"=>($this->facebook_show==="true") ? true : false,
                "facebook_appid"=>$this->facebook_appid,
                "required_home_address"=>($this->required_home_address==="true") ? true : false,
        ]);
    }

    /**
     * User registration email validation
     */
    public function userSignUpValidation($token, UserManager $userManager, Request $request)
    {
        $error = "";
        if ($request->isMethod('POST') && $token !== "") {
            // We need to check if the token exists
            $userFound = $userManager->findByValidationDateToken($token);
            if (!empty($userFound)) {
                if ($userFound->getValidatedDate()!==null) {
                    $error = "alreadyValidated";
                } else {
                    $userFound->setValidatedDate(new \Datetime()); // TO DO : Correct timezone
                    $userFound = $userManager->updateUser($userFound);
                    if (!$userFound) {
                        $error = "updateError";
                    } else {
                        // Auto login and redirect
                        $token = new UsernamePasswordToken($userFound, null, 'main', $userFound->getRoles());
                        $this->get('security.token_storage')->setToken($token);
                        $this->get('session')->set('_security_main', serialize($token));
                        return $this->redirectToRoute('carpool_first_ad_post');
                    }
                }
            } else {
                $error = "unknown";
            }
        }
        return $this->render('@Mobicoop/user/signupValidation.html.twig', ['urlToken'=>$token, 'error'=>$error]);
    }

    /**
     * User profile update.
     */
    public function userProfileUpdate(UserManager $userManager, Request $request, ImageManager $imageManager, AddressManager $addressManager, TranslatorInterface $translator, $tabDefault)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('update', $user);

        // get the homeAddress
        $homeAddress = $user->getHomeAddress();
        
        $error = false;
           
        if ($request->isMethod('POST')) {
            $data = $request->request;
            $file = $request->files->get('avatar');
            
            if (!$homeAddress) {
                $homeAddress = new Address();
            }

            $this->denyAccessUnlessGranted('address_update_self', $user);
            
            
            $address=json_decode($data->get('homeAddress'), true);
            $homeAddress->setAddressCountry($address['addressCountry']);
            $homeAddress->setAddressLocality($address['addressLocality']);
            $homeAddress->setCountryCode($address['countryCode']);
            $homeAddress->setCounty($address['county']);
            $homeAddress->setLatitude($address['latitude']);
            $homeAddress->setLocalAdmin($address['localAdmin']);
            $homeAddress->setLongitude($address['longitude']);
            $homeAddress->setMacroCounty($address['macroCounty']);
            $homeAddress->setMacroRegion($address['macroRegion']);
            $homeAddress->setPostalCode($address['postalCode']);
            $homeAddress->setRegion($address['region']);
            $homeAddress->setStreet($address['street']);
            $homeAddress->setStreetAddress($address['streetAddress']);
            $homeAddress->setSubLocality($address['subLocality']);
            $homeAddress->setName($translator->trans('homeAddress', [], 'signup'));
            $homeAddress->setHome(true);
            
            if (is_null($homeAddress->getId()) && !empty($homeAddress->getLongitude() && !empty($homeAddress->getLatitude()))) {
                $user->addAddress($homeAddress);
            } elseif (!empty($homeAddress->getLongitude() && !empty($homeAddress->getLatitude()))) {
                $addressData = $addressManager->updateAddress($homeAddress);
                $reponseofmanager= $this->handleManagerReturnValue($addressData);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
            }

            $user->setEmail($data->get('email'));
            $user->setTelephone($data->get('telephone'));
            $user->setGivenName($data->get('givenName'));
            $user->setFamilyName($data->get('familyName'));
            $user->setGender($data->get('gender'));
            $user->setBirthYear($data->get('birthYear'));
            // cause we use FormData to post data
            $user->setNewsSubscription($data->get('newsSubscription') === "true" ? true : false);
            
            if ($user = $userManager->updateUser($user)) {
                if ($file) {
                    // Post avatar of the user
                    $image = new Image();
                    $image->setUserFile($file);
                    $image->setUserId($user->getId());
                
                    if ($image = $imageManager->createImage($image)) {
                        return new JsonResponse($image);
                    }
                    // return error if image post didnt't work
                    return new Response(json_encode('error.image'));
                }
            }
        }
        
        $userManager->getProposals($user);
        
        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
                'error' => $error,
                'alerts' => $userManager->getAlerts($user)['alerts'],
                'tabDefault' => $tabDefault,
            'proposals' => $userManager->getProposals($user)
        ]);
    }

    /**
     * User avatar delete.
     * Ajax
     */
    public function userProfileAvatarDelete(ImageManager $imageManager, UserManager $userManager)
    {
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);
        $imageId = $user->getImages()[0]->getId();
        $imageManager->deleteImage($imageId);

        return new JsonResponse($userManager->getUser($user->getId())->getAvatars());
    }

    /**
     * User password update.
     * Ajax
     */
    public function userPasswordUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('password', $user);

        $error = [
            'state' => false,
            'message' => "",
        ];

        if ($request->isMethod('POST')) {
            $error["message"] = "Ok";
            
            if ($request->request->get('password')!==null) {
                $user->setPassword($request->request->get('password'));
            } else {
                $error["state"] = "true";
                $error["message"] = "Empty password";
                return new Response(json_encode($error));
            }

            if ($user = $userManager->updateUserPassword($user)) {
                $reponseofmanager= $this->handleManagerReturnValue($user);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
                // after successful update, we re-log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                $error["message"] = "Ok";
                return new Response(json_encode($error));
            }
            $error["state"] = "true";
            $error["message"] = "Update password failed";
            return new Response(json_encode($error));
        }

        $error["state"] = "true";
        $error["message"] = "Request failed";
        return new Response(json_encode($error));
    }

    /**
     * User password recovery page.
     */
    public function userPasswordRecovery()
    {
        $this->denyAccessUnlessGranted('login');
        return $this->render('@Mobicoop/user/passwordRecovery.html.twig', []);
    }

    /**
     * Get the password of a user if it exists
     * @param UserManager $userManager The class managing the user.
     * @param Request $request The symfony request object.
     */
    public function userPasswordForRecovery(UserManager $userManager, Request $request)
    {
        $this->denyAccessUnlessGranted('login');
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (isset($data["email"]) && $data["email"]!==null) {
                return new Response(json_encode($userManager->findByEmail($data["email"], true)));
            } elseif (isset($data["phone"]) && $data["phone"]!==null) {
                // For now, the recovery by phone has been removed from front but it functionnal in the backend
                return new Response(json_encode($userManager->findByPhone($data["phone"], true)));
            }
            return new Response();
        }
    }

    /**
     * Reset password
     */
    public function userPasswordReset(UserManager $userManager, string $token)
    {
        $user = $userManager->findByPwdToken($token);

        if (empty($user) || (time() - (int)$user->getPwdTokenDate()->getTimestamp()) > 86400) {
            return $this->redirectToRoute('user_password_forgot');
        } else {
            return $this->render(
                '@Mobicoop/user/passwordRecoveryUpdate.html.twig',
                [
                        "token" => $token,
                    ]
            );
        }
    }

    /**
     * Update the new Password after recovery
     */
    public function userUpdatePasswordReset(UserManager $userManager, Request $request, string $token)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            
            $user = $userManager->findByPwdToken($token);

            $this->denyAccessUnlessGranted('password', $user);

            if (!empty($user)) {
                $user->setPassword($data["password"]);

                if ($user = $userManager->updateUserPassword($user)) {
                    // after successful update, we re-log the user
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    $userManager->flushUserToken($user);
                    return new Response(json_encode($user));
                } else {
                    return new Response(json_encode("error"));
                }
            } else {
                return new Response(json_encode("error"));
            }
        }

        return new Response();
    }


    /*************
     * PROPOSALS *
     *************/

    /**
     * Retrieve all proposals for the current user.
     */
    public function userProposalList(UserManager $userManager, ProposalManager $proposalManager)
    {
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('proposals_self', $user);
    
        $data=$proposalManager->getProposals($user);
        $reponseofmanager= $this->handleManagerReturnValue($data);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        return $this->render('@Mobicoop/proposal/index.html.twig', [
            'hydra' => $data
        ]);
    }


    /*************
     * MESSAGES  *
     *************/

    /**
     * User mailbox
     */
    public function mailBox(UserManager $userManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);

        $newThread = null;
        $idThreadDefault = null;

        if ($request->isMethod('POST')) {
            $newThread = [
                "carpool" => (int)$request->request->get('carpool'),
                "idRecipient" => (int)$request->request->get('idRecipient'),
                "familyName" => $request->request->get('familyName'),
                "givenName" => $request->request->get('givenName'),
                "avatar" => $request->request->get('avatar')
            ];
            $idThreadDefault = -1; // To preselect the new thread. Id is always -1 because it doesn't really exist yet
        }

        return $this->render('@Mobicoop/user/messages.html.twig', [
            "idUser"=>$user->getId(),
            "idThreadDefault"=>$idThreadDefault,
            "newThread" => $newThread
        ]);
    }

    /**
     * Get direct messages threads
     */
    public function userMessageDirectThreadsList(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $threads = $userManager->getThreadsDirectMessages($user);
        return new Response(json_encode($threads));
    }

    /**
     * Get carpool messages threads
     */
    public function userMessageCarpoolThreadsList(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $threads = $userManager->getThreadsCarpoolMessages($user);
        return new Response(json_encode($threads));
    }

    /**
     * Get direct messages threads
     */
    public function userMessageThread($idMessage, InternalMessageManager $internalMessageManager, UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $completeThread = $internalMessageManager->getThread($idMessage, DataProvider::RETURN_JSON);
        return new Response(json_encode($completeThread));
    }


    /**
     * Get informations for Action Panel of the mailbox
     * AJAX
     */
    public function userMessagesActionsInfos(Request $request, AskManager $askManager, AskHistoryManager $askHistoryManager, UserManager $userManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $user = $userManager->getLoggedUser();
            if ($data['idAsk']) {
                // Carpool

                /**** Begining data */
                /** To Do : Retreive data from API */
                
                $response = '{
                    "@id": "/results/999999999999",
                    "@type": "Result",
                    "canAsk": true,
                    "status": 1,
                    "resultDriver": {
                        "@id": "/result_roles/999999999999",
                        "@type": "ResultRole",
                        "outward": {
                            "@id": "/result_items/999999999999",
                            "@type": "ResultItem",
                            "proposalId": 5,
                            "matchingId": null,
                            "date": null,
                            "time": "2019-11-16T08:00:00+00:00",
                            "fromDate": "2019-11-16T15:04:00+00:00",
                            "toDate": "2119-11-16T00:00:00+00:00",
                            "origin": {
                                "@id": "/addresses/28",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Lunéville",
                                "localAdmin": "Lunéville",
                                "county": "Lunéville",
                                "macroCounty": "arrondissement de Lunéville",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.589904",
                                "longitude": "6.500118",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Lunéville",
                                    "Grand Est, France"
                                ]
                            },
                            "destination": {
                                "@id": "/addresses/29",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Nancy",
                                "localAdmin": "Nancy",
                                "county": "Nancy",
                                "macroCounty": "arrondissement de Nancy",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.690303",
                                "longitude": "6.178289",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Nancy",
                                    "Grand Est, France"
                                ]
                            },
                            "originDriver": {
                                "@id": "/addresses/999999999999",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": null,
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Lunéville",
                                "localAdmin": "Lunéville",
                                "county": "Lunéville",
                                "macroCounty": "arrondissement de Lunéville",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.589904",
                                "longitude": "6.500118",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Lunéville",
                                    "Grand Est, France"
                                ]
                            },
                            "destinationDriver": {
                                "@id": "/addresses/999999999999",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": null,
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Nancy",
                                "localAdmin": "Nancy",
                                "county": "Nancy",
                                "macroCounty": "arrondissement de Nancy",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.690303",
                                "longitude": "6.178289",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Nancy",
                                    "Grand Est, France"
                                ]
                            },
                            "originPassenger": {
                                "@id": "/addresses/28",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Lunéville",
                                "localAdmin": "Lunéville",
                                "county": "Lunéville",
                                "macroCounty": "arrondissement de Lunéville",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.589904",
                                "longitude": "6.500118",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Lunéville",
                                    "Grand Est, France"
                                ]
                            },
                            "destinationPassenger": {
                                "@id": "/addresses/29",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Nancy",
                                "localAdmin": "Nancy",
                                "county": "Nancy",
                                "macroCounty": "arrondissement de Nancy",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.690303",
                                "longitude": "6.178289",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Nancy",
                                    "Grand Est, France"
                                ]
                            },
                            "waypoints": [
                                {
                                    "id": 0,
                                    "person": "requester",
                                    "role": "driver",
                                    "time": "2019-11-16T08:00:00+00:00",
                                    "address": {
                                        "@id": "/addresses/999999999999",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": null,
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Lunéville",
                                        "localAdmin": "Lunéville",
                                        "county": "Lunéville",
                                        "macroCounty": "arrondissement de Lunéville",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.589904",
                                        "longitude": "6.500118",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Lunéville",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "origin"
                                },
                                {
                                    "id": 1,
                                    "person": "carpooler",
                                    "role": "passenger",
                                    "time": "2019-11-16T08:00:00+00:00",
                                    "address": {
                                        "@id": "/addresses/28",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": "",
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Lunéville",
                                        "localAdmin": "Lunéville",
                                        "county": "Lunéville",
                                        "macroCounty": "arrondissement de Lunéville",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.589904",
                                        "longitude": "6.500118",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Lunéville",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "origin"
                                },
                                {
                                    "id": 2,
                                    "person": "carpooler",
                                    "role": "passenger",
                                    "time": "2019-11-16T08:26:24+00:00",
                                    "address": {
                                        "@id": "/addresses/29",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": "",
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Nancy",
                                        "localAdmin": "Nancy",
                                        "county": "Nancy",
                                        "macroCounty": "arrondissement de Nancy",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.690303",
                                        "longitude": "6.178289",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Nancy",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "destination"
                                },
                                {
                                    "id": 3,
                                    "person": "requester",
                                    "role": "driver",
                                    "time": "2019-11-16T08:26:24+00:00",
                                    "address": {
                                        "@id": "/addresses/999999999999",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": null,
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Nancy",
                                        "localAdmin": "Nancy",
                                        "county": "Nancy",
                                        "macroCounty": "arrondissement de Nancy",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.690303",
                                        "longitude": "6.178289",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Nancy",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "destination"
                                }
                            ],
                            "monCheck": true,
                            "tueCheck": null,
                            "wedCheck": null,
                            "thuCheck": true,
                            "friCheck": true,
                            "satCheck": null,
                            "sunCheck": null,
                            "monTime": "2019-11-16T08:00:00+00:00",
                            "tueTime": null,
                            "wedTime": null,
                            "thuTime": "2019-11-16T08:00:00+00:00",
                            "friTime": "2019-11-16T08:00:00+00:00",
                            "satTime": null,
                            "sunTime": null,
                            "multipleTimes": false,
                            "driverPriceKm": "0.06",
                            "passengerPriceKm": "0.06",
                            "driverOriginalPrice": "2.1171",
                            "passengerOriginalPrice": "2.12",
                            "driverOriginalRoundedPrice": "2.1",
                            "passengerOriginalRoundedPrice": "2.10",
                            "computedPrice": "2.1171",
                            "computedRoundedPrice": "2.1",
                            "originalDistance": 35285,
                            "acceptedDetourDistance": 11644,
                            "newDistance": 35285,
                            "detourDistance": 0,
                            "detourDistancePercent": 0,
                            "originalDuration": 1584,
                            "acceptedDetourDuration": 522,
                            "newDuration": 1584,
                            "detourDuration": 0,
                            "detourDurationPercent": 0,
                            "commonDistance": 35285
                        },
                        "return": null,
                        "seats": 1
                    },
                    "resultPassenger": {
                        "@id": "/result_roles/999999999999",
                        "@type": "ResultRole",
                        "outward": {
                            "@id": "/result_items/999999999999",
                            "@type": "ResultItem",
                            "proposalId": 5,
                            "matchingId": null,
                            "date": null,
                            "time": "2019-11-16T08:00:00+00:00",
                            "fromDate": "2019-11-16T15:04:00+00:00",
                            "toDate": "2119-11-16T00:00:00+00:00",
                            "origin": {
                                "@id": "/addresses/28",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Lunéville",
                                "localAdmin": "Lunéville",
                                "county": "Lunéville",
                                "macroCounty": "arrondissement de Lunéville",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.589904",
                                "longitude": "6.500118",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Lunéville",
                                    "Grand Est, France"
                                ]
                            },
                            "destination": {
                                "@id": "/addresses/29",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Nancy",
                                "localAdmin": "Nancy",
                                "county": "Nancy",
                                "macroCounty": "arrondissement de Nancy",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.690303",
                                "longitude": "6.178289",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Nancy",
                                    "Grand Est, France"
                                ]
                            },
                            "originDriver": {
                                "@id": "/addresses/28",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Lunéville",
                                "localAdmin": "Lunéville",
                                "county": "Lunéville",
                                "macroCounty": "arrondissement de Lunéville",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.589904",
                                "longitude": "6.500118",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Lunéville",
                                    "Grand Est, France"
                                ]
                            },
                            "destinationDriver": {
                                "@id": "/addresses/29",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": "",
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Nancy",
                                "localAdmin": "Nancy",
                                "county": "Nancy",
                                "macroCounty": "arrondissement de Nancy",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.690303",
                                "longitude": "6.178289",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Nancy",
                                    "Grand Est, France"
                                ]
                            },
                            "originPassenger": {
                                "@id": "/addresses/999999999999",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": null,
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Lunéville",
                                "localAdmin": "Lunéville",
                                "county": "Lunéville",
                                "macroCounty": "arrondissement de Lunéville",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.589904",
                                "longitude": "6.500118",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Lunéville",
                                    "Grand Est, France"
                                ]
                            },
                            "destinationPassenger": {
                                "@id": "/addresses/999999999999",
                                "@type": "Address",
                                "houseNumber": null,
                                "street": null,
                                "streetAddress": null,
                                "postalCode": null,
                                "subLocality": null,
                                "addressLocality": "Nancy",
                                "localAdmin": "Nancy",
                                "county": "Nancy",
                                "macroCounty": "arrondissement de Nancy",
                                "region": "Meurthe-et-Moselle",
                                "macroRegion": "Grand Est",
                                "addressCountry": "France",
                                "countryCode": "FRA",
                                "latitude": "48.690303",
                                "longitude": "6.178289",
                                "elevation": null,
                                "name": null,
                                "venue": null,
                                "home": null,
                                "displayLabel": [
                                    "Nancy",
                                    "Grand Est, France"
                                ]
                            },
                            "waypoints": [
                                {
                                    "id": 0,
                                    "person": "carpooler",
                                    "role": "driver",
                                    "time": "2019-11-16T08:00:00+00:00",
                                    "address": {
                                        "@id": "/addresses/28",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": "",
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Lunéville",
                                        "localAdmin": "Lunéville",
                                        "county": "Lunéville",
                                        "macroCounty": "arrondissement de Lunéville",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.589904",
                                        "longitude": "6.500118",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Lunéville",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "origin"
                                },
                                {
                                    "id": 1,
                                    "person": "requester",
                                    "role": "passenger",
                                    "time": "2019-11-16T08:00:00+00:00",
                                    "address": {
                                        "@id": "/addresses/999999999999",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": null,
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Lunéville",
                                        "localAdmin": "Lunéville",
                                        "county": "Lunéville",
                                        "macroCounty": "arrondissement de Lunéville",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.589904",
                                        "longitude": "6.500118",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Lunéville",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "origin"
                                },
                                {
                                    "id": 2,
                                    "person": "requester",
                                    "role": "passenger",
                                    "time": "2019-11-16T08:26:24+00:00",
                                    "address": {
                                        "@id": "/addresses/999999999999",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": null,
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Nancy",
                                        "localAdmin": "Nancy",
                                        "county": "Nancy",
                                        "macroCounty": "arrondissement de Nancy",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.690303",
                                        "longitude": "6.178289",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Nancy",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "destination"
                                },
                                {
                                    "id": 3,
                                    "person": "carpooler",
                                    "role": "driver",
                                    "time": "2019-11-16T08:26:24+00:00",
                                    "address": {
                                        "@id": "/addresses/29",
                                        "@type": "Address",
                                        "houseNumber": null,
                                        "street": null,
                                        "streetAddress": "",
                                        "postalCode": null,
                                        "subLocality": null,
                                        "addressLocality": "Nancy",
                                        "localAdmin": "Nancy",
                                        "county": "Nancy",
                                        "macroCounty": "arrondissement de Nancy",
                                        "region": "Meurthe-et-Moselle",
                                        "macroRegion": "Grand Est",
                                        "addressCountry": "France",
                                        "countryCode": "FRA",
                                        "latitude": "48.690303",
                                        "longitude": "6.178289",
                                        "elevation": null,
                                        "name": null,
                                        "venue": null,
                                        "home": null,
                                        "displayLabel": [
                                            "Nancy",
                                            "Grand Est, France"
                                        ]
                                    },
                                    "type": "destination"
                                }
                            ],
                            "monCheck": true,
                            "tueCheck": null,
                            "wedCheck": null,
                            "thuCheck": true,
                            "friCheck": true,
                            "satCheck": null,
                            "sunCheck": null,
                            "monTime": "2019-11-16T08:00:00+00:00",
                            "tueTime": null,
                            "wedTime": null,
                            "thuTime": "2019-11-16T08:00:00+00:00",
                            "friTime": "2019-11-16T08:00:00+00:00",
                            "satTime": null,
                            "sunTime": null,
                            "multipleTimes": false,
                            "driverPriceKm": "0.06",
                            "passengerPriceKm": "0.06",
                            "driverOriginalPrice": "2.12",
                            "passengerOriginalPrice": "2.1171",
                            "driverOriginalRoundedPrice": "2.1",
                            "passengerOriginalRoundedPrice": "2.1",
                            "computedPrice": "2.1171",
                            "computedRoundedPrice": "2.1",
                            "originalDistance": 35285,
                            "acceptedDetourDistance": 11644,
                            "newDistance": 35285,
                            "detourDistance": 0,
                            "detourDistancePercent": 0,
                            "originalDuration": 1584,
                            "acceptedDetourDuration": 522,
                            "newDuration": 1584,
                            "detourDuration": 0,
                            "detourDurationPercent": 0,
                            "commonDistance": 35285
                        },
                        "return": null,
                        "seats": 3
                    },
                    "carpooler": {
                        "@id": "/users/5",
                        "@type": "User",
                        "id": 5,
                        "givenName": "Tak",
                        "familyName": "Tik",
                        "shortFamilyName": "T.",
                        "email": "tak.tik@yopmail.com",
                        "birthDate": "1998-01-01T00:00:00+00:00",
                        "telephone": "0908070808",
                        "images": [
                            {
                                "@id": "/images/9",
                                "@type": "Image",
                                "fileName": "7026f5a959f8489dcabd0eb2eb7d72-1.png",
                                "userId": null,
                                "versions": {
                                    "max": "http://localhost:8080/upload/users/images/versions/7026f5a959f8489dcabd0eb2eb7d72-1.png",
                                    "square_800": "http://localhost:8080/upload/users/images/versions/800-7026f5a959f8489dcabd0eb2eb7d72-1.png",
                                    "square_250": "http://localhost:8080/upload/users/images/versions/250-7026f5a959f8489dcabd0eb2eb7d72-1.png",
                                    "square_100": "http://localhost:8080/upload/users/images/versions/100-7026f5a959f8489dcabd0eb2eb7d72-1.png"
                                }
                            }
                        ],
                        "avatars": [
                            "http://localhost:8080/upload/users/images/versions/100-7026f5a959f8489dcabd0eb2eb7d72-1.png",
                            "http://localhost:8080/upload/users/images/versions/250-7026f5a959f8489dcabd0eb2eb7d72-1.png"
                        ]
                    },
                    "frequency": 2,
                    "frequencyResult": 2,
                    "origin": {
                        "@id": "/addresses/999999999999",
                        "@type": "Address",
                        "houseNumber": null,
                        "street": null,
                        "streetAddress": null,
                        "postalCode": null,
                        "subLocality": null,
                        "addressLocality": "Lunéville",
                        "localAdmin": "Lunéville",
                        "county": "Lunéville",
                        "macroCounty": "arrondissement de Lunéville",
                        "region": "Meurthe-et-Moselle",
                        "macroRegion": "Grand Est",
                        "addressCountry": "France",
                        "countryCode": "FRA",
                        "latitude": "48.589904",
                        "longitude": "6.500118",
                        "elevation": null,
                        "name": null,
                        "venue": null,
                        "home": null,
                        "displayLabel": [
                            "Lunéville",
                            "Grand Est, France"
                        ]
                    },
                    "originFirst": true,
                    "destination": {
                        "@id": "/addresses/999999999999",
                        "@type": "Address",
                        "houseNumber": null,
                        "street": null,
                        "streetAddress": null,
                        "postalCode": null,
                        "subLocality": null,
                        "addressLocality": "Nancy",
                        "localAdmin": "Nancy",
                        "county": "Nancy",
                        "macroCounty": "arrondissement de Nancy",
                        "region": "Meurthe-et-Moselle",
                        "macroRegion": "Grand Est",
                        "addressCountry": "France",
                        "countryCode": "FRA",
                        "latitude": "48.690303",
                        "longitude": "6.178289",
                        "elevation": null,
                        "name": null,
                        "venue": null,
                        "home": null,
                        "displayLabel": [
                            "Nancy",
                            "Grand Est, France"
                        ]
                    },
                    "destinationLast": true,
                    "originDriver": {
                        "@id": "/addresses/28",
                        "@type": "Address",
                        "houseNumber": null,
                        "street": null,
                        "streetAddress": "",
                        "postalCode": null,
                        "subLocality": null,
                        "addressLocality": "Lunéville",
                        "localAdmin": "Lunéville",
                        "county": "Lunéville",
                        "macroCounty": "arrondissement de Lunéville",
                        "region": "Meurthe-et-Moselle",
                        "macroRegion": "Grand Est",
                        "addressCountry": "France",
                        "countryCode": "FRA",
                        "latitude": "48.589904",
                        "longitude": "6.500118",
                        "elevation": null,
                        "name": null,
                        "venue": null,
                        "home": null,
                        "displayLabel": [
                            "Lunéville",
                            "Grand Est, France"
                        ]
                    },
                    "destinationDriver": {
                        "@id": "/addresses/29",
                        "@type": "Address",
                        "houseNumber": null,
                        "street": null,
                        "streetAddress": "",
                        "postalCode": null,
                        "subLocality": null,
                        "addressLocality": "Nancy",
                        "localAdmin": "Nancy",
                        "county": "Nancy",
                        "macroCounty": "arrondissement de Nancy",
                        "region": "Meurthe-et-Moselle",
                        "macroRegion": "Grand Est",
                        "addressCountry": "France",
                        "countryCode": "FRA",
                        "latitude": "48.690303",
                        "longitude": "6.178289",
                        "elevation": null,
                        "name": null,
                        "venue": null,
                        "home": null,
                        "displayLabel": [
                            "Nancy",
                            "Grand Est, France"
                        ]
                    },
                    "originPassenger": {
                        "@id": "/addresses/999999999999",
                        "@type": "Address",
                        "houseNumber": null,
                        "street": null,
                        "streetAddress": null,
                        "postalCode": null,
                        "subLocality": null,
                        "addressLocality": "Lunéville",
                        "localAdmin": "Lunéville",
                        "county": "Lunéville",
                        "macroCounty": "arrondissement de Lunéville",
                        "region": "Meurthe-et-Moselle",
                        "macroRegion": "Grand Est",
                        "addressCountry": "France",
                        "countryCode": "FRA",
                        "latitude": "48.589904",
                        "longitude": "6.500118",
                        "elevation": null,
                        "name": null,
                        "venue": null,
                        "home": null,
                        "displayLabel": [
                            "Lunéville",
                            "Grand Est, France"
                        ]
                    },
                    "destinationPassenger": {
                        "@id": "/addresses/999999999999",
                        "@type": "Address",
                        "houseNumber": null,
                        "street": null,
                        "streetAddress": null,
                        "postalCode": null,
                        "subLocality": null,
                        "addressLocality": "Nancy",
                        "localAdmin": "Nancy",
                        "county": "Nancy",
                        "macroCounty": "arrondissement de Nancy",
                        "region": "Meurthe-et-Moselle",
                        "macroRegion": "Grand Est",
                        "addressCountry": "France",
                        "countryCode": "FRA",
                        "latitude": "48.690303",
                        "longitude": "6.178289",
                        "elevation": null,
                        "name": null,
                        "venue": null,
                        "home": null,
                        "displayLabel": [
                            "Nancy",
                            "Grand Est, France"
                        ]
                    },
                    "date": null,
                    "time": null,
                    "startDate": "2019-11-16T15:04:00+00:00",
                    "toDate": "2119-11-16T00:00:00+00:00",
                    "seats": 3,
                    "price": "2.1171",
                    "roundedPrice": "2.1",
                    "comment": null,
                    "monCheck": true,
                    "tueCheck": false,
                    "wedCheck": false,
                    "thuCheck": true,
                    "friCheck": true,
                    "satCheck": false,
                    "sunCheck": false,
                    "outwardTime": "2019-11-16T08:00:00+00:00",
                    "returnTime": null,
                    "return": false
                }';
    
                return new Response($response);
            } else {
                // Direct
                $recipient = $userManager->getUser($data['idRecipient']);
                $response = [
                    'avatar'=>$recipient->getAvatars()[0],
                    'recipientName'=>$recipient->getGivenName()." ".$recipient->getShortFamilyName(),
                ];
                return new JsonResponse($response);
            }
        }
        return new JsonResponse();
    }

    
    public function userMessageSend(UserManager $userManager, InternalMessageManager $internalMessageManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('messages', $user);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $idThreadMessage = ($data['idThreadMessage']==-1) ? null : $data['idThreadMessage'];
            $idAsk = (isset($data['idAsk']) && !is_null($data['idAsk'])) ? $data['idAsk'] : null;
            $text = $data['text'];
            $idRecipient = $data['idRecipient'];

            $messageToSend = $internalMessageManager->createInternalMessage(
                $user,
                $idRecipient,
                "",
                $text,
                $idThreadMessage
            );

            if ($idAsk!==null) {
                $messageToSend->setIdAsk($idAsk);
            }
            
            return new Response($internalMessageManager->sendInternalMessage($messageToSend, DataProvider::RETURN_JSON));
        }
        return new Response(json_encode("Not a post"));
    }
    
    
    /**
     * Update and ask
     * Ajax Request
     */
    public function userMessageUpdateAsk(Request $request, AskManager $askManager, AskHistoryManager $askHistoryManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $idAsk = $data['idAsk'];
            $status = $data['status'];
            $criteria = ($data['criteria']) ? $data['criteria'] : null;
            //var_dump($criteria);die;

            // Get the Ask
            $ask = $askManager->getAsk($idAsk);
            /** TO DO : Get the Ask of the return */
            $askReturn = null;

            $reponseofmanager= $this->handleManagerReturnValue($ask);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
            
            // Change the status
            if ($status!==null && is_numeric($status)) {
                $ask->setStatus($status);
            }
            
            // If we need to, we update the criteria
            if ($criteria!==null) {

                /** TO DO : Get the criteria of the return */

                $ask->getCriteria()->setFromDate(new \DateTime($criteria['fromDate']));
                $ask->getCriteria()->setToDate(new \DateTime($criteria['toDate']));

                if (isset($criteria['outwardSchedule'])) {
                    $ask->getCriteria()->setMonCheck(($criteria['outwardSchedule']['monTime']) ? true : false);
                    $ask->getCriteria()->setTueCheck(($criteria['outwardSchedule']['tueTime']) ? true : false);
                    $ask->getCriteria()->setWedCheck(($criteria['outwardSchedule']['wedTime']) ? true : false);
                    $ask->getCriteria()->setThuCheck(($criteria['outwardSchedule']['thuTime']) ? true : false);
                    $ask->getCriteria()->setFriCheck(($criteria['outwardSchedule']['friTime']) ? true : false);
                    $ask->getCriteria()->setSatCheck(($criteria['outwardSchedule']['satTime']) ? true : false);
                    $ask->getCriteria()->setSunCheck(($criteria['outwardSchedule']['sunTime']) ? true : false);
                }


                if ($askReturn!==null && isset($criteria['returnSchedule'])) {
                    $askReturn->getCriteria()->setMonCheck(($criteria['returnSchedule']['monTime']) ? true : false);
                    $askReturn->getCriteria()->setTueCheck(($criteria['returnSchedule']['tueTime']) ? true : false);
                    $askReturn->getCriteria()->setWedCheck(($criteria['returnSchedule']['wedTime']) ? true : false);
                    $askReturn->getCriteria()->setThuCheck(($criteria['returnSchedule']['thuTime']) ? true : false);
                    $askReturn->getCriteria()->setFriCheck(($criteria['returnSchedule']['friTime']) ? true : false);
                    $askReturn->getCriteria()->setSatCheck(($criteria['returnSchedule']['satTime']) ? true : false);
                    $askReturn->getCriteria()->setSunCheck(($criteria['returnSchedule']['sunTime']) ? true : false);
                }
            }

            // Update the Ask via API
            $ask = $askManager->updateAsk($ask);

            // To do : Update the return Ask

            $return = [
                "id"=>$ask->getId(),
                "status"=>$ask->getStatus()
            ];

            return new JsonResponse($return);
        }

        return new JsonResponse("Not a post");
    }

    /**
     * Connect a user by his facebook credentials
     * AJAX
     */
    public function userFacebookConnect(UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            
            // We get the user by his email
            if (!empty($data["email"])) {
                $user = $userManager->findByEmail($data["email"]);
                if ($user && $user->getFacebookId()===$data["personalID"]) {
                    // Same Facebook ID in BDD that the one from the front component. We log the user.
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));

                    return new JsonResponse($user->getFacebookId());
                } else {
                    return new JsonResponse(['error'=>'userFBNotFound']);
                }
            } else {
                return new JsonResponse(['error'=>'userFBNotFound']);
            }
        }

        return new JsonResponse(['error'=>'errorCredentialsFacebook']);
    }

    /**
     * Update a user alert
     * AJAX
     */
    public function updateAlert(UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = $userManager->getLoggedUser();
            $data = json_decode($request->getContent(), true);

            $responseUpdate = $userManager->updateAlert($user, $data["id"], $data["active"]);
            return new JsonResponse($responseUpdate);
        }
        return new JsonResponse(['error'=>'errorUpdateAlert']);
    }

    /**
     * User carpool settings update.
     * Ajax
     *
     * @param UserManager $userManager
     * @param Request $request
     * @return JsonResponse
     */
    public function userCarpoolSettingsUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('update', $user);
        
        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true);

            $user->setSmoke($data["smoke"]);
            $user->setMusic($data["music"]);
            $user->setMusicFavorites($data["musicFavorites"]);
            $user->setChat($data["chat"]);
            $user->setChatFavorites($data["chatFavorites"]);
            
            if ($response = $userManager->updateUser($user)) {
                $reponseofmanager= $this->handleManagerReturnValue($response);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
                return new JsonResponse(
                    ['message'=>'success'],
                    Response::HTTP_ACCEPTED
                );
            }
            return new JsonResponse(
                ["message" => "error"],
                Response::HTTP_BAD_REQUEST
            );
        }
        return new JsonResponse(
            ['message'=>'error'],
            Response::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * Get all communities of a user
     * AJAX
     */
    public function userCommunities(Request $request, CommunityManager $communityManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $communities = [];
            if ($communityUsers = $communityManager->getAllCommunityUser($data['userId'])) {
                foreach ($communityUsers as $communityUser) {
                    $communities[] = $communityUser->getCommunity();
                }
            }
            return new JsonResponse($communities);
        }
        return new JsonResponse(['error'=>'errorUpdateAlert']);
    }
}
