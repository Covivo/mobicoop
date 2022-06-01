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
 */

namespace App\Solidary\Service;

use App\Action\Entity\Diary;
use App\Action\Repository\DiaryRepository;
use App\Solidary\Entity\SolidaryAnimation;
use App\Solidary\Event\SolidaryAnimationPostedEvent;
use App\Solidary\Repository\SolidaryRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAnimationManager
{
    private $dispatcher;
    private $diaryRepository;
    private $solidaryRepository;

    public function __construct(EventDispatcherInterface $dispatcher, SolidaryRepository $solidaryRepository, DiaryRepository $diaryRepository)
    {
        $this->dispatcher = $dispatcher;
        $this->diaryRepository = $diaryRepository;
        $this->solidaryRepository = $solidaryRepository;
    }

    public function treatSolidaryAnimation(SolidaryAnimation $solidaryAnimation)
    {
        $event = new SolidaryAnimationPostedEvent($solidaryAnimation);
        $this->dispatcher->dispatch(SolidaryAnimationPostedEvent::NAME, $event);
    }

    /**
     * Get the SolidaryAnimations for a Solidary.
     *
     * @param int $solidaryId The id of the Solidary
     */
    public function getSolidaryAnimations(int $solidaryId): array
    {
        $solidary = $this->solidaryRepository->find($solidaryId);

        $diaries = $this->diaryRepository->findBy(['solidary' => $solidary]);

        $return = [];
        foreach ($diaries as $diary) {
            /**
             * @var Diary $diary
             */
            $solidaryAnimation = new SolidaryAnimation();
            $solidaryAnimation->setActionName($diary->getAction()->getName());
            $solidaryAnimation->setComment($diary->getComment());
            $solidaryAnimation->setUser($diary->getUser());
            $solidaryAnimation->setAuthor($diary->getAuthor());
            $solidaryAnimation->setProgression($diary->getProgression());
            $solidaryAnimation->setSolidary($diary->getSolidary());
            $solidaryAnimation->setSolidarySolution($diary->getSolidarySolution());
            $solidaryAnimation->setCreatedDate($diary->getCreatedDate());
            $solidaryAnimation->setUpdatedDate($diary->getUpdatedDate());
            // we set transporter or carpooler if present
            if ($diary->getSolidarySolution()) {
                // case of a transporter
                if ($diary->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()) {
                    $solidaryAnimation->setTransporter($diary->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser());
                // case of a carpooler
                } else {
                    $solidaryAnimation->setCarpooler($diary->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser());
                }
            }
            $return[] = $solidaryAnimation;
        }

        return $return;
    }
}
