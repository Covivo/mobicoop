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

declare(strict_types=1);

namespace App\Geography\Service\Point;

use App\Geography\Repository\AddressRepository;
use App\User\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserPointProvider implements PointProvider
{
    protected $addressRepository;
    protected $translator;
    protected $user;
    protected $maxResults;

    public function __construct(AddressRepository $addressRepository, TranslatorInterface $translator)
    {
        $this->addressRepository = $addressRepository;
        $this->translator = $translator;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function search(string $search): array
    {
        return $this->addressesToPoints(
            $this->addressRepository->findByName($this->translator->trans($search), $this->user->getId())
        );
    }

    private function addressesToPoints(array $addresses): array
    {
        $points = [];
        foreach ($addresses as $address) {
            $point = AddressAdapter::addressToPoint($address);
            $point->setId($address->getId());
            $point->setName($this->translator->trans($address->getName()));
            $point->setType('user');
            $points[] = $point;
            if ($this->maxResults > 0 && count($points) == $this->maxResults) {
                break;
            }
        }

        return $points;
    }
}
