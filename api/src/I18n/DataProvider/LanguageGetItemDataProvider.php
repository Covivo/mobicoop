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

namespace App\I18n\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\I18n\Service\LanguageManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Item data provider for Community
 * We use this provider to add on a communtiy the last 3 members, and the add so we can have only 1 request in front
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 *
 */
final class LanguageGetItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $languageManager;
    private $security;

    public function __construct(RequestStack $requestStack, LanguageManager $languageManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->languageManager = $languageManager;

        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Language::class === $resourceClass && $operationName === "get";
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Language
    {
        return $this->languageManager->getLanguage($id);
    }
}
