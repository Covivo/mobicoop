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
use App\User\Entity\User;

/**
 * Data persister for Community
 * Use for add the role community_manager to the author before save
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */

final class CommunityDataPersister implements ContextAwareDataPersisterInterface
{
    private $communityManager;
    private $security;

    public function __construct(CommunityManager $communityManager, Security $security)
    {
        $this->communityManager = $communityManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        // We post a community, we add the role community_manager_public to the author
        return $data instanceof Community;
    }

    public function persist($data, array $context = [])
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad community is provided"));
        }
       
        if (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'put') {
            $data = $this->communityManager->updateCommunity($data);
        } elseif (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->communityManager->save($data);
        } elseif (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'join') {
            if (!($this->security->getUser() instanceof User)) {
                throw new \LogicException("Only a User can join a Community");
            }
            $data = $this->communityManager->joinCommunity($data, $this->security->getUser());
        } elseif (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'leave') {
            if (!($this->security->getUser() instanceof User)) {
                throw new \LogicException("Only a User can leave a Community");
            }
            $data = $this->communityManager->leaveCommunity($data, $this->security->getUser());
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
