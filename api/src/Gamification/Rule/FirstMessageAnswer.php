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

namespace App\Gamification\Rule;

use App\Gamification\Interfaces\GamificationRuleInterface;
use App\Communication\Repository\MessageRepository;

/**
 *  Check that the requester is the author of the related Ad
 */
class FirstMessageAnswer implements GamificationRuleInterface
{
    private $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * First Message Answer rule
     *
     * @param  $requester
     * @param  $log
     * @param  $sequenceItem
     * @return bool
     */
    public function execute($requester, $log, $sequenceItem)
    {
        $answers = $this->messageRepository->findAnswers($log->getUser());
        if (count($answers)===1) {
            return true;
        }
        return false;
    }
}
