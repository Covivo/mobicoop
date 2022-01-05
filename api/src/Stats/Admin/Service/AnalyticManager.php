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

class AnalyticManager
{
    public const DOMAIN_USER = 'user';
    public const DOMAIN_AD = 'ad';
    public const DOMAIN_COMMUNITY = 'community';
    public const DOMAIN_SOLIDARY = 'solidary';
    public const DOMAIN_MISC = 'misc';

    public const DOMAINS = [
        self::DOMAIN_USER,
        self::DOMAIN_AD,
        self::DOMAIN_COMMUNITY,
        self::DOMAIN_SOLIDARY,
        self::DOMAIN_MISC,
    ];

    public function __construct()
    {
    }

    public function getAnalytics(): array
    {
        return [$this->getAnalytic(1)];
    }

    public function getAnalytic(int $id): Analytic
    {
        $analytic = new Analytic();
        $analytic->setId($id);
        $analytic->setDomain(self::DOMAIN_USER);
        $analytic->setValueType(Analytic::VALUE_TYPE_SCALAR);
        $analytic->setValue(12);

        return $analytic;
    }
}
