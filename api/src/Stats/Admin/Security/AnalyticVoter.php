<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Stats\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Stats\Admin\Resource\Analytic;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AnalyticVoter extends Voter
{
    public const ANALYTIC_READ = 'analytic_read';
    public const ANALYTIC_LIST = 'analytic_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ANALYTIC_READ,
            self::ANALYTIC_LIST,
        ])) {
            return false;
        }

        // only vote on Analytic objects inside this voter
        if (!in_array($attribute, [
            self::ANALYTIC_READ,
            self::ANALYTIC_LIST,
        ]) && !($subject instanceof Paginator)
                && !($subject instanceof Analytic)
            ) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ANALYTIC_READ:
                return $this->canReadAnalytic($subject);

            case self::ANALYTIC_LIST:
                return $this->canListAnalytic();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadAnalytic(Analytic $analytic)
    {
        return $this->authManager->isAuthorized(self::ANALYTIC_READ, ['analytic' => $analytic]);
    }

    private function canListAnalytic()
    {
        return $this->authManager->isAuthorized(self::ANALYTIC_LIST);
    }
}
