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
 **************************/

namespace App\User\Event;

use App\User\Entity\IdentityProof;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when an identity proof is moderated.
 */
class IdentityProofModeratedEvent extends Event
{
    public const NAME = 'identity_proof_moderated';
    public const NAME_ACCEPTED = 'identity_proof_moderated_accepted';
    public const NAME_REJECTED = 'identity_proof_moderated_rejected';

    protected $identityProof;

    public function __construct(IdentityProof $identityProof)
    {
        $this->identityProof = $identityProof;
    }

    public function getIdentityProof()
    {
        return $this->identityProof;
    }
}
