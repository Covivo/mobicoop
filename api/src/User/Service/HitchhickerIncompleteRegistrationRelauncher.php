<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\User\Service;

use App\User\Entity\IdentityProof;
use App\User\Event\HitchhickerIncompleteRegistrationFirstRelaunchEvent;
use App\User\Event\HitchhickerIncompleteRegistrationSecondRelaunchEvent;
use App\User\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class HitchhickerIncompleteRegistrationRelauncher
{
    public const NB_DAYS_FOR_FIRST_RELAUNCH = 7;
    public const NB_DAYS_FOR_SECOND_RELAUNCH = 30;
    public const STATUS_TO_RELAUNCH = [IdentityProof::STATUS_CANCELED, IdentityProof::STATUS_NONE, IdentityProof::STATUS_REFUSED];

    private $_userRepository;
    private $_eventDispatcher;
    private $_active;
    private $_logger;

    public function __construct(
        LoggerInterface $logger,
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher,
        bool $active
    ) {
        $this->_logger = $logger;
        $this->_active = $active;
        $this->_userRepository = $userRepository;
        $this->_eventDispatcher = $eventDispatcher;
    }

    public function relaunch()
    {
        if (!$this->_active) {
            return;
        }
        $this->_sendFirst();
        $this->_sendSecond();
    }

    private function _sendFirst()
    {
        $this->_logger->info('*********************************************************');
        $this->_logger->info('Launching first hitchhicker incomplete registration relaunch for '.self::NB_DAYS_FOR_FIRST_RELAUNCH.' days');
        $this->_logger->info('Registration date : '.$this->_computeRegistrationDate(self::NB_DAYS_FOR_FIRST_RELAUNCH)->format('d/m/Y'));

        $users = $this->_findUsers(self::NB_DAYS_FOR_FIRST_RELAUNCH);
        foreach ($users as $user) {
            if (in_array($user->getIdentityStatus(), self::STATUS_TO_RELAUNCH)) {
                $this->_logger->info('Sending first hitchhicker incomplete registration relaunch for user '.$user->getId());
                $event = new HitchhickerIncompleteRegistrationFirstRelaunchEvent($user);
                $this->_eventDispatcher->dispatch(HitchhickerIncompleteRegistrationFirstRelaunchEvent::NAME, $event);
            }
        }
    }

    private function _sendSecond()
    {
        $this->_logger->info('*********************************************************');
        $this->_logger->info('Launching second hitchhicker incomplete registration relaunch for '.self::NB_DAYS_FOR_SECOND_RELAUNCH.' days');
        $this->_logger->info('Registration date : '.$this->_computeRegistrationDate(self::NB_DAYS_FOR_SECOND_RELAUNCH)->format('d/m/Y'));

        $users = $this->_findUsers(self::NB_DAYS_FOR_SECOND_RELAUNCH);
        foreach ($users as $user) {
            if (in_array($user->getIdentityStatus(), self::STATUS_TO_RELAUNCH)) {
                $this->_logger->info('Sending second hitchhicker incomplete registration relaunch for user '.$user->getId());
                $event = new HitchhickerIncompleteRegistrationSecondRelaunchEvent($user);
                $this->_eventDispatcher->dispatch(HitchhickerIncompleteRegistrationSecondRelaunchEvent::NAME, $event);
            }
        }
    }

    private function _findUsers(int $nb_days): array
    {
        return $this->_userRepository->findUsersHitchhickersIncompleteRegistrationOn($this->_computeRegistrationDate($nb_days));
    }

    private function _computeRegistrationDate(int $nb_days): \DateTime
    {
        $registrationDate = new \DateTime('now');
        $registrationDate->modify('- '.$nb_days.' day');

        return $registrationDate;
    }
}
