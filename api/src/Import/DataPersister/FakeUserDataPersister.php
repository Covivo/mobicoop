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

namespace App\Import\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Import\Ressource\FakeUser;
use App\Import\Service\FakeManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data persister for Fake User creation.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class FakeUserDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $request;
    private $fakeManager;

    public function __construct(Security $security, FakeManager $fakeManager, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
        $this->fakeManager = $fakeManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof FakeUser && isset($context['collection_operation_name']) && 'create' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        $this->fakeManager->fakeUsers(
            $this->request->get('number_users'),
            $this->request->get('min_lat'),
            $this->request->get('min_lon'),
            $this->request->get('max_lat'),
            $this->request->get('max_lon'),
            $this->request->get('split', 1),
            $this->request->get('truncate', false)
        );

        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
