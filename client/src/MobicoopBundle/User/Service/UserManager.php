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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Geography\Service\AddressManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;
use DateTime;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;

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
            $this->logger->info('User | Is found');
            return $user;
        }
        $this->logger->error('User | is Not found');
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
     * @param int $userId The user id that requested the password change
     */
    public function sendEmailRecoveryPassword(int $userId)
    {
        return $this->dataProvider->getSpecialItem($userId, "password_update_request");
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
        // encoding of the password
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        $this->logger->info('User Creation | Start');
        $response = $this->dataProvider->post($user);
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
     * Delete a user
     *
     * @param int $id The id of the user to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteUser(User $user)
    {
        //$response = $this->dataProvider->getSpecialItem($user->getId(), "anonymise_user");
        $response = $this->dataProvider->putSpecial($user, null, "anonymise_user");
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
     * Get the threads (messages) of a user
     *
     * @param User $user The user
     *
     * @return array The messages.
     */
    public function getThreads(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threads');
        return $response->getValue();
    }

    /**
     * Get the threads of direct messages of a user
     *
     * @param User $user The user
     *
     * @return array The messages.
     */
    public function getThreadsDirectMessages(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threadsDirectMessages');
        return $response->getValue();
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
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($user->getId(), 'thread', 'threadsCarpoolMessages');
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
     * Get the proposals of an user
     *
     * @param User $user
     * @return array|object
     * @throws \ReflectionException
     */
    public function getProposals(User $user)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        // we set the private param to false to get only published ad, not proposals posted after a search
        $response = $this->dataProvider->getSubCollection($user->getId(), Proposal::class, null, ['private'=>false]);
        $proposals = $response->getValue();
        
        $proposalsSanitized = [
            "ongoing" => [],
            "archived" => []
        ];
        
        /** @var \App\Carpool\Entity\Proposal $proposal */
        foreach ($proposals as $proposal) {
            $isAlreadyInArray = false;
            
            if (isset($proposalsSanitized["ongoing"][$proposal["id"]]) || isset($proposalsSanitized["ongoing"][$proposal["proposalLinked"]["id"]]) ||
                isset($proposalsSanitized["archived"][$proposal["id"]]) || isset($proposalsSanitized["archived"][$proposal["proposalLinked"]["id"]])) {
                $isAlreadyInArray = true;
            }
            
            if ($isAlreadyInArray) {
                continue;
            }

            $now = new DateTime();
            
            // Carpool regular
            if ($proposal["criteria"]["frequency"] === Criteria::FREQUENCY_REGULAR) {
                $date = new DateTime($proposal["criteria"]["toDate"]);
            }
            // Carpool punctual
            else {
                $fromDate = new DateTime($proposal["criteria"]["fromDate"]);
                $linkedDate = isset($proposal["proposalLinked"]) ? new DateTime($proposal["proposalLinked"]["criteria"]["fromDate"]) : null;
                $date = isset($linkedDate) && $linkedDate > $fromDate ? $linkedDate : $fromDate;
            }

            $key = $date < $now ? 'archived' : 'ongoing';

            // We do not keep the matchingOffers or matchingRequest where proposals are private
            // TO DO : The api should return the array without all these
            $proposal = $this->cleanPrivateMatchings($proposal, 'Offers');
            $proposal = $this->cleanPrivateMatchings($proposal, 'Requests');

            // proposal is an outward
            if ($proposal["type"] === Proposal::TYPE_OUTWARD && !is_null($proposal["proposalLinked"])) {
                $proposalsSanitized[$key][$proposal["id"]] = [
                    'outward' => $proposal,
                    'return' => $proposal["proposalLinked"]
                ];
            // proposal is a return
            } elseif ($proposal["type"] === Proposal::TYPE_RETURN && !is_null($proposal["proposalLinked"])) {
                $proposalsSanitized[$key][$proposal["id"]] = [
                    'outward' => $proposal["proposalLinked"],
                    'return' => $proposal
                ];
            // proposal is one way
            } else {
                $proposalsSanitized[$key][$proposal["id"]] = [
                    'outward' => $proposal
                ];
            }
        }

        return $proposalsSanitized;
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
}
