<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Match\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Match\Service\MassImportManager;
use App\Match\Entity\Mass;
use Symfony\Component\HttpFoundation\RequestStack;

final class MassMatchAction
{
    private $massImportManager;
    private $request;

    public function __construct(RequestStack $requestStack, MassImportManager $massImportManager)
    {
        $this->massImportManager = $massImportManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(Mass $data): Mass
    {
        if ($data->getStatus() == Mass::STATUS_ANALYZED) {
            $maxDetourDurationPercent = 40;
            $maxDetourDistancePercent = 40;
            $minOverlapRatio = 0;
            $maxSuperiorDistanceRatio = 1000;
            $doubleCheck = true;
            if ($this->request->get("maxDetourDurationPercent")) $maxDetourDurationPercent = $this->request->get("maxDetourDurationPercent");
            if ($this->request->get("maxDetourDistancePercent")) $maxDetourDistancePercent = $this->request->get("maxDetourDistancePercent");
            if ($this->request->get("minOverlapRatio")) $minOverlapRatio = $this->request->get("minOverlapRatio");
            if ($this->request->get("maxSuperiorDistanceRatio")) $maxSuperiorDistanceRatio = $this->request->get("maxSuperiorDistanceRatio");
            if ($this->request->get("doubleCheck")) $doubleCheck = $this->request->get("doubleCheck");
            $result = $this->massImportManager->matchMass($data,$maxDetourDurationPercent,$maxDetourDistancePercent,$minOverlapRatio,$maxSuperiorDistanceRatio,$doubleCheck);
            print_r($result);exit;
        }
        return $data;
    }
}
