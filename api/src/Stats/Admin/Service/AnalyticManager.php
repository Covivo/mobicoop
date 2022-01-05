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
        1,
        2,
        3,
        4,
        5,
    ];

    public function __construct()
    {
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
        if (!in_array($id, self::IDS)) {
            throw new Exception('Unknown Id');
        }

        $analytic = new Analytic();
        $analytic->setId($id);

        switch ($id) {
            case 1:
                $analytic->setValue([
                    'total' => 65765,
                    'dashboard' => [
                        2019 => [
                            'new_validated' => 150,
                            'new_unvalidated' => 93,
                        ],
                        2020 => [
                            'new_validated' => 140,
                            'new_unvalidated' => 84,
                        ],
                        2021 => [
                            'new_validated' => 188,
                            'new_unvalidated' => 64,
                        ],
                    ],
                ]);

                break;

            case 2:
                $analytic->setValue(['total' => 12744]);

                break;

            case 3:
                $analytic->setValue(['total' => 635]);

                break;

            case 4:
                $analytic->setValue(['total' => 45]);

                break;

            case 5:
                $analytic->setValue(['total' => 3745]);

                break;
        }

        return $analytic;
    }
}
