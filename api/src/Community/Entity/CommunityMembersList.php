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

namespace App\Community\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Community Members List
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CommunityMembersList
{
    /**
     * @var CommunityMember[]
     * @Groups({"readCommunityMember"})
    */
    private $members;

    /**
     * @var int
     * @Groups({"readCommunityMember"})
    */
    private $totalMembers;

    public function __construct(array $members = null, int $totalMembers = null)
    {
        $this->members = [];
        if (!is_null($members)) {
            $this->members = $members;
        }

        $this->totalMembers = 0;
        if (!is_null($totalMembers)) {
            $this->totalMembers = $totalMembers;
        }
    }

    public function getMembers(): ?array
    {
        return $this->members;
    }

    public function setMembers(?array $members): self
    {
        $this->members = $members;

        return $this;
    }

    public function getTotalMembers(): ?string
    {
        return $this->totalMembers;
    }

    public function setTotalMembers(?string $totalMembers): self
    {
        $this->totalMembers = $totalMembers;

        return $this;
    }
}
