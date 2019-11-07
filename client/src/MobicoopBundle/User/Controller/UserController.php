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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\AskHistory;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AskHistoryManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class UserController extends AbstractController
{
    use HydraControllerTrait;

    private $encoder;
    private $facebook_show;
    private $facebook_appid;
    private $required_home_address;

    /**
     * Constructor
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder, $facebook_show, $facebook_appid, $required_home_address)
    {
        $this->encoder = $encoder;
        $this->facebook_show = $facebook_show;
        $this->facebook_appid = $facebook_appid;
        $this->required_home_address = $required_home_address;
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
            $user->setBirthYear($data['birthYear']);

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
    public function userProfileUpdate(UserManager $userManager, Request $request, ImageManager $imageManager, AddressManager $addressManager, TranslatorInterface $translator)
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
            
            if ($user = $userManager->updateUser($user)) {
                if ($file) {
                    // Post avatar of the user
                    $image = new Image();
                    $image->setUserFile($file);
                    $image->setUserId($user->getId());
                
                    if ($image = $imageManager->createImage($image)) {
                        return new Response();
                    }
                    // return error if image post didnt't work
                    return new Response(json_encode('error.image'));
                }
            }
        }
        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
                'error' => $error,
                'alerts' => $userManager->getAlerts($user)['alerts']
            ]);
    }

    /**
     * User avatar delete.
     * Ajax
     */
    public function userProfileAvatarDelete(ImageManager $imageManager, UserManager $userManager)
    {
        $user = clone $userManager->getLoggedUser();
        $imageId = $user->getImages()[0]->getId();
        $imageManager->deleteImage($imageId);

        return new Response();
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
        return $this->render('@Mobicoop/user/passwordRecovery.html.twig', []);
    }

    /**
     * Get the password of a user if it exists
     * @param UserManager $userManager The class managing the user.
     * @param Request $request The symfony request object.
     */
    public function userPasswordForRecovery(UserManager $userManager, Request $request)
    {
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
                "givenName" => $request->request->get('givenName')
            ];
            $idThreadDefault = -1; // To preselect the new thread. Id is always -1 because it doesn't really exist yet
        }

        return $this->render('@Mobicoop/user/messages.html.twig', [
            "idUser"=>$user->getId(),
            "idThreadDefault"=>$idThreadDefault,
            "newThread" => $newThread
        ]);
    }

    /*************** NEW VERSION */
    /**
     * Get direct messages threads
     */
    public function userMessageDirectThreadsList(UserManager $userManager, InternalMessageManager $internalMessageManager)
    {
        $user = $userManager->getLoggedUser();
        $threads = $userManager->getThreadsDirectMessages($user);
        return new Response(json_encode($threads));
    }

    /**
     * Get carpool messages threads
     */
    public function userMessageCarpoolThreadsList(UserManager $userManager, InternalMessageManager $internalMessageManager)
    {
        $user = $userManager->getLoggedUser();
        $threads = $userManager->getThreadsCarpoolMessages($user);
        return new Response(json_encode($threads));
    }

    /**
     * Get direct messages threads
     */
    public function userMessageThread($idMessage, InternalMessageManager $internalMessageManager)
    {
        $completeThread = $internalMessageManager->getThread($idMessage, DataProvider::RETURN_JSON);
        return new Response(json_encode($completeThread));
    }

    /*************** END NEW VERSION */

    /**
     * User messages.
     * OLD Controller
     */
    public function userMessageList(UserManager $userManager, InternalMessageManager $internalMessageManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);

        $threadsDirectMessagesForView = [];
        $threadsCarpoolingMessagesForView = [];
        $idMessageDefault = null;
        $idRecipientDefault = null;
        $firstNameRecipientDefault = "";
        $lastNameRecipientDefault = "";

        // Building threads array
        $threads = $userManager->getThreads($user);
        $reponseofmanager= $this->handleManagerReturnValue($threads);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $idMessageDefaultSelected = false;

        foreach ($threads["threads"] as $thread) {
            $arrayThread = [];

            $arrayThread["idThreadMessage"] = $thread["id"];
            if (!isset($thread["user"]["id"])) {
                // the user is the sender
                $arrayThread["contactId"] =  $thread["recipients"][0]["user"]["id"];
                $arrayThread["contactFirstName"] = $thread["recipients"][0]["user"]["givenName"];
                $arrayThread["contactLastName"] = $thread["recipients"][0]["user"]["familyName"];
            } else {
                // the user is the recipient
                $arrayThread["contactId"] =  $thread["user"]["id"];
                $arrayThread["contactFirstName"] = $thread["user"]["givenName"];
                $arrayThread["contactLastName"] = $thread["user"]["familyName"];
            }
            $arrayThread["text"] = $thread["text"];
            $arrayThread["askHistory"] = $thread["askHistory"];

            // The default message is the first direct message or the last carpooling message
            if (!$idMessageDefaultSelected || !is_null($thread["askHistory"])) {
                $idMessageDefault = $thread["id"];
                $idRecipientDefault = $arrayThread["contactId"];
                $firstNameRecipientDefault = $arrayThread["contactFirstName"];
                $lastNameRecipientDefault = $arrayThread["contactLastName"];
                $arrayThread["selected"] = true;
                $idMessageDefaultSelected = true;

                // For the summary of the journey
                if (!is_null($thread["askHistory"])) {
                    $arrayThread["firstWayPoint"] = $thread["askHistory"]["ask"]["matching"]["waypoints"][0]["address"]["addressLocality"];
                    $arrayThread["lastWayPoint"] = $thread["askHistory"]["ask"]["matching"]["waypoints"][count($thread["askHistory"]["ask"]["matching"]["waypoints"])-1]["address"]["addressLocality"];

                    if ($thread["askHistory"]["ask"]["matching"]['criteria']["frequency"]==1) {
                        // Punctual, we show the from date
                        $fromDate = new DateTime($thread["askHistory"]["ask"]["matching"]['criteria']["fromDate"]);
                        $fromTime = new DateTime($thread["askHistory"]["ask"]["matching"]['criteria']["fromTime"]);
                        $arrayThread["fromDateReadable"] = $fromDate->format("D d F Y");
                        $arrayThread["fromTimeReadable"] = $fromTime->format("H\hi");
                    } else {
                        // Regular
                        $dayChecked = [];
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["monCheck"]!==null) {
                            $dayChecked[] = "monday";
                        }
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["tueCheck"]!==null) {
                            $dayChecked[] = "tuesday";
                        }
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["wedCheck"]!==null) {
                            $dayChecked[] = "wednesday";
                        }
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["thuCheck"]!==null) {
                            $dayChecked[] = "thursday";
                        }
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["friCheck"]!==null) {
                            $dayChecked[] = "friday";
                        }
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["satCheck"]!==null) {
                            $dayChecked[] = "saturday";
                        }
                        if ($thread["askHistory"]["ask"]["matching"]['criteria']["sunCheck"]!==null) {
                            $dayChecked[] = "sunday";
                        }
                        $arrayThread["dayChecked"] = $dayChecked;
                    }
                }

                // I need the send date of the last message of this thread
                $completeThread = $internalMessageManager->getThread($thread["id"], DataProvider::RETURN_JSON);
                if ($completeThread["messages"]!==null && count($completeThread["messages"])>0) {
                    //$lastMessageCreatedDate = new DateTime($completeThread["messages"][count($completeThread["messages"])-1]["createdDate"]);
                    //$arrayThread["lastMessageCreatedDate"] = $lastMessageCreatedDate->format("d M Y");
                    $arrayThread["lastMessageCreatedDate"] = $completeThread["messages"][count($completeThread["messages"])-1]["createdDate"];
                } else {
                    //$lastMessageCreatedDate = new DateTime($completeThread["createdDate"]);
                    //$arrayThread["lastMessageCreatedDate"] = $lastMessageCreatedDate->format("d M Y");
                    $arrayThread["lastMessageCreatedDate"] = $completeThread["createdDate"];
                }
                // If it's today i just show... today
                $today = new DateTime(date("Y-m-d"));
                if ($today->format("d F Y")===$arrayThread["lastMessageCreatedDate"]) {
                    $arrayThread["lastMessageCreatedDate"] = "today";
                }
            }

            // Push on the right array
            (is_null($thread["askHistory"])) ? $threadsDirectMessagesForView[] = $arrayThread : $threadsCarpoolingMessagesForView[] = $arrayThread;
        }
        
        return $this->render('@Mobicoop/user/messages.html.twig', [
            'threadsDirectMessagesForView' => $threadsDirectMessagesForView,
            'threadsCarpoolingMessagesForView' => $threadsCarpoolingMessagesForView,
            'userId' => $user->getId(),
            'idMessageDefault' => $idMessageDefault,
            'idRecipientDefault'=>$idRecipientDefault,
            'firstNameRecipientDefault'=>$firstNameRecipientDefault,
            'lastNameRecipientDefault'=>$lastNameRecipientDefault,
        ]);
    }

    /**
     * Get a complete thread from a first message
     * Ajax Request
     * OLD Controller
     */
    public function userMessageThreadOld(int $idFirstMessage, UserManager $userManager, InternalMessageManager $internalMessageManager, AskManager $askManager)
    {
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('messages', $user);

        $thread = $internalMessageManager->getThread($idFirstMessage, DataProvider::RETURN_JSON);
        $reponseofmanager= $this->handleManagerReturnValue($thread);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }

        // Format the date with a human readable version
        // First message
        $createdDateFirstMessage = new DateTime($thread["createdDate"]);
        $thread["createdDateReadable"] = $createdDateFirstMessage->format("D d F Y");
        $thread["createdTimeReadable"] = $createdDateFirstMessage->format("H:i:s");

        // Children messages
        foreach ($thread["messages"] as $key => $message) {
            $createdDate = new DateTime($message["createdDate"]);
            $thread["messages"][$key]["createdDateReadable"] = $createdDate->format("D d F Y");
            $thread["messages"][$key]["createdTimeReadable"] = $createdDate->format("H:i:s");
        }
        
        if (!is_null($thread["askHistory"])) {
            // Get the last AskHistory
            // You do that because you can have a AskHistory without a message
            $askHistories = $askManager->getAskHistories($thread["askHistory"]["ask"]["id"]);
            $reponseofmanager= $this->handleManagerReturnValue($askHistories);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
            $thread["lastAskHistory"] = end($askHistories);

            $fromDate = new DateTime($thread["lastAskHistory"]["ask"]["matching"]["criteria"]["fromDate"]);
            $thread["lastAskHistory"]["ask"]["matching"]["criteria"]["fromDateReadable"] = $fromDate->format("D d F Y");
            $fromTime = new DateTime($thread["lastAskHistory"]["ask"]["matching"]["criteria"]["fromTime"]);
            $thread["lastAskHistory"]["ask"]["matching"]["criteria"]["fromTimeReadable"] = $fromTime->format("G\hi");
        } else {
            $thread["lastAskHistory"] = null;
        }

        return new Response(json_encode($thread));
    }

    /**
     * Send an internal message to another user
     * Ajax Request
     */
    public function userMessageSend(UserManager $userManager, InternalMessageManager $internalMessageManager, Request $request, AskHistoryManager $askHistoryManager)
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
            $text = $data['text'];
            $idRecipient = $data['idRecipient'];
            $idAskHistory = $data['idAskHistory'];

            $messageToSend = $internalMessageManager->createInternalMessage(
                $user,
                $idRecipient,
                "",
                $text,
                $idThreadMessage
            );
            $reponseofmanager= $this->handleManagerReturnValue($messageToSend);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            // If there is an AskHistory i will post an AskHistory with the message within. If not, i only send a Message.
            if ($idAskHistory!==null) {
                
                // Get the current AskHistory
                $currentAskHistory = $askHistoryManager->getAskHistory($idAskHistory);
                $reponseofmanager= $this->handleManagerReturnValue($currentAskHistory);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }

                // Create the new Ask History to post
                $askHistory = new AskHistory();
                $askHistory->setMessage($messageToSend);
                $askHistory->setAsk($currentAskHistory->getAsk());
                $askHistory->setStatus($currentAskHistory->getStatus());
                $askHistory->setType($currentAskHistory->getType());

                // print_r($askHistoryManager->createAskHistory($askHistory));
                // die;
                return new Response($askHistoryManager->createAskHistory($askHistory, DataProvider::RETURN_JSON));
            } else {
                return new Response($internalMessageManager->sendInternalMessage($messageToSend, DataProvider::RETURN_JSON));
            }
        }
        return new Response(json_encode("Not a post"));
    }

    /**
     * Update and ask
     * Ajax Request
     */
    public function userMessageUpdateAsk(Request $request, AskManager $askManager)
    {
        if ($request->isMethod('POST')) {
            $idAsk = $request->request->get('idAsk');

            // Get the Ask
            $ask = $askManager->getAsk($idAsk);
            $reponseofmanager= $this->handleManagerReturnValue($ask);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            // Change the status
            if ($request->request->get('status')!==null &&
                is_numeric($request->request->get('status'))
            ) {
                // Modify the Ask status
                $ask->setStatus($request->request->get('status'));
            }
            
            // Update the Ask via API
            $ask = $askManager->updateAsk($ask);
            $reponseofmanager= $this->handleManagerReturnValue($ask);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            $return = [
                "id"=>$ask->getId(),
                "status"=>$ask->getStatus()
            ];

            return new Response(json_encode($return));
        }

        return new Response(json_encode("Not a post"));
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
}
