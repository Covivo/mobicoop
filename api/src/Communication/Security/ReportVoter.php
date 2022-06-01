<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Communication\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Communication\Ressource\Report;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReportVoter extends Voter
{
    public const REPORT_CREATE = 'report_create';
    public const REPORT_UPDATE = 'report_update';
    public const REPORT_READ = 'report_read';
    public const REPORT_DELETE = 'report_delete';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::REPORT_CREATE,
            self::REPORT_UPDATE,
            self::REPORT_READ,
            self::REPORT_DELETE,
        ])) {
            return false;
        }

        // only vote on Message objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::REPORT_CREATE,
            self::REPORT_UPDATE,
            self::REPORT_READ,
            self::REPORT_DELETE,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Report)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::REPORT_CREATE:
                return $this->canCreateReport($subject);

            case self::REPORT_UPDATE:
                return $this->canUpdateReport($subject);

            case self::REPORT_READ:
                return $this->canReadReport($subject);

            case self::REPORT_DELETE:
                return $this->canDeleteReport($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateReport(Report $report)
    {
        return $this->authManager->isAuthorized(self::REPORT_CREATE, ['report' => $report]);
    }

    private function canReadReport(Report $report)
    {
        return $this->authManager->isAuthorized(self::REPORT_READ, ['report' => $report]);
    }

    private function canUpdateReport(Report $report)
    {
        return $this->authManager->isAuthorized(self::REPORT_UPDATE, ['report' => $report]);
    }

    private function canDeleteReport(Report $report)
    {
        return $this->authManager->isAuthorized(self::REPORT_DELETE, ['report' => $report]);
    }
}
