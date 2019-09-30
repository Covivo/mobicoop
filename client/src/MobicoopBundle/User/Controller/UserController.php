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

use App\Communication\Entity\Email;
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
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\ProposalForm;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Form\Login;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserLoginForm;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserDeleteForm;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Geography\Service\AddressManager;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use Mobicoop\Bundle\MobicoopBundle\Communication\Service\InternalMessageManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Recipient;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AskManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\AskHistory;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AskHistoryManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use function GuzzleHttp\json_encode;
use function MongoDB\BSON\fromJSON;
use function MongoDB\BSON\toJSON;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class UserController extends AbstractController
{
    use HydraControllerTrait;
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
            "errorMessage"=>$errorMessage
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

            // create user in database
            $data= $userManager->createUser($user);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }
 
        return $this->render('@Mobicoop/user/signup.html.twig', [
                'error' => $error
            ]);
    }

    /**
     * User profile update.
     */
    public function userProfileUpdate(UserManager $userManager, Request $request, AddressManager $addressManager, TranslatorInterface $translator)
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
            $data = json_decode($request->getContent(), true);
          
            if (!$homeAddress) {
                $homeAddress = new Address();
            }
            $homeAddress->setAddressCountry($data['homeAddress']['addressCountry']);
            $homeAddress->setAddressLocality($data['homeAddress']['addressLocality']);
            $homeAddress->setCountryCode($data['homeAddress']['countryCode']);
            $homeAddress->setCounty($data['homeAddress']['county']);
            $homeAddress->setLatitude($data['homeAddress']['latitude']);
            $homeAddress->setLocalAdmin($data['homeAddress']['localAdmin']);
            $homeAddress->setLongitude($data['homeAddress']['longitude']);
            $homeAddress->setMacroCounty($data['homeAddress']['macroCounty']);
            $homeAddress->setMacroRegion($data['homeAddress']['macroRegion']);
            $homeAddress->setPostalCode($data['homeAddress']['postalCode']);
            $homeAddress->setRegion($data['homeAddress']['region']);
            $homeAddress->setStreet($data['homeAddress']['street']);
            $homeAddress->setStreetAddress($data['homeAddress']['streetAddress']);
            $homeAddress->setSubLocality($data['homeAddress']['subLocality']);
            $homeAddress->setName($translator->trans('homeAddress', [], 'signup'));
            $homeAddress->setHome(true);
            
            if (is_null($homeAddress->getId()) && !empty($homeAddress->getLongitude() && !empty($homeAddress->getLatitude()))) {
                $user->addAddress($homeAddress);
            } elseif (!empty($homeAddress->getLongitude() && !empty($homeAddress->getLatitude()))) {
                $data= $addressManager->updateAddress($homeAddress);
                $reponseofmanager= $this->handleManagerReturnValue($data);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
            }

            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setGivenName($data['givenName']);
            $user->setFamilyName($data['familyName']);
            $user->setGender($data['gender']);
            $user->setBirthYear($data['birthYear']);
            $data = $userManager->updateUser($user);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }
        
        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
                'error' => $error,
                'user' => $user,
            ]);
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
    public function getUserPasswordForRecovery(UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (isset($data["email"]) && $data["email"]!==null) {
                return new Response(json_encode($userManager->findByEmail($data["email"], true)));
            } elseif (isset($data["phone"]) && $data["phone"]!==null) {
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
    }

    /**
     * Delete the current user.
     */
    public function userProfileDelete(UserManager $userManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('delete', $user);

        $form = $this->createForm(
            UserDeleteForm::class,
            $user,
            ['validation_groups'=>['delete']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($data=$userManager->deleteUser($user->getId())) {
                $reponseofmanager= $this->handleManagerReturnValue($data);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
                return $this->redirectToRoute('home');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/user/delete.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'error' => $error
        ]);
    }

    /**
     * Retrieve all proposals for the current user.
     */
    public function userProposals(UserManager $userManager, ProposalManager $proposalManager)
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

    /**
     * User messages.
     */
    public function userMessages(UserManager $userManager, InternalMessageManager $internalMessageManager)
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
     */
    public function getThread(int $idFirstMessage, UserManager $userManager, InternalMessageManager $internalMessageManager, AskManager $askManager)
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
    public function sendInternalMessage(UserManager $userManager, InternalMessageManager $internalMessageManager, Request $request, AskHistoryManager $askHistoryManager)
    {
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('messages', $user);

        if ($request->isMethod('POST')) {
            $idThreadMessage = $request->request->get('idThreadMessage');
            $idRecipient = $request->request->get('idRecipient');

            $messageToSend = $internalMessageManager->createInternalMessage(
                $user,
                $idRecipient,
                "",
                $request->request->get('text'),
                $idThreadMessage
            );
            $reponseofmanager= $this->handleManagerReturnValue($messageToSend);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            // If there is an AskHistory i will post an AskHistory with the message within. If not, i only send a Message.
            if (trim($request->request->get('idAskHistory'))!=="") {
                
                // Get the current AskHistory
                $currentAskHistory = $askHistoryManager->getAskHistory($request->request->get('idAskHistory'));
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
    public function updateAsk(Request $request, AskManager $askManager)
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



    // ADMIN

    /**
     * Retrieve a user.
     *
     * @Route("/user/{id}", name="user", requirements={"id"="\d+"})
     *
     */
    // public function user($id, UserManager $userManager)
    // {
    //     return $this->render('@Mobicoop/user/detail.html.twig', [
    //         'user' => $userManager->getUser($id)
    //     ]);
    // }

    /**
     * Retrieve all users.
     *
     * @Route("/users", name="users")
     *
     */
    // public function users(UserManager $userManager)
    // {
    //     return $this->render('@Mobicoop/user/index.html.twig', [
    //         'hydra' => $userManager->getUsers()
    //     ]);
    // }

    /**
     * Delete a user.
     *
     * @Route("/user/{id}/delete", name="user_delete", requirements={"id"="\d+"})
     *
     */
    // public function userDelete($id, UserManager $userManager)
    // {
    //     if ($userManager->deleteUser($id)) {
    //         return $this->redirectToRoute('users');
    //     } else {
    //         return $this->render('@Mobicoop/user/index.html.twig', [
    //                 'hydra' => $userManager->getUsers(),
    //                 'error' => 'An error occured'
    //         ]);
    //     }
    // }
    
    /**
     * Retrieve all matchings for a proposal.
     *
     * @Route("/user/{id}/proposal/{idProposal}/matchings", name="user_proposal_matchings", requirements={"id"="\d+","idProposal"="\d+"})
     *
     */
    // public function userProposalMatchings($id, $idProposal, ProposalManager $proposalManager)
    // {
    //     $user = new User($id);
    //     $proposal = $proposalManager->getProposal($idProposal);
    //     return $this->render('@Mobicoop/proposal/matchings.html.twig', [
    //         'user' => $user,
    //         'proposal' => $proposal,
    //         'hydra' => $proposalManager->getMatchings($proposal)
    //     ]);
    // }

    /**
     * Delete a proposal of a user.
     *
     * @Route("/user/{id}/proposal/{idProposal}/delete", name="user_proposal_delete", requirements={"id"="\d+","idProposal"="\d+"})
     *
     */
    // public function userProposalDelete($id, $idProposal, ProposalManager $proposalManager)
    // {
    //     if ($proposalManager->deleteProposal($idProposal)) {
    //         return $this->redirectToRoute('user_proposals', ['id'=>$id]);
    //     } else {
    //         $user = new User($id);
    //         return $this->render('@Mobicoop/proposal/index.html.twig', [
    //             'user' => $user,
    //             'hydra' => $proposalManager->getProposals($user),
    //             'error' => 'An error occured'
    //         ]);
    //     }
    // }





    /**
     * Create a proposal for a user.
     */
    // public function userProposalCreate($id=null, ProposalManager $proposalManager, Request $request)
    // {
    //     $proposal = new Proposal();
    //     if ($id) {
    //         $proposal->setUser(new User($id));
    //     } else {
    //         $proposal->setUser(new User());
    //     }

    //     $form = $this->createForm(ProposalForm::class, $proposal);
    //     $form->handleRequest($request);
    //     $error = false;

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         // for now we add the starting end ending points,
    //         // in the future we will need to have dynamic fields
    //         $proposal->addPoint($proposal->getStart());
    //         $proposal->addPoint($proposal->getDestination());
    //         if ($proposal = $proposalManager->createProposal($proposal)) {
    //             return $this->redirectToRoute('user_proposal_matchings', ['id'=>$id,'idProposal'=>$proposal->getId()]);
    //         }
    //         $error = true;
    //     }

    //     return $this->render('@Mobicoop/proposal/create.html.twig', [
    //         'form' => $form->createView(),
    //         'error' => $error
    //     ]);
    // }
}
