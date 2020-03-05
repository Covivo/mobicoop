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

use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
                $address->setPostalCode($data['address']['postalCode']);
                $address->setRegion($data['address']['region']);
                $address->setStreet($data['address']['street']);
                $address->setStreetAddress($data['address']['streetAddress']);
                $address->setSubLocality($data['address']['subLocality']);
                $address->setName($translator->trans('homeAddress', [], 'signup'));
                $address->setHome(true);
                $user->addAddress($address);
            }
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
    public function userSignUpValidation($token, $email, UserManager $userManager, Request $request)
    {
        $error = "";
        if ($request->isMethod('POST')) {
            if ($token !== "" && $email!=="") {
                $user = $userManager->validSignUpByToken($token, $email);
                if (is_null($user)) {
                    $error="updateError";
                } else {
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    return $this->redirectToRoute('carpool_first_ad_post');
                }
            } else {
                $error = "missingArguments";
            }
        }
        return $this->render('@Mobicoop/user/signupValidation.html.twig', [
            'urlToken'=>$token,
            'urlEmail'=>$email,
            'error'=>$error
        ]);
    }

    /**
     * Generate a phone token
     *
     * @param UserManager $userManager
     * @return void
     */
    public function generatePhoneToken(UserManager $userManager)
    {
        $tokenError = [
            'state' => false,
        ];
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);
        $user = $userManager->generatePhoneToken($user) ?  $tokenError['state'] = false : $tokenError['state'] = true ;
            
        return new Response(json_encode($tokenError));
    }

    /**
     * Phone validation
     *
     * @param $token
     * @param UserManager $userManager
     * @param Request $request
     * @return void
     */
    public function userPhoneValidation(UserManager $userManager, Request $request)
    {
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);
        
        $phoneError = [
            'state' => false,
            'message' => "",
        ];
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            // We need to check if the token is right
            // if ($user->getPhoneToken() == $data['token']) {
            //     if ($user->getPhoneValidatedDate()!==null) {
            //         $phoneError["state"] = "true";
            //         $phoneError["message"] = "snackBar.phoneAlreadyVerified";
            //     } else {
            //         $user->setPhoneValidatedDate(new \Datetime()); // TO DO : Correct timezone
            //         $user = $userManager->updateUser($user);
            //         if (!$user) {
            //             $phoneError["state"] = "true";
            //             $phoneError["message"] = "snackBar.phoneUpdate";
            //         }
            //     }
            // } else {
            //     $phoneError["state"] = "true";
            //     $phoneError["message"] = "snackBar.unknown";
            // }

            
            $response = $userManager->validPhoneByToken($data['token'], $data['telephone']);
            return new Response(json_encode($response));
        }
        return new Response(json_encode($phoneError));
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
            
           
            // check if the phone number is new and if so change token and validationdate
            if ($user->getTelephone() != $data->get('telephone')) {
                $user->setTelephone($data->get('telephone'));
                $user->setPhoneValidatedDate(null);
                $user->setPhoneToken(null);
            }
            $user->setEmail($data->get('email'));
            $user->setTelephone($data->get('telephone'));
            $user->setPhoneDisplay($data->get('phoneDisplay'));
            $user->setGivenName($data->get('givenName'));
            $user->setFamilyName($data->get('familyName'));
            $user->setGender((int)($data->get('gender')));
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

        //TODO - fix : Change this when use router vue
        if ($tabDefault == 'mes-annonces') {
            $tabDefault = 'myAds';
        }
        if ($tabDefault == 'mes-covoiturages-acceptes') {
            $tabDefault = 'carpoolsAccepted';
        }
        if ($tabDefault == 'mon-profil') {
            $tabDefault = 'myProfile';
        }

        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
            'error' => $error,
            'alerts' => $userManager->getAlerts($user)['alerts'],
            'tabDefault' => $tabDefault,
            'ads' => $userManager->getAds($user),
            'acceptedCarpools' => $userManager->getAds($user, true)
        ]);
    }

    /**
     * User avatar delete.
     * Ajax
     */
    public function userProfileAvatarDelete(ImageManager $imageManager, UserManager $userManager)
    {
        $user = clone $userManager->getLoggedUser();
        // To DO : Voter for deleting image
        //$this->denyAccessUnlessGranted('update', $user);
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
            $response = $userManager->sendEmailRecoveryPassword($data["email"]);
            return new Response(json_encode($response));
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
            $user = $userManager->userUpdatePasswordReset($token, $data['password']);
            if (!is_null($user)) {
                // after successful update, we re-log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                $userManager->flushUserToken($user);
                return new Response(json_encode($user));
            }
        }

        return new Response();
    }

    /**
     * Update Address of a user
     * Ajax
     */
    public function UserAddressUpdate(AddressManager $addressManager, Request $request, UserManager $userManager, TranslatorInterface $translator)
    {
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('update', $user);
        // To Do : Specific right for update a address ?
        //$this->denyAccessUnlessGranted('address_update_self', $user);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
           
            $homeAddress = new Address();
            
            $homeAddress->setAddressCountry($data['addressCountry']);
            $homeAddress->setAddressLocality($data['addressLocality']);
            $homeAddress->setCountryCode($data['countryCode']);
            $homeAddress->setCounty($data['county']);
            $homeAddress->setLatitude($data['latitude']);
            $homeAddress->setLocalAdmin($data['localAdmin']);
            $homeAddress->setLongitude($data['longitude']);
            $homeAddress->setMacroCounty($data['macroCounty']);
            $homeAddress->setMacroRegion($data['macroRegion']);
            $homeAddress->setPostalCode($data['postalCode']);
            $homeAddress->setRegion($data['region']);
            $homeAddress->setStreet($data['street']);
            $homeAddress->setStreetAddress($data['streetAddress']);
            $homeAddress->setSubLocality($data['subLocality']);
            $homeAddress->setName($translator->trans('homeAddress', [], 'signup'));
            $homeAddress->setHome(true);
            
            if (($data['id']) == null) {
                $user->addAddress($homeAddress);
                $user = $userManager->updateUser($user);
                $addressData = $addressManager->updateAddress($user->getHomeAddress());
            } else {
                $homeAddress->setId($data['id']);
                $addressData = $addressManager->updateAddress($homeAddress);
            }
            $reponseofmanager= $this->handleManagerReturnValue($addressData);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
            return new Response(json_encode($addressData));
        }
    }

    /**
     * Delete the user by anonymise
     *
     * @param UserManager $userManager
     * @return void
     */
    public function deleteUser(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $userManager->deleteUser($user);

        return $this->redirectToRoute('user_logout');
    }


    /*************
     * MESSAGES  *
     *************/

    /**
     * User mailbox
     */
    public function mailBox(UserManager $userManager, Request $request, InternalMessageManager $messageManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $data = $request->request;

        $newThread = null;
        $idThreadDefault = null;
        $idMessage = null;
        $idRecipient = null;
        $idAsk= null;

        if ($request->isMethod('POST')) {
            // if we ask for a specific thread then we return it
            if ($data->has("idMessage")) {
                /** @var Message $message */
                $message = $messageManager->getMessage($data->get("idMessage"));
                $reponseofmanager = $this->handleManagerReturnValue($message);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
                $idMessage = $idThreadDefault = !empty($message->getMessage()) ? $message->getMessage()->getId() : $message->getMessage();
                $idRecipient = $message->getRecipients()[0]->getId();
                $idAsk = $message->getIdAsk();
            } else {
                $newThread = [
                    "carpool" => (int)$request->request->get('carpool'),
                    "idRecipient" => (int)$request->request->get('idRecipient'),
                    "shortFamilyName" => $request->request->get('shortFamilyName'),
                    "givenName" => $request->request->get('givenName'),
                    "avatar" => $request->request->get('avatar')
                ];
                $idThreadDefault = -1; // To preselect the new thread. Id is always -1 because it doesn't really exist yet
            }
        }

        return $this->render('@Mobicoop/user/messages.html.twig', [
            "idUser"=>$user->getId(),
            "idThreadDefault"=>$idThreadDefault,
            "idMessage" => $idMessage,
            "idRecipient" => $idRecipient,
            "idAsk" => $idAsk,
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
        $response = str_replace("\\n", "<br />", json_encode($completeThread));
        return new Response($response);
    }


    /**
     * Get informations for Action Panel of the mailbox
     * AJAX
     */
    public function userMessagesActionsInfos(Request $request, AdManager $adManager, UserManager $userManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $user = $userManager->getLoggedUser();
            if ($data['idAsk']) {
                // Carpool

                // Get the related Ad of this Ask
                $response = $adManager->getAdAsk($data['idAsk'], $user->getId());

                $results = $response->getResults()[0];
                $results["canUpdateAsk"] = $response->getCanUpdateAsk(); // Because it's not in result
                $results["askStatus"] = $response->getAskStatus(); // Because it's not in result

                return new JsonResponse($results);
            } else {
                // Direct
                $recipient = $userManager->getUser($data['idRecipient']);
                $response = [
                    'carpooler' => [
                        'avatars'=>$recipient->getAvatars(),
                        'givenName'=>$recipient->getGivenName(),
                        'shortFamilyName' => $recipient->getShortFamilyName()
                    ]
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
            // -1 : It's a false id for no direct message
            // -99 : It's a false id for no carpool message
            $idThreadMessage = ($data['idThreadMessage']==-1 || $data['idThreadMessage']==-99) ? null : $data['idThreadMessage'];
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
    public function userMessageUpdateAsk(Request $request, AdManager $adManager, UserManager $userManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $user = $userManager->getLoggedUser();
            $idAsk = $data['idAsk'];
            $status = $data['status'];
            $outwardDate = (isset($data['outwardDate'])) ? $data['outwardDate'] : null;
            $outwardLimitDate = (isset($data['outwardLimitDate'])) ? $data['outwardLimitDate'] : null;
            $outwardSchedule = (isset($data['outwardSchedule'])) ? $data['outwardSchedule'] : null;
            $returnSchedule = (isset($data['returnSchedule'])) ? $data['returnSchedule'] : null;

            $schedule = [];
            if (!is_null($outwardSchedule) || !is_null($returnSchedule)) {
               
                // It's a regular journey I need to build the schedule of this journey (structure of an Ad)

                $days = ["mon","tue","wed","thu","fri","sat","sun"];
                foreach ($days as $day) {
                    $currentOutwardTime = (!is_null($outwardSchedule)) ? $outwardSchedule[$day."Time"] : null;
                    $currentReturnTime = (!is_null($returnSchedule)) ? $returnSchedule[$day."Time"] : null;

                    // I need to know if there is already a section of the schedule with these times
                    $alreadyExists = false;
                    if (count($schedule)>0) {
                        foreach ($schedule as $key => $section) {
                            (isset($section['outwardTime']) && !is_null($section['outwardTime'])) ? $sectionOutwardTime = $section['outwardTime'] : $sectionOutwardTime = null;
                            (isset($section['returnTime']) && !is_null($section['returnTime'])) ? $sectionReturnTime = $section['returnTime'] : $sectionReturnTime = null;

                            if ($sectionOutwardTime===$currentOutwardTime && $sectionReturnTime===$currentReturnTime) {
                                $alreadyExists = true;
                                // It's exists so i update the current day on this section
                                $schedule[$key][$day] = 1;
                            }
                        }
                    }
                    
                    // It's a new section i need tu push it with the good day at 1
                    if (!$alreadyExists && (!is_null($currentOutwardTime) || !is_null($currentReturnTime))) {
                        $schedule[] = [
                            "outwardTime" => $currentOutwardTime,
                            "returnTime" => $currentReturnTime,
                            "mon" => ($day=="mon") ? 1 : 0,
                            "tue" => ($day=="tue") ? 1 : 0,
                            "wed" => ($day=="wed") ? 1 : 0,
                            "thu" => ($day=="thu") ? 1 : 0,
                            "fri" => ($day=="fri") ? 1 : 0,
                            "sat" => ($day=="sat") ? 1 : 0,
                            "sun" => ($day=="sun") ? 1 : 0
                        ];
                    }
                }
            }
           
            // I build the Ad for the put
            $adToPost = new Ad($idAsk);
            $adToPost->setAskStatus($status);
            if (!is_null($outwardDate)) {
                $adToPost->setOutwardDate(new \DateTime($outwardDate));
            }
            if (!is_null($outwardLimitDate)) {
                $adToPost->setOutwardLimitDate(new \DateTime($outwardLimitDate));
            }
            if (count($schedule)>0) {
                $adToPost->setSchedule($schedule);
            } // Only regular

            $return = $adManager->updateAdAsk($adToPost, $user->getId());

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
            // We get de communities in session. If it exists we don't need to make the api call
            $session = $this->get('session');
            $userCommunitiesInSession = $session->get(Community::SESSION_VAR_NAME);
            $communities = [];
            if (!is_null($userCommunitiesInSession)) {
                return new JsonResponse($userCommunitiesInSession);
            } else {
                if ($communityUsers = $communityManager->getAllCommunityUser($data['userId'])) {
                    foreach ($communityUsers as $communityUser) {
                        $communities[] = $communityUser->getCommunity();
                    }
                    // We store de communities in session
                    $session->set(Community::SESSION_VAR_NAME, $communities);
                }
            }
            return new JsonResponse($communities);
        }
        return new JsonResponse(['error'=>'errorUpdateAlert']);
    }

    /**
     * Check if an email is already registered by a user
     * AJAX
     */
    public function userCheckEmailExists(Request $request, UserManager $userManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['email']) && $data['email']!=="") {
                $user = $userManager->findByEmail($data['email']);
                if (!is_null($user)) {
                    return new JsonResponse(['error'=>false, 'message'=>$user->getId()]);
                } else {
                    return new JsonResponse(['error'=>false, 'message'=>'']);
                }
            } else {
                return new JsonResponse(['error'=>true, 'message'=>'empty email']);
            }
        }
        return new JsonResponse(['error'=>true, 'message'=>'Only POST is allowed']);
    }
    
    /**
    * Unsubscribe email for a user
    */
    public function userUnsubscribeFromEmail(UserManager $userManager, string $token)
    {
        $user = $userManager->unsubscribeUserFromEmail($token);
        if ($user != null) {
            return $this->render(
                '@Mobicoop/default/index.html.twig',
                [
                    'baseUri' => $_ENV['API_URI'],
                    'metaDescription' => 'Mobicoop',
                    'unsubscribe' => json_encode($user->getUnsubscribeMessage())
                ]
            );
        }
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $_ENV['API_URI'],
                'metaDescription' => 'Mobicoop',
            ]
        );
    }
}
