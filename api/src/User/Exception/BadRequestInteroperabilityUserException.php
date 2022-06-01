<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\User\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\User\Interoperability\Ressource\User;

class BadRequestInteroperabilityUserException extends BadRequestHttpException
{
    public const UNAUTHORIZED = "You are not authorized to perform this operation";
    public const NO_USER_PROVIDED = "No user provided";
    public const INVALID_GENDER = "Gender must be a valid value : ".User::GENDER_FEMALE." (female), ".User::GENDER_MALE." (male), ".User::GENDER_OTHER." (other)";
    public const USER_ALREADY_EXISTS = "A user with this email address already exists in our database";
}
