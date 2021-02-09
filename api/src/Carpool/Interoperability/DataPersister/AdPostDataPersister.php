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

namespace App\Carpool\Interoperability\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Carpool\Interoperability\Ressource\Ad;
use App\Carpool\Service\AdManager;

final class AdPostDataPersister implements ContextAwareDataPersisterInterface
{
    private $adManager;

    public function __construct(AdManager $adManager)
    {
        $this->adManager = $adManager;
    }
  
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Ad && isset($context['collection_operation_name']) && $context['collection_operation_name'] === 'interop_post';
    }

    public function persist($data, array $context = [])
    {
        echo "yo!";
        die;
        // return $this->adManager->createAd($data, true, false);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
