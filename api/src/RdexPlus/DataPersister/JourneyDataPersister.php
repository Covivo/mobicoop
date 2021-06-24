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

namespace App\RdexPlus\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\RdexPlus\Resource\Journey;
use App\RdexPlus\Service\JourneyManager;

/**
 * RDEX+ : Journey data persister
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class JourneyDataPersister implements ContextAwareDataPersisterInterface
{
    private $journeyManager;

    public function __construct(JourneyManager $journeyManager)
    {
        $this->journeyManager = $journeyManager;
    }
  
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Journey && isset($context['collection_operation_name']) && $context['collection_operation_name'] === 'rdex_plus_journey_post';
    }

    public function persist($data, array $context = [])
    {
        return $this->journeyManager->createJourney($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
