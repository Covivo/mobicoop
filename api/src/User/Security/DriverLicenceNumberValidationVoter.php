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

namespace App\User\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Validator\DriverLicenceNumber\Resource\DriverLicenceNumberValidation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DriverLicenceNumberValidationVoter extends Voter
{
    public const DRIVER_LICENCE_NUMBER_VALIDATION = 'driver_licence_number_validation';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::DRIVER_LICENCE_NUMBER_VALIDATION,
        ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::DRIVER_LICENCE_NUMBER_VALIDATION,
        ]) && !($subject instanceof Paginator) && !($subject instanceof DriverLicenceNumberValidation)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::DRIVER_LICENCE_NUMBER_VALIDATION:
                return $this->canValidateDriverLicenceNumber();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canValidateDriverLicenceNumber()
    {
        return $this->authManager->isAuthorized(self::DRIVER_LICENCE_NUMBER_VALIDATION);
    }
}
