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

namespace Mobicoop\Bundle\MobicoopBundle\User\Controller;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Report;
use Mobicoop\Bundle\MobicoopBundle\Communication\Service\InternalMessageManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Event\Service\EventManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Geography\Service\AddressManager;
use Mobicoop\Bundle\MobicoopBundle\I18n\Entity\Language;
use Mobicoop\Bundle\MobicoopBundle\I18n\Service\LanguageManager;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Image\Service\ImageManager;
use Mobicoop\Bundle\MobicoopBundle\Incentive\Service\CeeSubscriptionManager;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\ValidationDocument;
use Mobicoop\Bundle\MobicoopBundle\Payment\Service\PaymentManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\ReviewManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\SsoManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserController extends AbstractController
{
    use HydraControllerTrait;

    private const ALLOWED_TAB_TYPE = ['carpool', 'direct', 'solidary'];

    private $encoder;
    private $facebook_show;
    private $facebook_appid;
    private $required_home_address;
    private $news_subscription;
    private $communityShow;
    private $userProvider;
    private $signUpLinkInConnection;
    private $signupRgpdInfos;
    private $loginLinkInConnection;
    private $solidaryDisplay;
    private $paymentElectronicActive;
    private $userManager;
    private $paymentManager;
    private $languageManager;
    private $validationDocsAuthorizedExtensions;
    private $ssoManager;
    private $required_community;
    private $loginDelegate;
    private $fraudWarningDisplay;
    private $ageDisplay;
    private $birthDateDisplay;
    private $eventManager;
    private $ceeSubscriptionManager;
    private $carpoolSettingsDisplay;
    private $signInSsoOriented;
    private $ceeDisplay;

    /**
     * Constructor.
     *
     * @param mixed $facebook_show
     * @param mixed $facebook_appid
     * @param mixed $required_home_address
     * @param mixed $community_show
     * @param mixed $signUpLinkInConnection
     * @param mixed $signupRgpdInfos
     * @param mixed $loginLinkInConnection
     * @param mixed $solidaryDisplay
     * @param mixed $required_community
     */
    public function __construct(
        UserPasswordEncoderInterface $encoder,
        $facebook_show,
        $facebook_appid,
        $required_home_address,
        bool $news_subscription,
        $community_show,
        UserProvider $userProvider,
        $signUpLinkInConnection,
        $signupRgpdInfos,
        $loginLinkInConnection,
        $solidaryDisplay,
        bool $paymentElectronicActive,
        string $validationDocsAuthorizedExtensions,
        UserManager $userManager,
        SsoManager $ssoManager,
        PaymentManager $paymentManager,
        LanguageManager $languageManager,
        EventManager $eventManager,
        CeeSubscriptionManager $ceeSubscriptionManager,
        $required_community,
        bool $loginDelegate,
        bool $fraudWarningDisplay,
        bool $ageDisplay,
        bool $birthDateDisplay,
        bool $carpoolSettingsDisplay,
        bool $signInSsoOriented,
        bool $ceeDisplay
    ) {
        $this->encoder = $encoder;
        $this->facebook_show = $facebook_show;
        $this->facebook_appid = $facebook_appid;
        $this->required_home_address = $required_home_address;
        $this->news_subscription = $news_subscription;
        $this->community_show = $community_show;
        $this->userProvider = $userProvider;
        $this->signUpLinkInConnection = $signUpLinkInConnection;
        $this->signupRgpdInfos = $signupRgpdInfos;
        $this->loginLinkInConnection = $loginLinkInConnection;
        $this->solidaryDisplay = $solidaryDisplay;
        $this->paymentElectronicActive = $paymentElectronicActive;
        $this->userManager = $userManager;
        $this->paymentManager = $paymentManager;
        $this->languageManager = $languageManager;
        $this->validationDocsAuthorizedExtensions = $validationDocsAuthorizedExtensions;
        $this->required_community = $required_community;
        $this->loginDelegate = $loginDelegate;
        $this->fraudWarningDisplay = $fraudWarningDisplay;
        $this->ageDisplay = $ageDisplay;
        $this->carpoolSettingsDisplay = $carpoolSettingsDisplay;
        $this->birthDateDisplay = $birthDateDisplay;
        $this->eventManager = $eventManager;
        $this->ceeSubscriptionManager = $ceeSubscriptionManager;
        $this->ssoManager = $ssoManager;
        $this->signInSsoOriented = $signInSsoOriented;
        $this->ceeDisplay = $ceeDisplay;
    }

    private function __parsePostParams(string $response): array
    {
        $parsed_reponse = [];

        $array_response = explode('&', $response);

        foreach ($array_response as $value) {
            $parsed_item = explode('=', $value);
            $parsed_reponse[$parsed_item[0]] = $parsed_item[1];
        }

        return $parsed_reponse;
    }

    // PROFILE

    /**
     * User login.
     */
    public function login(Request $request, ?int $id = null, ?int $eventId = null, ?int $communityId = null, ?string $redirect = null)
    {
        $errorMessage = '';
        if (in_array('bad-credentials-api', $request->getSession()->getFlashBag()->peek('notice'))) {
            $errorMessage = 'Bad credentials.';
            $request->getSession()->getFlashBag()->clear();
        }
        // if ($eventId !== null && $destination == null && $event==null) {
        //     $event = $this->eventManager->getEvent($eventId);
        //     $destination = json_encode($event->getAddress());
        // }

        // initializations for redirections
        $type = 'default';
        if (!is_null($id)) {
            $type = 'proposal';
        }
        if (!is_null($eventId)) {
            $type = 'event';
            $id = $eventId;
        }
        if (!is_null($communityId)) {
            $type = 'community';
            $id = $communityId;
        }
        if (!is_null($redirect)) {
            $type = $redirect;
            $id = 1; // default id
        }

        $template = $this->signInSsoOriented ? 'login-sso-oriented.html.twig' : 'login.html.twig';

        return $this->render('@Mobicoop/user/'.$template, [
            'id' => $id,
            'type' => $type,
            'errorMessage' => $errorMessage,
            'facebook_show' => ('true' === $this->facebook_show) ? true : false,
            'facebook_appid' => $this->facebook_appid,
            'signUpLinkInConnection' => $this->signUpLinkInConnection,
        ]);
    }

    public function loginSsoError(Request $request)
    {
        return $this->render('@Mobicoop/user/login.html.twig', [
            'id' => null,
            'type' => 'default',
            'errorMessage' => 'errorSso',
            'service' => $request->get('service'),
            'facebook_show' => ('true' === $this->facebook_show) ? true : false,
            'facebook_appid' => $this->facebook_appid,
            'signUpLinkInConnection' => $this->signUpLinkInConnection,
        ]);
    }

    /**
     * User registration.
     */
    public function userSignUp(UserManager $userManager, Request $request, TranslatorInterface $translator, ?int $id = null, ?int $eventId = null, ?int $communityId = null, ?string $redirect = null)
    {
        $this->denyAccessUnlessGranted('register');

        $user = new User();
        $address = new Address();

        $error = false;

        // Default status of the checkbox (.env)
        $newsSubscription = $this->news_subscription;

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            // add home address to user if it exists
            if (isset($data['address'])) {
                $address->setAddressCountry(isset($data['address']['addressCountry']) ? $data['address']['addressCountry'] : null);
                $address->setAddressLocality(isset($data['address']['addressLocality']) ? $data['address']['addressLocality'] : null);
                $address->setCountryCode(isset($data['address']['countryCode']) ? $data['address']['countryCode'] : null);
                $address->setCounty(isset($data['address']['county']) ? $data['address']['county'] : null);
                $address->setLatitude(isset($data['address']['latitude']) ? $data['address']['latitude'] : null);
                $address->setLocalAdmin(isset($data['address']['localAdmin']) ? $data['address']['localAdmin'] : null);
                $address->setLongitude(isset($data['address']['longitude']) ? $data['address']['longitude'] : null);
                $address->setMacroCounty(isset($data['address']['macroCounty']) ? $data['address']['macroCounty'] : null);
                $address->setMacroRegion(isset($data['address']['macroRegion']) ? $data['address']['macroRegion'] : null);
                $address->setPostalCode(isset($data['address']['postalCode']) ? $data['address']['postalCode'] : null);
                $address->setRegion(isset($data['address']['region']) ? $data['address']['region'] : null);
                $address->setStreet(isset($data['address']['street']) ? $data['address']['street'] : null);
                $address->setStreetAddress(isset($data['address']['streetAddress']) ? $data['address']['streetAddress'] : null);
                $address->setSubLocality(isset($data['address']['subLocality']) ? $data['address']['subLocality'] : null);
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
            // $user->setBirthYear($data->get('birthYear')); Replace only year by full birthday
            $user->setBirthDate(new \DateTime($data['birthDay']));

            if (isset($data['newsSubscription'])) {
                $newsSubscription = $data['newsSubscription'];
            }

            $user->setNewsSubscription($newsSubscription);

            // set phone display by default
            $user->setPhoneDisplay(1);

            if (!is_null($data['idFacebook'])) {
                $user->setFacebookId($data['idFacebook']);
            }

            // join a community
            if (!is_null($data['community'])) {
                $user->setCommunityId($data['community']);
            }

            // create user in database
            $data = $userManager->createUser($user);
            $reponseofmanager = $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }

        // initializations for redirections
        $type = 'default';
        if (!is_null($id)) {
            $type = 'proposal';
        }
        if (!is_null($eventId)) {
            $type = 'event';
            $id = $eventId;
        }
        if (!is_null($communityId)) {
            $type = 'community';
            $id = $communityId;
        }
        if (!is_null($redirect)) {
            $type = $redirect;
        }

        return $this->render('@Mobicoop/user/signup.html.twig', [
            'id' => $id,
            'type' => $type,
            'error' => $error,
            'facebook_show' => ('true' === $this->facebook_show) ? true : false,
            'facebook_appid' => $this->facebook_appid,
            'required_home_address' => ('true' === $this->required_home_address) ? true : false,
            'community_show' => ('true' === $this->community_show) ? true : false,
            'loginLinkInConnection' => $this->loginLinkInConnection,
            'signup_rgpd_infos' => $this->signupRgpdInfos,
            'required_community' => ('true' === $this->required_community) ? true : false,
            'newsSubscription' => $newsSubscription,
            'birthDateDisplay' => $this->birthDateDisplay,
            'communityId' => $communityId,
        ]);
    }

    // /**
    //  * User registration email validation
    //  */
    public function userSignUpValidation($token, $email, Request $request)
    {
        $errorMessage = '';
        if (in_array('bad-credentials-api', $request->getSession()->getFlashBag()->peek('notice'))) {
            $errorMessage = 'Bad credentials.';
            $request->getSession()->getFlashBag()->clear();
        }

        return $this->render('@Mobicoop/user/signupValidation.html.twig', [
            'urlToken' => $token,
            'urlEmail' => $email,
            'error' => $errorMessage,
        ]);
    }

    // /**
    //  * User registration email validation
    //  */
    public function userEmailValidation($token, $email, Request $request)
    {
        $errorMessage = '';
        if (in_array('bad-credentials-api', $request->getSession()->getFlashBag()->peek('notice'))) {
            $errorMessage = 'Bad credentials.';
            $request->getSession()->getFlashBag()->clear();
        }

        return $this->render('@Mobicoop/user/signupValidation.html.twig', [
            'emailValidation' => true,
            'urlToken' => $token,
            'urlEmail' => $email,
            'error' => $errorMessage,
        ]);
    }

    /**
     * User registration email validation check -> we get here if there is an error with $credentials
     * We redirect on  user_sign_up_validation, in message flash there is error.
     */
    public function userSignUpValidationCheck(Request $request)
    {
        return $this->redirectToRoute('user_email_form_validation', [
            'token' => $request->get('emailToken'),
            'email' => $request->get('email'),
        ]);
    }

    /**
     * User registration email validation check -> we get here if there is an error with $credentials
     * We redirect on  user_sign_up_validation, in message flash there is error.
     */
    public function userEmailValidationCheck(Request $request)
    {
        return $this->redirectToRoute('user_sign_up_validation', [
            'token' => $request->get('emailToken'),
            'email' => $request->get('email'),
        ]);
    }

    /**
     * Generate a phone token.
     */
    public function generatePhoneToken(UserManager $userManager)
    {
        $tokenError = [
            'state' => false,
        ];
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);
        $user = $userManager->generatePhoneToken($user) ? $tokenError['state'] = false : $tokenError['state'] = true;

        return new Response(json_encode($tokenError));
    }

    /**
     * Phone validation.
     *
     * @param $token
     */
    public function userPhoneValidation(UserManager $userManager, Request $request)
    {
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);

        $phoneError = [
            'state' => false,
            'message' => '',
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
     * Send a validation email.
     *
     * @return User
     */
    public function sendValidationEmail()
    {
        $user = $this->userManager->getLoggedUser();

        // Redirect to user_login
        if (!$user instanceof User) {
            return null;
        }

        $this->userManager->sendValidationEmail($user);

        return new response(json_encode('emailSend'));
    }

    /**
     * User profile update.
     *
     * @param mixed $tabDefault
     * @param mixed $selectedTab
     */
    public function userProfileUpdate(UserManager $userManager, Request $request, ImageManager $imageManager, AddressManager $addressManager, TranslatorInterface $translator, $tabDefault, $selectedTab)
    {
        $user = $userManager->getLoggedUser();

        // # Redirect to user_login
        if (!$user instanceof User) {
            return $this->redirectToRoute('user_login');
        }

        $this->denyAccessUnlessGranted('update', $userManager->getLoggedUser());
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }

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
            $user->setGender((int) $data->get('gender'));
            $user->setBirthDate(new \DateTime($data->get('birthDay')));
            // cause we use FormData to post data
            $user->setNewsSubscription('true' === $data->get('newsSubscription') ? true : false);
            $user->setDrivingLicenceNumber('' !== trim($data->get('drivingLicenceNumber')) ? $data->get('drivingLicenceNumber') : null);

            if ($user = $userManager->updateUser($user)) {
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));

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

        // TODO - fix : Change this when use router vue
        if ('mes-annonces' == $tabDefault) {
            $tabDefault = 'myAds';
        }
        if ('mes-covoiturages-acceptes' == $tabDefault) {
            $tabDefault = 'carpoolsAccepted';
        }
        if ('mon-profil' == $tabDefault) {
            $tabDefault = 'myProfile';
        }
        if ('avis' == $tabDefault) {
            $tabDefault = 'reviews';
        }

        $tab = 'myAccount';
        if ('mes-badges' == $selectedTab) {
            $tab = 'myBadges';
        }

        if ('alertes' == $selectedTab) {
            $tab = 'alerts';
        }

        if ('identite-bancaire' == $selectedTab) {
            $tab = 'bankCoordinates';
        }

        if ('avis-recus' == $selectedTab) {
            $tab = 'receivedReviews';
        }

        $isAfterEECSubscription = !is_null($request->get('afterEECSubscription')) && '1' === $request->get('afterEECSubscription');

        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
            'ageDisplay' => $this->ageDisplay,
            'alerts' => $userManager->getAlerts($user)['alerts'],
            'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
            'ceeDisplay' => $this->ceeDisplay,
            'error' => $error,
            'isAfterEECSubscription' => $isAfterEECSubscription,
            'paymentElectronicActive' => $this->paymentElectronicActive,
            'selectedTab' => $tab,
            'showReviews' => $user->isUserReviewsActive(),
            'tabDefault' => $tabDefault,
            'validationDocsAuthorizedExtensions' => $this->validationDocsAuthorizedExtensions,
        ]);
    }

    /**
     * Get user ads
     * AJAX get.
     */
    public function userAds(Request $request)
    {
        $user = $this->userManager->getLoggedUser();

        // Redirect to user_login
        if (!$user instanceof User) {
            return null;
        }

        return new JsonResponse($this->userManager->getMyAds());
    }

    /**
     * Export list carpools for a user
     * AJAX post.
     */
    public function getExport(Request $request)
    {
        $user = $this->userManager->getLoggedUser();
        // Redirect to user_login
        if (!$user instanceof User) {
            return $this->redirectToRoute('user_login');
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $params = [
                'fromDate' => $data['fromDate'],
                'toDate' => $data['toDate'],
            ];
            $user = $this->userManager->getCarpoolExport($user, $params);

            return new JsonResponse($user->getCarpoolExport());
        }

        return null;
    }

    /**
     * User avatar delete.
     * Ajax.
     */
    public function userProfileAvatarDelete(ImageManager $imageManager, UserManager $userManager)
    {
        $user = clone $userManager->getLoggedUser();
        // To DO : Voter for deleting image
        // $this->denyAccessUnlessGranted('update', $user);
        $imageId = $user->getImages()[0]->getId();
        $imageManager->deleteImage($imageId);

        return new JsonResponse($userManager->getUser($user->getId())->getAvatars());
    }

    /**
     * User password update.
     * Ajax.
     */
    public function userPasswordUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('password', $user);

        $error = [
            'state' => false,
            'message' => '',
        ];

        if ($request->isMethod('POST')) {
            $error['message'] = 'Ok';

            if (null !== $request->request->get('password')) {
                $user->setPassword($request->request->get('password'));
            } else {
                $error['state'] = 'true';
                $error['message'] = 'Empty password';

                return new Response(json_encode($error));
            }

            if ($user = $userManager->updateUserPassword($user)) {
                $reponseofmanager = $this->handleManagerReturnValue($user);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
                // after successful update, we re-log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                $error['message'] = 'Ok';

                return new Response(json_encode($error));
            }
            $error['state'] = 'true';
            $error['message'] = 'Update password failed';

            return new Response(json_encode($error));
        }

        $error['state'] = 'true';
        $error['message'] = 'Request failed';

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
     * Get the password of a user if it exists.
     *
     * @param UserManager $userManager the class managing the user
     * @param Request     $request     the symfony request object
     */
    public function userPasswordForRecovery(UserManager $userManager, Request $request)
    {
        $this->denyAccessUnlessGranted('login');
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $response = $userManager->sendEmailRecoveryPassword($data['email']);

            return new Response(json_encode($response));
        }
    }

    /**
     * Reset password.
     */
    public function userPasswordReset(UserManager $userManager, string $token)
    {
        $pwdToken = $userManager->checkPasswordToken($token);

        if (empty($pwdToken)) {
            return $this->redirectToRoute('user_password_forgot');
        }

        return $this->render(
            '@Mobicoop/user/passwordRecoveryUpdate.html.twig',
            [
                'pwdToken' => $token,
            ]
        );
    }

    /**
     * Update the new Password after recovery.
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
     * Ajax.
     */
    public function UserAddressUpdate(AddressManager $addressManager, Request $request, UserManager $userManager, TranslatorInterface $translator)
    {
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('update', $user);
        // To Do : Specific right for update a address ?
        // $this->denyAccessUnlessGranted('address_update_self', $user);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $homeAddress = new Address();

            $homeAddress->setAddressCountry(isset($data['addressCountry']) ? $data['addressCountry'] : null);
            $homeAddress->setAddressLocality(isset($data['addressLocality']) ? $data['addressLocality'] : null);
            $homeAddress->setCountryCode(isset($data['countryCode']) ? $data['countryCode'] : null);
            $homeAddress->setCounty(isset($data['county']) ? $data['county'] : null);
            $homeAddress->setLatitude(isset($data['latitude']) ? $data['latitude'] : null);
            $homeAddress->setLocalAdmin(isset($data['localAdmin']) ? $data['localAdmin'] : null);
            $homeAddress->setLongitude(isset($data['longitude']) ? $data['longitude'] : null);
            $homeAddress->setMacroCounty(isset($data['macroCounty']) ? $data['macroCounty'] : null);
            $homeAddress->setMacroRegion(isset($data['macroRegion']) ? $data['macroRegion'] : null);
            $homeAddress->setPostalCode(isset($data['postalCode']) ? $data['postalCode'] : null);
            $homeAddress->setRegion(isset($data['region']) ? $data['region'] : null);
            $homeAddress->setStreet(isset($data['street']) ? $data['street'] : null);
            $homeAddress->setStreetAddress(isset($data['streetAddress']) ? $data['streetAddress'] : null);
            $homeAddress->setSubLocality(isset($data['subLocality']) ? $data['subLocality'] : null);
            $homeAddress->setName($translator->trans('homeAddress', [], 'signup'));
            $homeAddress->setHome(true);

            if (null == $data['id']) {
                $user->addAddress($homeAddress);
                $user = $userManager->updateUser($user);
                $addressData = $addressManager->updateAddress($user->getHomeAddress());
            } else {
                $homeAddress->setId($data['id']);
                $addressData = $addressManager->updateAddress($homeAddress);
            }
            $reponseofmanager = $this->handleManagerReturnValue($addressData);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            return new Response(json_encode($addressData));
        }
    }

    /**
     * Delete the user by anonymise.
     */
    public function deleteUser(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $userManager->deleteUser($user);

        return $this->redirectToRoute('user_logout');
    }

    // MESSAGES

    /**
     * User mailbox.
     *
     * @param null|mixed $askId
     */
    public function mailBox($askId = null, UserManager $userManager, Request $request, InternalMessageManager $messageManager, int $msgId = null)
    {
        $user = $userManager->getLoggedUser();

        // # Redirect to user_login
        if (!$user instanceof User) {
            return $this->redirectToRoute('user_login');
        }

        $this->denyAccessUnlessGranted('messages', $user);
        $data = $request->request;
        $newThread = null;
        $idThreadDefault = null;
        $idMessage = $msgId ? $msgId : null;
        $defaultThreadTab = null;
        $idRecipient = null;
        $idAsk = $askId ? $askId : null;

        if ($request->isMethod('POST')) {
            // if we ask for a specific thread then we return it
            if ($data->has('idAsk')) {
                $idAsk = $data->get('idAsk');
            } elseif ($data->has('idMessage')) {
                /** @var Message $message */
                $message = $messageManager->getMessage($data->get('idMessage'));
                $reponseofmanager = $this->handleManagerReturnValue($message);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }
                $idMessage = $idThreadDefault = !empty($message->getMessage()) ? $message->getMessage()->getId() : $message->getMessage();
                $idRecipient = $message->getRecipients()[0]->getId();
                $idAsk = $message->getIdAsk();
            } else {
                $newThread = [
                    'carpool' => (int) $request->request->get('carpool'),
                    'idRecipient' => (int) $request->request->get('idRecipient'),
                    'shortFamilyName' => $request->request->get('shortFamilyName'),
                    'givenName' => $request->request->get('givenName'),
                    'avatar' => $request->request->get('avatar'),
                    'origin' => $request->request->get('origin'),
                    'destination' => $request->request->get('destination'),
                    'askHistoryId' => $request->request->get('askHistoryId'),
                    'frequency' => (int) $request->request->get('frequency'),
                    'fromDate' => $request->request->get('fromDate'),
                    'fromTime' => $request->request->get('fromTime'),
                    'monCheck' => (bool) $request->request->get('monCheck'),
                    'tueCheck' => (bool) $request->request->get('tueCheck'),
                    'wedCheck' => (bool) $request->request->get('wedCheck'),
                    'thuCheck' => (bool) $request->request->get('thuCheck'),
                    'friCheck' => (bool) $request->request->get('friCheck'),
                    'satCheck' => (bool) $request->request->get('satCheck'),
                    'sunCheck' => (bool) $request->request->get('sunCheck'),
                    'adId' => (int) $request->request->get('adIdResult'),
                    'matchingId' => (int) $request->request->get('matchingId'),
                    'proposalId' => (int) $request->request->get('proposalId'),
                    'date' => $request->request->get('date'),
                    'time' => $request->request->get('time'),
                    'driver' => (bool) $request->request->get('driver'),
                    'passenger' => (bool) $request->request->get('passenger'),
                    'regular' => (bool) $request->request->get('regular'),
                ];
                $idThreadDefault = -1; // To preselect the new thread. Id is always -1 because it doesn't really exist yet
            }
        }

        if ($request->isMethod('GET') && !is_null($request->get('type')) && in_array($request->get('type'), self::ALLOWED_TAB_TYPE)) {
            $defaultThreadTab = $request->get('type');
        }

        return $this->render('@Mobicoop/user/messages.html.twig', [
            'idUser' => $user->getId(),
            'emailUser' => $user->getEmail(),
            'unreadCarpoolMessages' => $user->getUnreadCarpoolMessageNumber(),
            'unreadDirectMessages' => $user->getUnreadDirectMessageNumber(),
            'unreadSolidaryMessages' => $user->getUnreadSolidaryMessageNumber(),
            'idThreadDefault' => $idThreadDefault,
            'idMessage' => $idMessage,
            'idRecipient' => $idRecipient,
            'idAsk' => $idAsk,
            'newThread' => $newThread,
            'solidaryDisplay' => $this->solidaryDisplay,
            'fraudWarningDisplay' => $this->fraudWarningDisplay,
            'defaultThreadTab' => $defaultThreadTab,
        ]);
    }

    /**
     * Get direct messages threads.
     */
    public function userMessageDirectThreadsList(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $threads = $userManager->getThreadsDirectMessages($user);

        return new Response(json_encode($threads));
    }

    /**
     * Get carpool messages threads.
     */
    public function userMessageCarpoolThreadsList(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $threads = $userManager->getThreadsCarpoolMessages($user);

        return new Response(json_encode($threads));
    }

    /**
     * Get solidary related messages threads.
     */
    public function userMessageSolidaryThreadsList(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $threads = $userManager->getThreadsSolidaryMessages($user);

        return new Response(json_encode($threads));
    }

    /**
     * Display user mailbox with a preselected thread.
     */
    public function userMessageThread(int $idFirstMessage, UserManager $userManager, Request $request, InternalMessageManager $messageManager)
    {
        return $this->mailBox(null, $userManager, $request, $messageManager, $idFirstMessage);
    }

    /**
     * Get direct messages threads.
     *
     * @param mixed $idMessage
     */
    public function userMessageCompleteThread($idMessage, InternalMessageManager $internalMessageManager, UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);
        $completeThread = $internalMessageManager->getThread($idMessage, DataProvider::RETURN_JSON);
        $response = str_replace('\\n', '<br />', json_encode($completeThread));

        return new Response($response);
    }

    /**
     * Get informations for Action Panel of the mailbox
     * AJAX.
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
                $results['canUpdateAsk'] = $response->getCanUpdateAsk(); // Because it's not in result
                $results['askStatus'] = $response->getAskStatus(); // Because it's not in result

                return new JsonResponse($results);
            }
        }

        return new JsonResponse();
    }

    public function userMessageSend(UserManager $userManager, InternalMessageManager $internalMessageManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('messages', $user);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            // -1 : It's a false id for no direct message
            // -99 : It's a false id for no carpool message
            $idThreadMessage = (-1 == $data['idThreadMessage'] || -99 == $data['idThreadMessage']) ? null : $data['idThreadMessage'];
            $idAsk = (isset($data['idAsk']) && !is_null($data['idAsk'])) ? $data['idAsk'] : null;
            $idAdToRespond = (isset($data['adIdToRespond']) && !is_null($data['adIdToRespond'])) ? $data['adIdToRespond'] : null;
            $idMatching = (isset($data['matchingId']) && !is_null($data['matchingId'])) ? $data['matchingId'] : null;
            $idProposal = (isset($data['proposalId']) && !is_null($data['proposalId'])) ? $data['proposalId'] : null;
            $text = $data['text'];
            $idRecipient = $data['idRecipient'];

            $messageToSend = $internalMessageManager->createInternalMessage(
                $user,
                $idRecipient,
                '',
                $text,
                $idThreadMessage
            );

            if (null !== $idAsk) {
                $messageToSend->setIdAsk($idAsk);
            }

            if (null !== $idAdToRespond && null !== $idMatching && null !== $idProposal) {
                $messageToSend->setIdAdToRespond($idAdToRespond);
                $messageToSend->setIdProposal($idProposal);
                $messageToSend->setIdMatching($idMatching);
            }

            return new Response($internalMessageManager->sendInternalMessage($messageToSend, DataProvider::RETURN_JSON));
        }

        return new Response(json_encode('Not a post'));
    }

    /**
     * Update and ask
     * Ajax Request.
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

                $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                foreach ($days as $day) {
                    $currentOutwardTime = (!is_null($outwardSchedule)) ? $outwardSchedule[$day.'Time'] : null;
                    $currentReturnTime = (!is_null($returnSchedule)) ? $returnSchedule[$day.'Time'] : null;

                    // I need to know if there is already a section of the schedule with these times
                    $alreadyExists = false;
                    if (count($schedule) > 0) {
                        foreach ($schedule as $key => $section) {
                            (isset($section['outwardTime']) && !is_null($section['outwardTime'])) ? $sectionOutwardTime = $section['outwardTime'] : $sectionOutwardTime = null;
                            (isset($section['returnTime']) && !is_null($section['returnTime'])) ? $sectionReturnTime = $section['returnTime'] : $sectionReturnTime = null;

                            if ($sectionOutwardTime === $currentOutwardTime && $sectionReturnTime === $currentReturnTime) {
                                $alreadyExists = true;
                                // It's exists so i update the current day on this section
                                $schedule[$key][$day] = 1;
                            }
                        }
                    }

                    // It's a new section i need tu push it with the good day at 1
                    if (!$alreadyExists && (!is_null($currentOutwardTime) || !is_null($currentReturnTime))) {
                        $schedule[] = [
                            'outwardTime' => $currentOutwardTime,
                            'returnTime' => $currentReturnTime,
                            'mon' => ('mon' == $day) ? 1 : 0,
                            'tue' => ('tue' == $day) ? 1 : 0,
                            'wed' => ('wed' == $day) ? 1 : 0,
                            'thu' => ('thu' == $day) ? 1 : 0,
                            'fri' => ('fri' == $day) ? 1 : 0,
                            'sat' => ('sat' == $day) ? 1 : 0,
                            'sun' => ('sun' == $day) ? 1 : 0,
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
            if (count($schedule) > 0) {
                $adToPost->setSchedule($schedule);
            } // Only regular

            $return = $adManager->updateAdAsk($adToPost, $user->getId());

            return new JsonResponse($return);
        }

        return new JsonResponse('Not a post');
    }

    /**
     * Connect a user by his facebook credentials
     * AJAX.
     */
    public function userFacebookConnect(UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            // We get the user by his email
            if (!empty($data['email'])) {
                $user = $userManager->findByEmail($data['email']);
                if ($user && $user->getFacebookId() === $data['personalID']) {
                    // Same Facebook ID in BDD that the one from the front component. We log the user.
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));

                    return new JsonResponse($user->getFacebookId());
                }

                return new JsonResponse(['error' => 'userFBNotFound']);
            }

            return new JsonResponse(['error' => 'userFBNotFound']);
        }

        return new JsonResponse(['error' => 'errorCredentialsFacebook']);
    }

    /**
     * Update a user alert
     * AJAX.
     */
    public function updateAlert(UserManager $userManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = $userManager->getLoggedUser();
            $data = json_decode($request->getContent(), true);

            $responseUpdate = $userManager->updateAlert($user, $data['id'], $data['active']);

            return new JsonResponse($responseUpdate);
        }

        return new JsonResponse(['error' => 'errorUpdateAlert']);
    }

    /**
     * User carpool settings update.
     * Ajax.
     *
     * @return JsonResponse
     */
    public function userCarpoolSettingsUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $reponseofmanager = $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('update', $user);

        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true);

            $user->setSmoke($data['smoke']);
            $user->setMusic($data['music']);
            $user->setMusicFavorites($data['musicFavorites']);
            $user->setChat($data['chat']);
            $user->setChatFavorites($data['chatFavorites']);

            if ($response = $userManager->updateUser($user)) {
                $reponseofmanager = $this->handleManagerReturnValue($response);
                if (!empty($reponseofmanager)) {
                    return $reponseofmanager;
                }

                return new JsonResponse(
                    ['message' => 'success'],
                    Response::HTTP_ACCEPTED
                );
            }

            return new JsonResponse(
                ['message' => 'error'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(
            ['message' => 'error'],
            Response::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * Get all communities of a user
     * AJAX.
     */
    public function userCommunities(Request $request, CommunityManager $communityManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $communities = $communityManager->getAllCommunityUser($data['userId']);

            return new JsonResponse($communities);
        }

        return new JsonResponse(['error' => 'errorUpdateAlert']);
    }

    /**
     * Check if an email is already registered by a user
     * AJAX.
     */
    public function userCheckEmailExists(Request $request, UserManager $userManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['email']) && '' !== $data['email']) {
                return new JsonResponse($userManager->checkEmail($data['email']));
            }

            return new JsonResponse(['error' => true, 'message' => 'empty email']);
        }

        return new JsonResponse(['error' => true, 'message' => 'Only POST is allowed']);
    }

    /**
     * Check if an email is already registered by a user
     * AJAX.
     */
    public function userPhoneNumberValidity(Request $request, UserManager $userManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['telephone']) && '' !== $data['telephone']) {
                return new JsonResponse($userManager->checkPhoneNumberValidity($data['telephone']));
            }

            return new JsonResponse(['error' => true, 'message' => 'Empty phone']);
        }

        return new JsonResponse(['error' => true, 'message' => 'Only POST is allowed']);
    }

    /**
     * Unsubscribe email for a user.
     */
    public function userUnsubscribeFromEmail(UserManager $userManager, string $token)
    {
        $user = $userManager->unsubscribeUserFromEmail($token);
        if (null != $user) {
            return $this->render(
                '@Mobicoop/default/index.html.twig',
                [
                    'baseUri' => $_ENV['API_URI'],
                    'metaDescription' => 'Mobicoop',
                    'unsubscribe' => json_encode($user->getUnsubscribeMessage()),
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

    /**
     * Get all communities owned by a user
     * AJAX.
     */
    public function userOwnedCommunities(Request $request, CommunityManager $communityManager)
    {
        $userOwnedCommunities = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($userOwnedCommunities = $communityManager->getOwnedCommunities($data['userId'])) {
                return new JsonResponse($userOwnedCommunities);
            }
        }

        return new JsonResponse($userOwnedCommunities);
    }

    /**
     * Get all communities owned by a user
     * AJAX.
     */
    public function userCreatedEvents(Request $request, EventManager $eventManager)
    {
        $userCreatedEvents = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($userCreatedEvents = $eventManager->getCreatedEvents($data['userId'])) {
                return new JsonResponse($userCreatedEvents);
            }
        }

        return new JsonResponse($userCreatedEvents);
    }

    /**
     * Get the bank coordinates of a user
     * AJAX.
     */
    public function getBankCoordinates(Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($this->userManager->getBankCoordinates());
        }

        return new JsonResponse();
    }

    /**
     * Get the bank coordinates of a user
     * AJAX.
     */
    public function addBankCoordinates(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($this->paymentManager->addBankCoordinates($data['iban'], $data['bic'], $data['address']));
        }

        return new JsonResponse();
    }

    /**
     * Get the bank coordinates of a user
     * AJAX.
     */
    public function deleteBankCoordinates(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($this->paymentManager->deleteBankCoordinates($data['bankAccountId']));
        }

        return new JsonResponse();
    }

    /**
     * Block or Unblock a User
     * AJAX.
     */
    public function sendIdentityValidationDocument(Request $request)
    {
        if ($request->isMethod('POST')) {
            if (!is_null($request->files->get('document'))) {
                $document = new ValidationDocument();
                $document->setFile($request->files->get('document'));

                return new JsonResponse($this->userManager->sendIdentityValidationDocument($document));
            }
        }

        return new JsonResponse();
    }

    /**
     * Block or Unblock a User
     * AJAX.
     */
    public function blockUser(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($this->userManager->blockUser($data['blockedUserId']));
        }

        return new JsonResponse();
    }

    /**
     * Return page after a SSO Login
     * Url is something like /user/sso/login?state=PassMobilite&code=1.
     * It can also be a POST form if the response_mode of the SSO Provider is form_post.
     */
    public function userReturnConnectSSO(Request $request)
    {
        if ($request->isMethod('POST')) {
            $response_body = $this->__parsePostParams($request->getContent());
        } else {
            $response_body = $request->query->all();
        }

        $params = $this->ssoManager->guessSsoParameters($response_body);

        // We add the front url to the parameters
        $params['baseSiteUri'] = isset($_SERVER['HTTPS']) ? 'https://'.$_SERVER['HTTP_HOST'] : 'http://'.$_SERVER['HTTP_HOST'];
        $params['redirectUri'] = $request->getPathInfo();

        // We add the service name
        $services = $this->userManager->getSsoServices();

        if (!is_null($services) && is_array($services)) {
            foreach ($services as $service) {
                if ($service->getSsoProvider() == $params['ssoProvider']) {
                    $params['ssoProviderName'] = $service->getSsoProvider();
                }
            }
        }

        return $this->redirectToRoute('user_login_sso', $params);
    }

    /**
     * Return page after a SSO Login from mobConnect
     * Url is something like /user/sso/cee-incentive?state=mobConnect&code=1.
     */
    public function userReturnConnectSSOMobConnect(Request $request)
    {
        $requestParams = $request->query->all();

        $params = [
            'ssoProvider' => $requestParams['state'],
            'ssoId' => $requestParams['code'],
            'baseSiteUri' => $request->getScheme().'://'.$request->server->get('HTTP_HOST').'/user/sso',
            'eec' => 1,
        ];

        if ($this->getUser()) {
            $this->userManager->patchUserForSsoAssociation($this->getUser(), $params);
        }

        return $this->redirectToRoute('user_profile_update', [
            'tabDefault' => 'mon-profil',
            'afterEECSubscription' => true,
        ]);
    }

    public function userReturnConnectSsoMobile(Request $request)
    {
        return $this->mobileRedirect($request->server->get('URL_MOBILE'), 'login', $request->query->all());
    }

    public function userReturnConnectSsoMobConnectMobile(Request $request)
    {
        return $this->mobileRedirect($request->server->get('URL_MOBILE'), 'eec-incentive', $request->query->all());
    }

    /**
     * Login route with sso credentials
     * Something like /user/sso/login/autolog?ssoId=1&ssoProvider=PassMobilite&baseSiteUri=http://localhost:8081.
     */
    public function userLoginSso(Request $request)
    {
        return new JsonResponse();
    }

    /**
     * Return page after a SSO Login.
     */
    public function userReturnLogoutSSO(Request $request)
    {
        return new JsonResponse(['error' => false, 'message' => 'SSO Logout']);
    }

    /**
     * Return the Sso connection services of the platform.
     *
     * AJAX
     */
    public function getSsoServices(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['service']) && !is_null($data['service'])) {
                return $this->getSsoService($request);
            }

            return new JsonResponse($this->userManager->getSsoServices());
        }

        return new JsonResponse();
    }

    /**
     * Return a specific Sso connection service of the platform.
     *
     * AJAX
     */
    public function getSsoService(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $path = isset($data['path']) ? $data['path'] : null;
            $redirectUri = isset($data['redirectUri']) ? $data['redirectUri'] : null;

            return new JsonResponse($this->userManager->getSsoService($data['service'], $path, $redirectUri));
        }

        return new JsonResponse();
    }

    /**
     * Return the user profile summary.
     *
     * @return JsonResponse
     */
    public function userProfileSummary(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($this->userManager->getProfileSummary($data['userId']));
        }

        return new JsonResponse();
    }

    /**
     * Return the user public profile.
     *
     * @return JsonResponse
     */
    public function userProfilePublic(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($this->userManager->getProfilePublic($data['userId']));
        }

        return new JsonResponse();
    }

    /**
     * Report a User.
     *
     * @param mixed $id
     */
    public function userReport($id, DataProvider $dataProvider, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $success = false;

            // Post the Report
            if (
                isset($data['email'], $data['text'])
                && '' !== $data['email'] && '' !== $data['text']
            ) {
                $dataProvider->setClass(Report::class);

                $report = new Report();
                $report->setUserId($id);
                $report->setReporterEmail($data['email']);
                $report->setText($data['text']);

                $response = $dataProvider->post($report);

                if (201 === $response->getCode()) {
                    $success = true;
                }
            }
        }

        return new JsonResponse(['success' => $success]);
    }

    /**
     * Write a review about a User.
     */
    public function userReviewWrite(Request $request, ReviewManager $reviewManager)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (
                isset($data['reviewerId'], $data['reviewedId'], $data['content'])
                && '' !== $data['reviewerId'] && '' !== $data['reviewedId'] && '' !== $data['content']
            ) {
                return new JsonResponse(['success' => $reviewManager->createReview($data['reviewerId'], $data['reviewedId'], $data['content'])]);
            }
        }

        return new JsonResponse(['success' => false]);
    }

    /**
     * Get the Review Dashboard of a User.
     */
    public function userReviewDashboard(Request $request, ReviewManager $reviewManager)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($reviewManager->reviewDashboard());
        }

        return new JsonResponse();
    }

    /**
     * Update the User's Language.
     */
    public function userUpdateLanguage(Request $request)
    {
        if ($request->isMethod('POST') && $this->userManager->getLoggedUser()) {
            $user = $this->userManager->getLoggedUser();
            $data = json_decode($request->getContent(), true);

            $language = new Language();
            $language->setCode($data['language']);

            $user->setLanguage($language);

            $this->userManager->updateUserLanguage($user);

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse();
    }

    /**
     * Update the User's Gamification prefs.
     */
    public function userUpdateGamification(Request $request)
    {
        if ($request->isMethod('PUT') && $this->userManager->getLoggedUser()) {
            $user = $this->userManager->getLoggedUser();
            $data = json_decode($request->getContent(), true);
            if (isset($data['gamification']) && '' !== $data['gamification']) {
                $user->setGamification($data['gamification']);

                $this->userManager->updateUser($user);

                return new JsonResponse(['success' => true]);
            }
        }

        return new JsonResponse();
    }

    // ADMIN
    /**
     * Login Admin.
     */
    public function loginAdmin(Request $request)
    {
        if ($this->loginDelegate) {
            return $this->render('@Mobicoop/user/loginAdmin.html.twig', [
                'email' => $request->get('email'),
                'delegate_email' => $request->get('delegateEmail'),
            ]);
        }

        throw new AccessDeniedException('Access Denied.');
    }

    public function myCeeSubscriptions()
    {
        return new JsonResponse($this->ceeSubscriptionManager->myCeeSubscriptions());
    }

    public function myEecSubscriptionsEligibility()
    {
        return new JsonResponse($this->ceeSubscriptionManager->myEecSubscriptionsEligibility());
    }

    private function mobileRedirect(string $host, string $path, array $params)
    {
        $redirectUri = $host.'/#/carpools/user/sso/'.$path.'?'.http_build_query($params, '', '&');

        return $this->redirect($redirectUri);
    }
}
