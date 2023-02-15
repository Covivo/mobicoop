<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

use App\User\Event\TooLongInactivityFirstWarningEvent;
use App\User\Event\TooLongInactivityLastWarningEvent;
use App\User\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserAutoDeleter
{
    public const FIRST_WARNING_RATIO = 0.25;
    public const LAST_WARNING_RATIO = 0.042;
    public const NB_DAYS_IN_A_MONTH = 30;

    private $_userRepository;
    private $_eventDispatcher;
    private $_active;
    private $_period;

    public function __construct(UserRepository $userRepository, EventDispatcherInterface $eventDispatcher, bool $active, int $period)
    {
        $this->_active = $active;
        $this->_period = $period;
        $this->_userRepository = $userRepository;
        $this->_eventDispatcher = $eventDispatcher;
    }

    public function autoDelete()
    {
        if (!$this->_active) {
            return;
        }
        $this->_sendWarnings();
        $this->_deleteAccounts();
    }

    private function _deleteAccounts()
    {
    }

    private function _sendWarnings()
    {
        $this->_sendFirstWarnings();
        $this->_sendLastWarnings();
    }

    private function _computeFirstWarningLastConnexionDate()
    {
        echo '_computeFirstWarningLastConnexionDate'.PHP_EOL;
        $offsetInMonths = $this->_computeFirstWarningTimeOffsetInMonth();
        $lastConnexionDate = new \DateTime('now');
        var_dump($lastConnexionDate);
        $lastConnexionDate->modify('+ '.$offsetInMonths.' month');
        $lastConnexionDate->modify('- '.$this->_period.' month');
        var_dump($lastConnexionDate);

        return $lastConnexionDate;
    }

    private function _computeLastWarningLastConnexionDate()
    {
        echo '_computeLastWarningLastConnexionDate'.PHP_EOL;
        $offsetInDays = $this->_computeLastWarningTimeOffsetInMonth();
        $lastConnexionDate = new \DateTime('now');
        var_dump($lastConnexionDate);
        $lastConnexionDate->modify('+ '.$offsetInDays.' day');
        $lastConnexionDate->modify('- '.$this->_period.' month');
        var_dump($lastConnexionDate);

        return $lastConnexionDate;
    }

    private function _sendFirstWarnings()
    {
        $inactiveUsers = $this->_getInactiveUsers($this->_computeFirstWarningLastConnexionDate());
        echo count($inactiveUsers).PHP_EOL;
        foreach ($inactiveUsers as $inactiveUser) {
            $event = new TooLongInactivityFirstWarningEvent($inactiveUser);
            $this->_eventDispatcher->dispatch(TooLongInactivityFirstWarningEvent::NAME, $event);
        }
    }

    private function _sendLastWarnings()
    {
        $inactiveUsers = $this->_getInactiveUsers($this->_computeLastWarningLastConnexionDate());
        echo count($inactiveUsers).PHP_EOL;
        foreach ($inactiveUsers as $inactiveUser) {
            $event = new TooLongInactivityLastWarningEvent($inactiveUser);
            $this->_eventDispatcher->dispatch(TooLongInactivityLastWarningEvent::NAME, $event);
        }
    }

    private function _computeFirstWarningTimeOffsetInMonth(): int
    {
        return floor($this->_period * self::FIRST_WARNING_RATIO);
    }

    private function _computeLastWarningTimeOffsetInMonth(): int
    {
        return floor($this->_period * self::LAST_WARNING_RATIO * self::NB_DAYS_IN_A_MONTH);
    }

    private function _getInactiveUsers(\DateTime $lastConnexionDate): array
    {
        return $this->_userRepository->findByLastActivityDate($lastConnexionDate);
    }
}
