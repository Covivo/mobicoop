<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Action\Service;

use App\Action\Entity\Animation;
use App\Action\Service\ActionManager;
use Doctrine\ORM\EntityManagerInterface;

class AnimationManager
{
    private $entityManager;
    private $actionManager;

    public function __construct(EntityManagerInterface $entityManager, ActionManager $actionManager)
    {
        $this->entityManager = $entityManager;
        $this->actionManager = $actionManager;
    }

    public function treatAnimation(Animation $animation)
    {
        $this->actionManager->handleAction($animation->getName(), $animation);
    }
}
