<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Gamification\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\BadgesBoard;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\Reward;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\RewardStep;

/**
 * Gamification management service.
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GamificationManager
{
    private $dataProvider;

    /**
     * @param DataProvider $dataProvider
     * @throws \ReflectionException
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Tags all given rewardSteps as notified
     * @var array $rewardStepsIds   Array of RewardSteps Ids
     * @return null
     */
    public function tagRewardStepsAsNotified(array $rewardStepsIds)
    {
        $this->dataProvider->setClass(RewardStep::class);
        foreach ($rewardStepsIds as $rewardStepsId) {
            $response = $this->dataProvider->getSpecialItem($rewardStepsId, "tagAsNotified");
        }
        return null;
    }

    /**
     * Tags a given reward as notified
     * @var int $rewardId   id of the reward
     * @return null
     */
    public function tagRewardAsNotified(int $rewardId)
    {
        $this->dataProvider->setClass(Reward::class);
        return  $this->dataProvider->getSpecialItem($rewardId, "tagAsNotified");
    }

    /**
     * Get the Badges Board of a User
     * @return null
     */
    public function badgesBoard()
    {
        $this->dataProvider->setClass(BadgesBoard::class);
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getCollection();
        return $response->getValue();
    }
}
