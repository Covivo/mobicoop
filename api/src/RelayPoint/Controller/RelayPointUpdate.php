<?php
 /**
    * Copyright (c) 2019, MOBICOOP. All rights reserved.
    * This project is dual licensed under AGPL and proprietary licence.
    ***************************
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU Affero General Public License as
    * published by the Free Software Foundation, either version 3 of the
    * License, or (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU Affero General Public License for more details.
    *
    * You should have received a copy of the GNU Affero General Public License
    * along with this program. If not, see <gnu.org/licenses>.
    ***************************
    * Licence MOBICOOP described in the file
    * LICENSE
    **************************/
 
 namespace App\RelayPoint\Controller;

use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Service\RelayPointManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class RelayPointUpdate
{
    use TranslatorTrait;
    private $relayPointManager;
    private $request;
 
    public function __construct(RequestStack $requestStack, RelayPointManager $relayPointManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->relayPointManager = $relayPointManager;
    }
 
    /**
     * This method is invoked when a relay point update is asked.
     * It returns the altered relay point.
     *
     * @param RelayPoint $data
     * @return RelayPoint
     */
    public function __invoke(RelayPoint $data): RelayPoint
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad relay point id is provided"));
        }
        return $this->relayPointManager->updateRelayPoint($data);
    }
}
