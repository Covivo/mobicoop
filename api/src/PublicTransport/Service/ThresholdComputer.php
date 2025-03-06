<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\PublicTransport\Service;

use App\Action\Repository\LogRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ThresholdComputer
{
    private $_provider;
    private $_threshold;
    private $_granularity;
    private $_logRepository;

    public function __construct(LogRepository $logRepository, string $_provider, int $threshold, string $granularity)
    {
        $this->_provider = $_provider;
        $this->_threshold = $threshold;
        $this->_granularity = $granularity;
        $this->_logRepository = $logRepository;
    }

    public function isReached(): bool
    {
        if (0 == $this->_threshold) {
            return false;
        }

        $logs = $this->_getLogForProvider();

        if (count($logs) >= $this->_threshold) {
            return true;
        }

        return false;
    }

    private function _getLogForProvider(): array
    {
        return $this->_logRepository->findByPtProviderAndDate($this->_provider, $this->_computeStartOfPeriod());
    }

    private function _computeStartOfPeriod(): \DateTime
    {
        $date = new \DateTime();

        switch ($this->_granularity) {
            case 'year':
                $date->modify('first day of january this year midnight');

                break;

            case 'month':
                $date->modify('first day of this month midnight');

                break;

            case 'week':
                $date->modify('monday this week midnight');

                break;

            case 'day':
                $date->modify('today midnight');

                break;

            case 'hour':
                $date->modify('this hour');

                break;
        }

        return $date;
    }
}
