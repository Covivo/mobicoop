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

namespace App\Communication\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Communication\Entity\Contact;
use App\Communication\Service\ContactManager;
use Symfony\Component\Security\Core\Security;

/**
 * Post a Message.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class ContactDataPersister implements ContextAwareDataPersisterInterface
{
    private $contactManager;
    private $security;

    public function __construct(ContactManager $contactManager, Security $security)
    {
        $this->contactManager = $contactManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Contact && isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('No Contact provided'));
        }

        return $this->contactManager->sendContactMail($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
