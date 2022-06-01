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

namespace App\Community\Controller;

use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use App\TranslatorTrait;
use Psr\Log\LoggerInterface;

/**
 * Controller for leaving a community.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class LeaveCommunityAction
{
    use TranslatorTrait;
    private $communityManager;
    private $logger;

    public function __construct(CommunityManager $communityManager, LoggerInterface $logger)
    {
        $this->communityManager = $communityManager;
        $this->logger = $logger;
    }

    public function __invoke(CommunityUser $data)
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad community user is provided'));
        }

        return $this->communityManager->unlinkCommunityJourneys($data);
    }
}
