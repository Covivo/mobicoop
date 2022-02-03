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

namespace App\User\Admin\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\IdentityProof;
use App\User\Service\IdentityProofManager;
use Symfony\Component\HttpFoundation\RequestStack;

final class IdentityProofDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $identityProofManager;

    public function __construct(RequestStack $requestStack, IdentityProofManager $identityProofManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->identityProofManager = $identityProofManager;
    }

    public function supports($data, array $context = []): bool
    {
        if ($data instanceof IdentityProof) {
            switch ($context) {
                case isset($context['item_operation_name']):
                    return 'ADMIN_patch' == $context['item_operation_name'];

                    break;

                default:
                    return false;

                    break;
            }
        } else {
            return false;
        }
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['item_operation_name']) && 'ADMIN_patch' == $context['item_operation_name']) {
            $data = $this->identityProofManager->patchIdentityProof($this->request->get('id'), json_decode($this->request->getContent(), true));
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
    }
}
