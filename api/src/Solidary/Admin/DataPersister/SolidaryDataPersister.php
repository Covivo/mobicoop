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

namespace App\Solidary\Admin\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Admin\Service\SolidaryManager;
use App\Solidary\Entity\Solidary;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data persister for Solidary records in administration context
 */
final class SolidaryDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $solidaryManager;

    public function __construct(RequestStack $requestStack, SolidaryManager $solidaryManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->solidaryManager = $solidaryManager;
    }

    public function supports($data, array $context = []): bool
    {
        if ($data instanceof Solidary) {
            switch ($context) {
                case isset($context['collection_operation_name']):
                    return $context['collection_operation_name'] == 'ADMIN_post';
                    break;
                case isset($context['item_operation_name']):
                    return $context['item_operation_name'] == 'ADMIN_patch' || $context['item_operation_name'] == 'ADMIN_delete';
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
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'ADMIN_post') {
            // for a post operation, we also pass the fields to handle some of them manually for convenience
            $data = $this->solidaryManager->addSolidary($data, json_decode($this->request->getContent(), true));
        } elseif (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'ADMIN_patch') {
            // for a patch operation, we update only some fields, we pass them to the method for further checkings
            $data = $this->solidaryManager->patchSolidary($data, json_decode($this->request->getContent(), true));
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        if (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'ADMIN_delete') {
            return $this->solidaryManager->deleteSolidary($data);
        }
    }
}
