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
 */

namespace App\Validator\Phone\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Validator\Phone\PhoneValidator;
use App\Validator\Phone\Resource\PhoneValidation;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class PhoneValidationDataPersister implements ContextAwareDataPersisterInterface
{
    private $phoneValidator;
    private $translator;

    public function __construct(PhoneValidator $phoneValidator, TranslatorInterface $translator)
    {
        $this->phoneValidator = $phoneValidator;
        $this->translator = $translator;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof PhoneValidation && isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        $data->setValid($this->phoneValidator->validate($data->getPhoneNumber()));

        if (!is_null($this->phoneValidator->message)) {
            $data->setMessage($this->translator->trans($this->phoneValidator->message));
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
