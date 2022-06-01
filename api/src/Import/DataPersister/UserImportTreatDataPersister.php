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
use App\Import\Entity\UserImport;
use App\Import\Service\ImportManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data persister for User import treatment.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class UserImportTreatDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $request;
    private $importManager;

    public function __construct(Security $security, ImportManager $importManager, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
        $this->importManager = $importManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserImport && isset($context['collection_operation_name']) && 'treat' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        // @var UserImport $data
        return $this->importManager->treatUserImport($this->request->get('origin'), null, $this->request->get('lowestId'));
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
