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

namespace Mobicoop\Bundle\MobicoopBundle\User\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;
use DateTime;
use DoctrineExtensions\Query\Mysql\Format;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\MyAd;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\BankAccount;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\ValidationDocument;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Block;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\PhoneValidation;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\ProfileSummary;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\PublicProfile;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\SsoConnection;

/**
 * User management service.
 */
class UserManager
{
    private $dataProvider;
    private $encoder;
    private $tokenStorage;
    private $logger;


    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     * @param UserPasswordEncoderInterface $encoder
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(DataProvider $dataProvider, UserPasswordEncoderInterface $encoder, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(User::class);
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * Get a user by its identifier
     *
     * @param int $id The user id
     *
     * @return User|null The user found or null if not found.
     */
    public function getUser(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $user = $response->getValue();
            if ($user->getBirthDate()) {
                $user->setBirthYear($user->getBirthDate()->format('Y'));
            }
            return $user;
        }
        return null;
    }

    /**
     * Get the profile summary of a user
     *
     * @param integer $userId   User id
     * @return ProfileSummary|null
     */
    public function getProfileSummary(int $userId): ?ProfileSummary
    {
        $this->dataProvider->setClass(ProfileSummary::class);
        $response = $this->dataProvider->getItem($userId);
        if ($response->getCode() == 200) {
            $profileSummary = $response->getValue();
            return $profileSummary;
        }
        return null;
    }

    /**
     * Get the public profile of a user
     *
     * @param integer $userId   User id
     * @return ProfileSummary|null
     */
    public function getProfilePublic(int $userId): ?PublicProfile
    {
        $this->dataProvider->setClass(PublicProfile::class);
        $response = $this->dataProvider->getItem($userId);
        if ($response->getCode() == 200) {
            $profileSummary = $response->getValue();
            return $profileSummary;
        }
        return null;
    }

    /**
     * Search user by password reset token
     *
     * @param string $token
     *
     * @return User|null The user found or null if not found.
     */
    public function findByPwdToken(string $token)
    {
        $response = $this->dataProvider->getCollection(['pwdToken' => $token]);
        if ($response->getCode() == 200) {
            /** @var Hydra $user */
            $user = $response->getValue();

            if ($user->getTotalItems() == 0) {
                return null;
            } else {
                return current($user->getMember());
            }
        }
        return null;
    }

    /**
     * Search user by validation date token
     *
     * @param string $token
     *
     * @return User|null The user found or null if not found.
     */
    public function findByValidationDateToken(string $token)
    {
        $response = $this->dataProvider->getCollection(['validatedDateToken' => $token]);
        if ($response->getCode() == 200) {
            /** @var Hydra $user */
            $user = $response->getValue();

            if ($user->getTotalItems() == 0) {
                return null;
            } else {
                return current($user->getMember());
            }
        }
        return null;
    }

    /**
     * Search user by unsubscribe token
     *
     * @param string $token
     *
     * @return User|null The user found or null if not found.
     */
    public function findByUnsubscribeToken(string $token)
    {
        $response = $this->dataProvider->getCollection(['unsubscribeToken' => $token]);
        if ($response->getCode() == 200) {
            /** @var Hydra $user */
            $user = $response->getValue();

            if ($user->getTotalItems() == 0) {
                return null;
            } else {
                return current($user->getMember());
            }
        }
        return null;
    }


    /**
     * Search user by email
     *
     * @param string $email
     *
     * @return User|null The user found or null if not found.
     */
    public function findByEmail(string $email, bool $sendEmailRecovery = false)
    {
        $response = $this->dataProvider->getCollection(['email' => $email]);
        if ($response->getCode() == 200) {
            /** @var Hydra $user */
            $user = $response->getValue();

            if ($user->getTotalItems() == 0) {
                return null;
            } else {
                if ($sendEmailRecovery) {
                    $this->updateUserToken($user->getMember()[0]);
                }

                return current($user->getMember());
            }
        }
        return null;
    }

    /**
     * Search user by phone number
     *
     * @param string $getTelephone
     *
     * @return User|null The user found or null if not found.
     */
    public function findByPhone(string $getTelephone, bool $sendEmailRecovery = false)
    {
        $response = $this->dataProvider->getCollection(['email' => $getTelephone]);
        if ($response->getCode() == 200) {
            /** @var Hydra $user */
            $user = $response->getValue();

            if ($user->getTotalItems() == 0) {
                return null;
            } else {
                if ($sendEmailRecovery) {
                    $this->updateUserToken($user->getMember()[0]);
                }

                return current($user->getMember());
            }
        }
        return null;
    }

    /**
     * Send the recovery mail password
     * @param string $email The user email that requested the password change
     */
    public function sendEmailRecoveryPassword(string $email)
    {
        $user = new User();
        $user->setEmail($email);
        $response = $this->dataProvider->postSpecial($user, ['passwordUpdateRequest'], "password_update_request");
        return $response->getValue();
    }


    /**
     * Get masses of a user
     *
     * @param int $id The user id
     *
     * @return Mass[]|null The user found or null if not found.
     */
    public function getMasses(int $id)
    {
        $response = $this->dataProvider->getSubCollection($id, Mass::class);
        return $response->getValue();
    }

    /**
     * Get the logged user.
     *
     * @return User|null The logged user found or null if not found.
     */
    public function getLoggedUser()
    {
        if ($this->tokenStorage->getToken() === null) {
            return null;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            if ($user->getBirthDate()) {
                $user->setBirthYear($user->getBirthDate()->format('Y'));
            }
            $this->logger->info('User | Is logged');
            return $user;
        }
        $this->logger->error('User | Not logged');
        return null;
    }

    /**
     * Get all users
     *
     * @return array|null The users found or null if not found.
     */
    public function getUsers()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            $this->logger->info('User | Found');
            return $response->getValue();
        }
        $this->logger->error('User | Not found');
        return null;
    }

    /**
    * Create a user
    *
    * @param User $user The user to create
    *
    * @return User|null The user created or null if error.
    */
    public function createUser(User $user)
    {
        $this->logger->info('User Creation | Start');
        $response = $this->dataProvider->postSpecial($user, null, 'register');
        if ($response->getCode() == 201) {
            $this->logger->info('User Creation | Ok');
        }
        $this->logger->error('User Creation | Fail');
        return null;
    }

    /**
     * Update a user
     *
     * @param User $user The user to update
     *
     * @return User|null The user updated or null if error.
     */
    public function updateUser(User $user)
    {
        $response = $this->dataProvider->put($user);
        if ($response->getCode() == 200) {
            $this->logger->info('User Update | Start');
            return $response->getValue();
        }
        return null;
    }

    /**
     * Update a user password
     *
     * @param User $user The user to update the password
     *
     * @return User|null The user updated or null if error.
     */
    public function updateUserPassword(User $user)
    {
        // encoding of the password
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
        $response = $this->dataProvider->put($user, ['password']);
        if ($response->getCode() == 200) {
            $this->logger->info('User Password Update | Start');
            return $response->getValue();
        }
        $this->logger->info('User Password Update | Fail');
        return null;
    }

    /**
     * Update a user password
     *
     * @param User $user The user to update the password
     *
     * @return User|null The user updated or null if error.
     */
    public function updateUserLanguage(User $user)
    {
        $response = $this->dataProvider->putSpecial($user, ['language'], "updateLanguage");
        if ($response->getCode() == 200) {
            $this->logger->info('User Language Update | Start');
            return $response->getValue();
        }
        $this->logger->info('User Language Update | Fail');
        return null;
    }

    /**
     * Update a user password from the reset form
     * @param string $token     The token to retrieve the user
     * @param string $password  The new password
     */
    public function userUpdatePasswordReset(string $token, string $password)
    {
        $user = new User();
        $user->setPwdToken($token);
        $user->setPassword($password);
        return $this->dataProvider->postSpecial($user, ['passwordUpdate'], "password_update")->getValue();
    }

    /**
     * Delete a user
     *
     * @param int $id The id of the user to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteUser(User $user)
    {
        $response = $this->dataProvider->delete($user->getId());
        //L'user est anonymiser
        if ($response->getCode() == 200) {
            return $response->getValue();
        }

        return $response;
    }

    /**
     * Get the communities where the user is member
     *
     * @param User $user            The user
     * @param integer|null $status  The status of the membership
     * @return void
     */
    public function getCommunities(User $user, ?int $status = null)
    {
        $params = [
            'user.id' => $user->getId()
        ];
        if (!is_null($status)) {
            $params['status'] = $status;
        }
        $this->dataProvider->setClass(CommunityUser::class);
        $response = $this->dataProvider->getCollection($params);
        if ($response->getCode() == 200 && $response->getValue()->getMember() !== null && is_array($response->getValue()->getMember())) {
            $communities = [];
            foreach ($response->getValue()->getMember() as $communityUser) {
                if ($communityUser->getCommunity() instanceof Community) {
                    $communities[] = $communityUser->getCommunity();
                }
            }
            return $communities;
        }
        return null;
    }

    /**
     * OBSOLETE---11/03/2020
     * Get the threads (messages) of a user
     *
     * @param User $user The user
     *
     * @return array The messages.
     */
    // public function getThreads(User $user)
    // {
    //     $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
    //     $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threads');
    //     return $response->getValue();
    // }

    /**
     * Get the threads of direct messages of a user
     *
     * @param User $user The user
     *
     * @return array The messages.
     */
    public function getThreadsDirectMessages(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_LDJSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threadsDirectMessages')->getValue();
        return $response;
    }

    /**
     * Get the threads of direct messages of a user
     *
     * @param User $user The user
     *
     * @return array The messages.
     */
    public function getThreadsCarpoolMessages(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_LDJSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threadsCarpoolMessages');
        return $response->getValue();
    }

    /**
     * Get the threads of solidary related messages of a user
     *
     * @param User $user The user
     *
     * @return array The messages.
     */
    public function getThreadsSolidaryMessages(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threadsSolidaryMessages');
        return $response->getValue();
    }

    /**
     * Update the user token.
     *
     * @param User $user
     * @return array|null|object
     */
    public function updateUserToken($user)
    {
        return $this->flushUserToken($user, 'password_update_request');
    }

    /**
     * Flush the user token.
     *
     * @param User $user
     * @return array|null|object
     */
    public function flushUserToken(User $user, string $operation = null)
    {
        if (empty($operation)) {
            $operation='password_update';
        }
        $response = $this->dataProvider->putSpecial($user, ['password_token'], $operation);
        if ($response->getCode() == 200) {
            $this->logger->info('User Token Update | Start');
            return $response->getValue();
        } else {
            return $response->getValue();
        }
        $this->logger->info('User Token Update | Fail');
    }

    /**
     * Get the alerts of a user
     *
     * @param User $user The user
     *
     * @return array The alerts.
     */
    public function getAlerts(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'alert', 'alerts');
        return $response->getValue();
    }

    /**
     * Update a user alert
     *
     * @param User $user
     * @param int $alertId
     * @param Boolean $active
     * @return array|null|object
     */
    public function updateAlert(User $user, int $alertId, bool $active)
    {
        $user->setAlerts([$alertId=>$active]);
        $response = $this->dataProvider->putSpecial($user, null, "alerts");
        if ($response->getCode() == 200) {
            return $response->getValue();
        } else {
            return $response->getValue();
        }
    }

    /**
     * Get the ads of an user
     *
     * @param User $user
     * @param bool $isAcceptedCarpools
     * @return array|object
     * @throws \ReflectionException
     */
    public function getAds(bool $isAcceptedCarpools = false)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_OBJECT);
        $this->dataProvider->setClass(Ad::class, Ad::RESOURCE_NAME);
        $response = $isAcceptedCarpools ? $this->dataProvider->getSpecialCollection("accepted") : $this->dataProvider->getCollection();

        $ads = $response->getValue()->getMember();

        $adsSanitized = [
            "ongoing" => [],
            "archived" => []
        ];
        /** @var Ad $ad */
        foreach ($ads as $ad) {
            $isAlreadyInArray = false;

            if (isset($adsSanitized["ongoing"][$ad->getId()]) ||
                isset($adsSanitized["archived"][$ad->getId()])) {
                $isAlreadyInArray = true;
            }

            if ($isAlreadyInArray) {
                continue;
            }

            $now = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
   
            // Carpool regular
            if ($ad->getFrequency() === Ad::FREQUENCY_REGULAR) {
                $date = $ad->getOutwardLimitDate();
            }
            // Carpool punctual
            else {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $ad->getReturnTime() ? $ad->getReturnTime() : $ad->getOutwardTime());
            }
            $key = $date >= $now ? 'ongoing' : 'archived';
            $adsSanitized[$key][$ad->getId()] = $ad;
        }
        return $adsSanitized;
    }

    /**
     * Get the ads of user
     *
     * @return array
     */
    public function getMyAds()
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_OBJECT);
        $this->dataProvider->setClass(MyAd::class, MyAd::RESOURCE_NAME);
        $response = $this->dataProvider->getCollection();

        $myAds = $response->getValue()->getMember();

        $ads = [
            'published' => [
                'active' => [],
                'archived' => []
            ],
            'accepted' => [
                'active' => [],
                'archived' => []
            ]
        ];
        /** @var MyAd $myAd */
        foreach ($myAds as $myAd) {
            // we check if the ad is still valid
            $valid = false;
            $now = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
            if ($myAd->getFrequency() === MyAd::FREQUENCY_REGULAR) {
                // regular
                $date = \DateTime::createFromFormat('Y-m-d', $myAd->getToDate());
                $date->setTime(0, 0);
            } else {
                // punctual
                $date = \DateTime::createFromFormat(
                    'Y-m-d H:i',
                    (!is_null($myAd->getReturnDate()) && !is_null($myAd->getReturnTime())) ?
                    $myAd->getReturnDate() . " " . $myAd->getReturnTime() :
                    $myAd->getOutwardDate() . " " . $myAd->getOutwardTime()
                );
            }
            if ($date >= $now) {
                $valid = true;
            }
            if ($myAd->isPublished()) {
                if ($valid) {
                    $ads['published']['active'][] = $myAd;
                } else {
                    $ads['published']['archived'][] = $myAd;
                }
            }
            if (count($myAd->getDriver())>0 || count($myAd->getPassengers())>0) {
                if ($valid) {
                    $ads['accepted']['active'][] = $myAd;
                } else {
                    $ads['accepted']['archived'][] = $myAd;
                }
            }
        }
        return $ads;
    }


    /**
     * Cleaning the Matchings related to private Proposals
     */
    private function cleanPrivateMatchings($proposal, $type='Offers')
    {
        if (is_array($proposal['matching'.$type]) && count($proposal['matching'.$type])>0) {
            foreach ($proposal['matching'.$type] as $keyMatching => $matching) {
                if (is_array($matching['proposalOffer']) && count($matching['proposalOffer'])>0) {
                    $proposalOffer = $matching['proposalOffer'];
                    if (is_array($proposalOffer) && $proposalOffer['private']) {
                        unset($proposal['matchingOffers'][$keyMatching]);
                    }
                }
                if (is_array($matching['proposalRequest']) && count($matching['proposalRequest'])>0) {
                    $proposalRequest = $matching['proposalRequest'];
                    if (is_array($proposalRequest) && $proposalRequest['private']) {
                        unset($proposal['matching'.$type][$keyMatching]);
                    }
                }
            }
            $proposal['matching'.$type] = array_values($proposal['matching'.$type]);
        }
        return $proposal;
    }

    /**
    * Generate phone token
    *
    * @param User $user The user to generate phone token
    *
    * @return User|null The user or null if error.
    */
    public function generatePhoneToken(User $user)
    {
        $response = $this->dataProvider->getSpecialItem($user->getId(), "generate_phone_token");
        if ($response->getCode() == 200) {
            $this->logger->info('User PhoneToken Update | Start');
            return $response->getValue();
        }
        $this->logger->info('User PhoneToken Update | Fail');
        return null;
    }


    /**
     * Get the asks of a user
     *
     * @param User $user The user
     *
     * @return array Of asks or null
     */
    public function getAsks(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'ask', 'asks');
        return $response->getValue();
    }

    /**
     * Validation phone
     *
     * @param string $token
     * @param string $phone
     *
     * @return User|null The user found or null if not found.
     */
    public function validPhoneByToken(string $token, string $phone)
    {
        $user = new User();
        $user->setTelephone($phone);
        $user->setPhoneToken($token);
        $response = $this->dataProvider->postSpecial($user, ["checkPhoneToken"], "checkPhoneToken");

        return $response->getValue();
    }

    /**
     * Unsubscribe the user from receiving news
     *
     * @param string $token
     * @param string $phone
     *
     * @return User|null The user found or null if not found.
     */
    public function unsubscribeUserFromEmail(string $token)
    {
        $user = $this->findByUnsubscribeToken($token);
        $response = $this->dataProvider->putSpecial($user, null, "unsubscribe_user");

        return $response->getValue();
    }

    /**
     * Check if the email is already in use
     *
     * @param string $email
     * @return void
     */
    public function checkEmail(string $email)
    {
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSpecialCollection('checkEmail', ['email' => $email]);
        return $response->getValue();
    }

    /**
     * Check if the phone number is valid
     *
     * @param string $phoneNumber
     * @return void
     */
    public function checkPhoneNumberValidity(string $phoneNumber)
    {
        $phoneValidation = new PhoneValidation($phoneNumber);
        $phoneValidation->setPhoneNumber($phoneNumber);
        $this->dataProvider->setClass(PhoneValidation::class);
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->post($phoneValidation);
        if ($response->getCode() == 201) {
            return json_decode($response->getValue());
        }
        return null;
    }

    /**
     * Check if password token exist
     *
     * @param string $pwdToken
     * @return void
     */
    public function checkPasswordToken(string $pwdToken)
    {
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSpecialCollection('checkPasswordToken', ['pwdToken' => $pwdToken]);
        return $response->getValue();
    }

    /**
     * Get the bank coordinates of a User
     *
     * @return BankAccount[]
     */
    public function getBankCoordinates()
    {
        $response = $this->dataProvider->getSpecialCollection('paymentProfile');
        if ($response->getCode() == 200) {
            $users = $response->getValue()->getMember();
            if (count($users)==1) {
                return $users[0]->getBankAccounts();
            }
        }
        return null;
    }

    /**
     * Send an Identity validation document
     *
     * @param ValidationDocument $document
     * @return ValidationDocument|null
     */
    public function sendIdentityValidationDocument(ValidationDocument $document): ?ValidationDocument
    {
        $this->dataProvider->setClass(ValidationDocument::class);
        $response = $this->dataProvider->postMultiPart($document);

        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Post a Block
     * @param int $userId Id of the User to block
     * @return Block|null
     */
    public function blockUser(int $userId)
    {
        $block = new Block();
        $block->setUser(new User($userId));
        
        $this->dataProvider->setClass(Block::class);
        $response = $this->dataProvider->post($block);
        
        if ($response->getCode() == 200) {
            return $response->getValue()->getMember();
        }
        return null;
    }

    /**
    * Get carpoolExport file
    *
    * @param User $user the user
    *
    * @return User|null The user or null if error.
    */
    public function getCarpoolExport(User $user)
    {
        $response = $this->dataProvider->getSpecialItem($user->getId(), "carpool_export");
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Get the Sso connection services of the platform
     *
     * @return void
     */
    public function getSsoServices(): ?array
    {
        $this->dataProvider->setClass(SsoConnection::class);

        // We add the front url to the parameters
        $baseSiteUri = (isset($_SERVER['HTTPS'])) ? 'https://'.$_SERVER['HTTP_HOST']  : 'http://'.$_SERVER['HTTP_HOST'];
        
        $response = $this->dataProvider->getCollection(['baseSiteUri' => $baseSiteUri]);
        if ($response->getCode() == 200) {
            return $response->getValue()->getMember();
        }
        return null;
    }

    /**
     * Send a validation email
     *
     * @param User $user the user
     * @return void
     */
    public function sendValidationEmail(User $user)
    {
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSpecialItem($user->getId(), 'sendValidationEmail');
        return $response->getValue();
    }
}
