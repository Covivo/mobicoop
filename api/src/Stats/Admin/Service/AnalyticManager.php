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

namespace App\Stats\Admin\Service;

use App\Stats\Admin\Resource\Analytic;
use Exception;

class AnalyticManager
{
    public const IDS = [
        1 => 'getUsersAnalytics',
        2 => 'getSolidaryUsersAnalytics',
    ];

    private $dataManager;
    private $analytic;

    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;
    }

    public function getAnalytics(): array
    {
        $analytics = [];
        foreach (self::IDS as $id) {
            $analytics[] = $this->getAnalytic($id);
        }

        return $analytics;
    }

    public function getAnalytic(int $id, ?array $filter = []): Analytic
    {
        $this->analytic = new Analytic();
        $this->analytic->setId($id);

        if (!in_array($id, array_keys(self::IDS))) {
            throw new Exception('Unknown Id');
        }

        if (is_callable([$this, self::IDS[$id]])) {
            $this->{self::IDS[$id]}($id);
        } else {
            $this->getGenericAnalytics($id);
        }

        return $this->analytic;
    }

    public function getGenericAnalytics(int $id, ?array $filter = [])
    {
        $this->dataManager->setDataName(self::IDS[$id]);
        $data = $this->dataManager->getData();
        $this->analytic->setValue([
            'total' => $data['total'],
            'data' => $data['data'],
        ]);
    }

    public function getUsersAnalytics(int $id, ?array $filter = [])
    {
        $analyticValue = ['data' => []];

        $this->dataManager->setDataName(DataManager::DATA_NAME_VALIDATED_USERS_DETAILED);
        $validatedUsers = $this->dataManager->getData();
        $analyticValue['total'] = $validatedUsers['total'];
        $analyticValue['data'][] = $validatedUsers['data'];

        $this->dataManager->setDataName(DataManager::DATA_NAME_NOT_VALIDATED_USERS_DETAILED);
        $notValidatedUsers = $this->dataManager->getData();
        $analyticValue['data'][] = $notValidatedUsers['data'];

        $this->analytic->setValue($this->normalizeResults($analyticValue));
    }

    private function normalizeResults($analyticValue)
    {
        foreach ($analyticValue['data'] as $key => $currentData) {
            if (!isset($analyticValue['data'][$key + 1])) {
                break;
            }

            $firstDateCurrent = new \DateTime($currentData[0]['key']);
            $lastDateCurrent = new \DateTime($currentData[count($currentData) - 1]['key']);
            $firstDateNextData = new \DateTime($analyticValue['data'][$key + 1][0]['key']);
            $lastDateNextData = new \DateTime($analyticValue['data'][$key + 1][count($analyticValue['data']) - 1]['key']);

            if ($firstDateCurrent > $firstDateNextData) {
                $dataToAdd = $analyticValue['data'][$key + 1][0];
                $dataToAdd['dataName'] = $currentData[0]['dataName'];
                $dataToAdd['value'] = '-';
                array_unshift($analyticValue['data'][$key], $dataToAdd);
            }
            if ($lastDateCurrent < $lastDateNextData) {
                $dataToAdd = $analyticValue['data'][$key + 1][count($analyticValue['data']) - 1];
                $dataToAdd['dataName'] = $currentData[0]['dataName'];
                $dataToAdd['value'] = '-';
                array_push($analyticValue['data'][$key], $dataToAdd);
            }
        }

        return $analyticValue;
    }
}
