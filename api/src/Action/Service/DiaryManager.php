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

use App\Action\Entity\Action;
use App\Action\Entity\Diary;
use App\Action\Entity\Animation;
use App\Action\Repository\DiaryRepository;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidarySolution;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DiaryManager
{
    private $entityManager;
    private $diaryRepository;

    public function __construct(EntityManagerInterface $entityManager, DiaryRepository $diaryRepository)
    {
        $this->entityManager = $entityManager;
        $this->diaryRepository = $diaryRepository;
    }

    /**
     * Add an entry in Diary
     *
     * @param Action $action                        Action related to this entry
     * @param User $user                            User related to this entry
     * @param User $author                          Author creating this entry (admin or user itself)
     * @param string $comment                       Comment about this entry
     * @param Solidary $solidary                    If this entry is related to a Solidary
     * @param SolidarySolution $solidarySolution    If this entry is related to a SolidarySolution
     * @param float $progression                    Custom progression If it's null, we take the default progression of the action
     * @return void
     */
    public function addDiaryEntry(Action $action, User $user, User $author, string $comment=null, Solidary $solidary=null, SolidarySolution $solidarySolution=null, float $progression=null): void
    {
        $diary = new Diary();
        $diary->setAction($action);
        $diary->setUser($user);
        $diary->setAuthor($author);
    
        if (!is_null($comment)) {
            $diary->setComment($comment);
        }
        if (!is_null($solidary)) {
            $diary->setSolidary($solidary);
        }
        if (!is_null($solidarySolution)) {
            $diary->setSolidarySolution($solidarySolution);
        }

        if (!is_null($progression)) {
            $diary->setProgression($progression);
        } elseif ((float)$action->getProgression()>0) {
            $diary->setProgression($action->getProgression());
            if (!is_null($solidary)) {
                $solidary->setProgression($action->getProgression());
                $this->entityManager->persist($solidary);
            }
        } elseif ($solidary && (float)$action->getProgression()<0) {
            // set progression to last progression
            if ($lastEntry = $this->diaryRepository->findLastEntryForSolidary($solidary)) {
                $diary->setProgression((string)$lastEntry->getProgression());
            }
        }

        $this->entityManager->persist($diary);
        $this->entityManager->flush();
    }

    /**
     * Handle an animation
     *
     * @param Animation $animation    The animation that has been made
     * @return void
     */
    public function handleAnimation(Animation $animation): void
    {
        // if the action linked with the animation is not meant to be logged in the diary, we exit immediately
        if (!$animation->getAction()->isInDiary()) {
            return;
        }

        // add an entry in diary
        $this->addDiaryEntry(
            $animation->getAction(),
            $animation->getUser(),
            $animation->getAuthor(),
            $animation->getComment(),
            $animation->getSolidary(),
            $animation->getSolidarySolution(),
            $animation->getProgression()
        );
    }
}
