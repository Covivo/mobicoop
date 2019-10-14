<?php
 /**
    * Copyright (c) 2019, MOBICOOP. All rights reserved.
    * This project is dual licensed under AGPL and proprietary licence.
    ***************************
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU Affero General Public License as
    * published by the Free Software Foundation, either version 3 of the
    * License, or (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU Affero General Public License for more details.
    *
    * You should have received a copy of the GNU Affero General Public License
    * along with this program. If not, see <gnu.org/licenses>.
    ***************************
    * Licence MOBICOOP described in the file
    * LICENSE
    **************************/
 
 namespace App\Community\Controller;

use App\Community\Entity\Community;
use App\Community\Service\CommunityManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunityUpdate
{
    use TranslatorTrait;
    private $communityManager; 
    private $request;
 
    public function __construct(RequestStack $requestStack, CommunityManager $communityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->communityManager = $communityManager;
    }
 
    /**
     * This method is invoked when a community update is asked.
     * It returns the altered community.
     *
     * @param Community $data
     * @return Community
     */
    public function __invoke(Community $data): Community
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad community id is provided"));
        }
        return $this->communityManager->updateCommunity($data);
    }
}
