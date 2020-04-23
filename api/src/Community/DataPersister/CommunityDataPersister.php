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

namespace App\Community\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Community\Entity\Community;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use App\Community\Service\CommunityManager;

final class CommunityDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $security;
    private $communityManager;

    public function __construct(RequestStack $requestStack, Security $security, CommunityManager $communityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->communityManager = $communityManager;
    }

    public function supports($data, array $context = []): bool
    {
        //We post a community, we add the role community_manager to the author
        return $data instanceof Community && isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad community id is provided"));
        }
        
        $data = $this->communityManager->save($data);
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
