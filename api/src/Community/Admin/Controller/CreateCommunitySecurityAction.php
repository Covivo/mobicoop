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

namespace App\Community\Admin\Controller;

use App\Community\Admin\Service\CommunitySecurityManager;
use App\Community\Entity\CommunitySecurity;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class CreateCommunitySecurityAction
{
    private $communitySecurityManager;

    public function __construct(
        CommunitySecurityManager $communitySecurityManager
    ) {
        $this->communitySecurityManager = $communitySecurityManager;
    }

    public function __invoke(Request $request): CommunitySecurity
    {
        if (!$request->files->get('file')) {
            throw new Exception('File is mandatory');
        }
        if (!$request->get('communityId') || !is_numeric($request->get('communityId'))) {
            throw new Exception('communityId is mandatory');
        }

        return $this->communitySecurityManager->createSecurity($request->files->get('file'), (int) $request->get('communityId'));
    }
}
