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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class UserController extends AbstractController
{

    /**
     * User login.
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $this->denyAccessUnlessGranted('login');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        $login = new Login();

        $form = $this->createForm(UserLoginForm::class, $login);

        return $this->render('@Mobicoop/user/login.html.twig', [
            "form"=>$form->createView(),
            "error"=>$error
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
        $form = $this->createForm(UserForm::class, $user, ['validation_groups'=>['signUp']]);
        $error = false;
        $success = false;
        
        if ($request->isMethod('POST')) {
            $createToken = $request->request->get('createToken');
            if (!$this->isCsrfTokenValid('user-signup', $createToken)) {
                return  new Response('Broken Token CSRF ', 403);
            }

            //get all data from form (user + homeAddress)
            $data = $request->request->get($form->getName());
            
            // pass homeAddress info into address entity
            $address->setAddressCountry($data['addressCountry']);
            $address->setAddressLocality($data['addressLocality']);
            $address->setCountryCode($data['countryCode']);
            $address->setCounty($data['county']);
            $address->setLatitude($data['latitude']);
            $address->setLocalAdmin($data['localAdmin']);
            $address->setLongitude($data['longitude']);
            $address->setMacroCounty($data['macroCounty']);
            $address->setMacroRegion($data['macroRegion']);
            $address->setName($translator->trans('homeAddress', [], 'signup'));
            $address->setPostalCode($data['postalCode']);
            $address->setRegion($data['region']);
            $address->setStreet($data['street']);
            $address->setStreetAddress($data['streetAddress']);
            $address->setSubLocality($data['subLocality']);
            $address->setHome(true);

            // pass front info into user form
            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setPassword($data['password']);
            $user->setGivenName($data['givenName']);
            $user->setFamilyName($data['familyName']);
            $user->setGender($data['gender']);

            $user->setBirthYear($data['birthYear']);


            // add the home address to the user
            
            $user->addAddress($address);

            // Not Valid populate error
            // if (!$form->isValid()) {
            //     $error = [];
            //     // Fields
            //     foreach ($form as $child) {
            //         if (!$child->isValid()) {
            //             foreach ($child->getErrors(true) as $err) {
            //                 $error[$child->getName()][] = $err->getMessage();
            //             }
            //         }
            //     }
            //     return $this->json(['error' => $error, 'success' => $success]);
            // }
            // create user in database
            $userManager->createUser($user);
        }
 
        if (!$form->isSubmitted()) {
            return $this->render('@Mobicoop/user/signup.html.twig', [
                'error' => $error
            ]);
        }
        return $this->json(['error' => $error, 'success' => $success]);
    }

    /**
     * User profile update.
     */
    public function userProfileUpdate(UserManager $userManager, Request $request, AddressManager $addressManager)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);

        // get the homeAddress
        $homeAddress = $user->getHomeAddress();
         
        $form = $this->createForm(UserForm::class, $user, ['validation_groups'=>['update']]);
        $error = false;
           
        
        if ($request->isMethod('POST')) {

            //get all data from form (user + homeAddress)
            $data = $request->request->get($form->getName());

            //pass homeAddress info into address entity
            if (!$homeAddress) {
                $homeAddress = new Address();
            }
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
            
            // pass front info into user form
            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setGivenName($data['givenName']);
            $user->setFamilyName($data['familyName']);
            $user->setGender($data['gender']);
            $user->setBirthYear($data['birthYear']);
            
            if (is_null($homeAddress->getId()) && !empty($homeAddress->getLongitude() && !empty($homeAddress->getLatitude()))) {
                $homeAddress->setName(User::HOME_ADDRESS_NAME);
                $user->addAddress($homeAddress);
            } elseif (!empty($homeAddress->getLongitude() && !empty($homeAddress->getLatitude()))) {
                $addressManager->updateAddress($homeAddress);
            }
            $userManager->updateUser($user);
            exit;
        }
      
        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
                'error' => $error,
                'user' => $user
            ]);
    }

    /**
     * User password update.
     */
    public function userPasswordUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('password', $user);
        $form = $this->createForm(
            UserForm::class,
            $user,
            ['validation_groups'=>['password']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user = $userManager->updateUserPassword($user)) {
                // after successful update, we re-log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                return $this->redirectToRoute('user_profile_update');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/user/password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'error' => $error
        ]);
    }


    /**
     * User password update.
     * @param UserManager $userManager
     *     The class managing the user.
     * @param Request $request
     *     The symfony request object.
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws Exception
     */
    public function userPasswordForgot(UserManager $userManager, Request $request, \Swift_Mailer $mailer)
    {
        /** @var Session $session */
        $session= $this->get('session');
        $userRequest= new User();
        $form = $this->createFormBuilder($userRequest)
        ->add('email', EmailType::class, ['required'=> false])
        ->add('telephone', TextType::class, ['required' => false])
        ->add('submit', SubmitType::class)
        ->getForm();

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($userRequest->getEmail())) {
                /** @var User $user */
                $user = $userManager->findByEmail($userRequest->getEmail());
            } else {
                if (!empty($userRequest->getTelephone())) {
                    $user = $userManager->findByPhone($userRequest->getTelephone());
                } else {
                    return $this->redirectToRoute('user_password_forgot');
                }
            }
            if (empty($user)) {
                return $this->redirectToRoute('user_password_forgot');
            } else {
                $data= $userManager->updateUserToken($user);
                if (!empty($data)) {
                    return $this->redirectToRoute('user_password_forgot');
                } else {
                    return $this->redirectToRoute('user_password_forgot');
                }
            }
        } else {
            return $this->render('@Mobicoop/user/password.html.twig', [
                'form' => $form->createView(),
                'user' => $user??$userRequest,
                'error' => $error,
                'waitParametersForMail' => true
            ]);
        }
    }

    /**
     * Reset password
     */
    public function userPasswordReset(UserManager $userManager, Request $request, string $token, \Swift_Mailer $mailer)
    {
        /** @var Session $session */
        $session= $this->get('session');
        $user = $userManager->findByToken($token);
        $error = false;

        if (empty($user) || (time() - (int)$user->getPupdtime()->getTimestamp()) > 86400) {
            return $this->redirectToRoute('user_password_forgot');
        } else {
            $form = $this->createFormBuilder($user)
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                ])
                ->add('submit', SubmitType::class)
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($user = $userManager->updateUserPassword($user)) {
                    // after successful update, we re-log the user
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    $userManager->flushUserToken($user);
                    return $this->redirectToRoute('user_profile_update');
                } else {
                    return $this->redirectToRoute('user_password_forgot');
                }
            } else {
                return $this->render('@Mobicoop/user/password.html.twig', [
                    'form' => $form->createView(),
                    'user' => $user,
                    'error' => $error,
                    'waitParametersForMail' => true
                ]);
            }
        }
    }

    /**
     * Delete the current user.
     */
    public function userProfileDelete(UserManager $userManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('delete', $user);

        $form = $this->createForm(
            UserDeleteForm::class,
            $user,
            ['validation_groups'=>['delete']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($userManager->deleteUser($user->getId())) {
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
        $this->denyAccessUnlessGranted('proposals_self', $user);

        return $this->render('@Mobicoop/proposal/index.html.twig', [
            'hydra' => $proposalManager->getProposals($user)
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

        // Building threads array
        $threads = $userManager->getThreads($user);
        $idMessageDefaultSelected = false;
        
        foreach ($threads["threads"] as $thread) {
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
            'lastNameRecipientDefault'=>$lastNameRecipientDefault
        ]);
    }

    /**
     * Get a complete thread from a first message
     * Ajax Request
     */
    public function getThread(int $idFirstMessage, UserManager $userManager, InternalMessageManager $internalMessageManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);

        $thread = $internalMessageManager->getThread($idFirstMessage, DataProvider::RETURN_JSON);

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
        return new Response(json_encode($thread));
    }

    /**
     * Send an internal message to another user
     * Ajax Request
     */
    public function sendInternalMessage(UserManager $userManager, InternalMessageManager $internalMessageManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('messages', $user);

        if ($request->isMethod('POST')) {
            $idThreadMessage = $request->request->get('idThreadMessage');
            $idRecipient = $request->request->get('idRecipient');

            $messageToSend = $internalMessageManager->createInternalMessage(
                $user,
                $userManager->getUser($idRecipient),
                "",
                $request->request->get('text'),
                $internalMessageManager->getMessage($idThreadMessage)
            );

            return new Response($internalMessageManager->sendInternalMessage($messageToSend, DataProvider::RETURN_JSON));
        }
        return new Response(json_encode("Not a post"));
    }

    /**
     * Get the details of an AskHistory
     * Ajax Request
     */
    public function getAskHistoryDetails()
    {
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
