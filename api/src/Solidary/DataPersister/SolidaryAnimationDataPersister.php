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

namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Action\Exception\ActionException;
use App\App\Entity\App;
use App\Solidary\Entity\SolidaryAnimation;
use App\Solidary\Service\SolidaryAnimationManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class SolidaryAnimationDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryAnimationManager;
    private $security;

    public function __construct(SolidaryAnimationManager $solidaryAnimationManager, Security $security)
    {
        $this->solidaryAnimationManager = $solidaryAnimationManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryAnimation;
    }

    public function persist($data, array $context = [])
    {
        if (!($data instanceof SolidaryAnimation)) {
            throw new ActionException(ActionException::INVALID_DATA_PROVIDED);
        }

        // @var SolidaryAnimation $data

        // We set the correct author
        if ($this->security->getUser() instanceof App) {
            $data->setAuthor($data->getUser());
        } else {
            $data->setAuthor($this->security->getUser());
        }

        if (isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name']) {
            $data = $this->solidaryAnimationManager->treatSolidaryAnimation($data);
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
