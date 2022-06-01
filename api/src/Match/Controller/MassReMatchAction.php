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
 */

namespace App\Match\Controller;

use App\Match\Entity\Mass;
use App\Match\Repository\MassMatchingRepository;
use App\Match\Service\MassImportManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

final class MassReMatchAction
{
    use TranslatorTrait;
    private $massImportManager;
    private $request;
    private $massMatchingRepository;

    public function __construct(RequestStack $requestStack, MassImportManager $massImportManager, MassMatchingRepository $massMatchingRepository)
    {
        $this->massImportManager = $massImportManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->massMatchingRepository = $massMatchingRepository;
    }

    public function __invoke(Mass $data): Mass
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad Mass id is provided'));
        }
        $statusAuthorized = [
            Mass::STATUS_ANALYZED,
            Mass::STATUS_MATCHED,
        ];

        if (in_array($data->getStatus(), $statusAuthorized)) {
            // Rollback the Mass status to only valid
            $this->massImportManager->updateStatusMass($data, Mass::STATUS_ANALYZED);

            // delete the previous matchings
            $this->massMatchingRepository->deleteMatchingsOfAMass($data->getId());

            $maxDetourDurationPercent = 40;
            $maxDetourDistancePercent = 40;
            $minOverlapRatio = 0;
            $maxSuperiorDistanceRatio = 1000;
            $bearingCheck = true;
            $bearingRange = 10;
            if ($this->request->get('maxDetourDurationPercent')) {
                $maxDetourDurationPercent = $this->request->get('maxDetourDurationPercent');
            }
            if ($this->request->get('maxDetourDistancePercent')) {
                $maxDetourDistancePercent = $this->request->get('maxDetourDistancePercent');
            }
            if ($this->request->get('minOverlapRatio')) {
                $minOverlapRatio = $this->request->get('minOverlapRatio');
            }
            if ($this->request->get('maxSuperiorDistanceRatio')) {
                $maxSuperiorDistanceRatio = $this->request->get('maxSuperiorDistanceRatio');
            }
            if ($this->request->get('bearingCheck')) {
                $bearingCheck = $this->request->get('bearingCheck');
            }
            if ($this->request->get('bearingRange')) {
                $bearingRange = $this->request->get('bearingRange');
            }
            $this->massImportManager->matchMass($data, $maxDetourDurationPercent, $maxDetourDistancePercent, $minOverlapRatio, $maxSuperiorDistanceRatio, $bearingCheck, $bearingRange);
        }

        return $data;
    }
}
