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
 **************************/

namespace App\Carpool\Event;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Proposal;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when there is a major Proposal update and that there are asks.
 */
class ProposalMajorUpdatedEvent extends Event
{
    public const NAME = 'carpool_proposal_major_updated';

    private $old;
    private $new;
    /**
     * @var Ask[]
     */
    private $asks;

    public function __construct(Proposal $old, Proposal $new, array $asks)
    {
        $this->old = $old;
        $this->new = $new;
        $this->asks = $asks;
    }

    /**
     * @return Proposal
     */
    public function getOldProposal(): Proposal
    {
        return $this->old;
    }

    /**
     * @return Proposal
     */
    public function getNewProposal(): Proposal
    {
        return $this->new;
    }

    /**
     * @return Ask[]
     */
    public function getAsks(): array
    {
        return $this->asks;
    }
}
