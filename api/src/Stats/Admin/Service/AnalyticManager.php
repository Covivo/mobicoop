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
        1 => DataManager::DATA_NAME_REGISTRATIONS_LIST,
        2 => DataManager::DATA_NAME_VALIDATED_USERS,
        3 => DataManager::DATA_NAME_VALIDATED_USERS_LIST,
    ];

    private $dataManager;

    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;
    }

    public function getAnalytics(): array
    {
        $analytics = [];
        foreach (self::IDS as $id => $label) {
            $analytics[] = $this->getAnalytic($id);
        }

        return $analytics;
    }

    public function getAnalytic(int $id, ?array $filter = []): Analytic
    {
        if (!in_array($id, array_keys(self::IDS))) {
            throw new Exception('Unknown Id');
        }

        $analytic = new Analytic();
        $analytic->setId($id);

        $this->dataManager->setDataName(self::IDS[$id]);
        $data = $this->dataManager->getData();
        $analytic->setValue([
            'total' => $data['total'],
            'data' => $data['data'],
        ]);

        return $analytic;
    }
}
