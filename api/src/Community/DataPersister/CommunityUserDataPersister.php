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

namespace App\Community\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data persister for Community User
 * Use for check if user can join a community before save.
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 */
final class CommunityUserDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $communityManager;

    public function __construct(RequestStack $requestStack, CommunityManager $communityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->communityManager = $communityManager;
    }

    public function supports($data, array $context = []): bool
    {
        // We want to join a community, check if user have the fight before save
        return $data instanceof CommunityUser;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad community user id is provided'));
        }
        if (isset($context['item_operation_name']) && 'put' == $context['item_operation_name']) {
            // only for validation or update availabilities
            $data = $this->communityManager->updateCommunityUser($data);
        } elseif (isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name']) {
            if (!$this->communityManager->canJoin($data, true)) {
                throw new \InvalidArgumentException("the user don't have a valid domain to join this community");
            }
            $data = $this->communityManager->saveCommunityUser($data);
        } elseif (isset($context['collection_operation_name']) && 'add' == $context['collection_operation_name']) {
            if (!$this->communityManager->canJoin($data, false)) {
                throw new \InvalidArgumentException("You don't have rights on that secured community");
            }
            $data = $this->communityManager->saveCommunityUser($data);
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
        return $this->communityManager->deleteCommunityUser($data, json_decode($this->request->getContent(), true));
    }
}
