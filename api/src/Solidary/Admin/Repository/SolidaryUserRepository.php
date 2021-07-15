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

namespace App\Solidary\Admin\Repository;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Carpool\Entity\Waypoint;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
*/
class SolidaryUserRepository
{
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(SolidaryUser::class);
    }

    /**
     * Get the matching volunteers for a given solidary record, and a given type (oneway/outward/return)
     *
     * @param Solidary  $structure  The structure
     * @param int       $type       The type
     * @return array|null           The volunteers as SolidaryUsers
     */
    public function getMatchingVolunteers(Solidary $solidary, int $type)
    {
        $structure = $solidary->getSolidaryUserStructure()->getStructure();
        $centerPointOrigin = $centerPointDestination = null;
        if ($type !== Proposal::TYPE_RETURN) {
            $criteria = $solidary->getProposal()->getCriteria();
            $centerPointOrigin = $solidary->getProposal()->getWaypoints()[0]->getAddress();
            if (!$solidary->getProposal()->hasNoDestination()) {
                foreach ($solidary->getProposal()->getWaypoints() as $waypoint) {
                    /**
                     * @var Waypoint $waypoint
                     */
                    if ($waypoint->isDestination()) {
                        $centerPointDestination = $waypoint->getAddress();
                        break;
                    }
                }
            }
        } else {
            $criteria = $solidary->getProposal()->getProposalLinked()->getCriteria();
            $centerPointOrigin = $solidary->getProposal()->getProposalLinked()->getWaypoints()[0]->getAddress();
            if (!$solidary->getProposal()->getProposalLinked()->hasNoDestination()) {
                foreach ($solidary->getProposal()->getProposalLinked()->getWaypoints() as $waypoint) {
                    /**
                     * @var Waypoint $waypoint
                     */
                    if ($waypoint->isDestination()) {
                        $centerPointDestination = $waypoint->getAddress();
                        break;
                    }
                }
            }
        }
        
        // we want to get accepted volunteers only for the given structure, and that are not already in the matchings
        // first we get the accepted volunteers
        $query = $this->repository->createQueryBuilder('su')
        ->join('su.address', 'a')
        ->join('su.solidaryUserStructures', 'sus')
        ->where('su.volunteer = 1 and sus.structure = :structure and sus.status = :status')
        ->setParameter('structure', $structure)
        ->setParameter('status', SolidaryUserStructure::STATUS_ACCEPTED)
        ;
        
        if ($criteria->getFrequency()==Criteria::FREQUENCY_PUNCTUAL) {
            $shortDay = $this->getShortDay($criteria->getFromDate()->format('w'));
            $query->andWhere(
                '((su.mMinTime <= :maxTime and su.mMaxTime > :minTime and su.m'.$shortDay.' = 1) or ' .
                '(su.aMinTime <= :maxTime and su.aMaxTime > :minTime and su.a'.$shortDay.' = 1) or ' .
                '(su.eMinTime <= :maxTime and su.eMaxTime > :minTime and su.e'.$shortDay.' = 1))'
            )
            ->setParameter(':minTime', $criteria->getMinTime()->format("H:i:s"))
            ->setParameter(':maxTime', $criteria->getMaxTime()->format("H:i:s"));
        } else {
            $andWhere = '';
            if ($criteria->isMonCheck()) {
                $andWhere .=
                    '((su.mMinTime <= :monMaxTime and su.mMaxTime > :monMinTime and su.mMon = 1) or ' .
                    '(su.aMinTime <= :monMaxTime and su.aMaxTime > :monMinTime and su.aMon = 1) or ' .
                    '(su.eMinTime <= :monMaxTime and su.eMaxTime > :monMinTime and su.eMon = 1))';
                $query->setParameter(':monMinTime', $criteria->getMonMinTime()->format("H:i:s"));
                $query->setParameter(':monMaxTime', $criteria->getMonMaxTime()->format("H:i:s"));
            }
            if ($criteria->isTueCheck()) {
                if ($andWhere != '') {
                    $andWhere .= ' OR ';
                }
                $andWhere .=
                    '((su.mMinTime <= :tueMaxTime and su.mMaxTime > :tueMinTime and su.mTue = 1) or ' .
                    '(su.aMinTime <= :tueMaxTime and su.aMaxTime > :tueMinTime and su.aTue = 1) or ' .
                    '(su.eMinTime <= :tueMaxTime and su.eMaxTime > :tueMinTime and su.eTue = 1))';
                $query->setParameter(':tueMinTime', $criteria->getTueMinTime()->format("H:i:s"));
                $query->setParameter(':tueMaxTime', $criteria->getTueMaxTime()->format("H:i:s"));
            }
            if ($criteria->isWedCheck()) {
                if ($andWhere != '') {
                    $andWhere .= ' OR ';
                }
                $andWhere .=
                    '((su.mMinTime <= :wedMaxTime and su.mMaxTime > :wedMinTime and su.mWed = 1) or ' .
                    '(su.aMinTime <= :wedMaxTime and su.aMaxTime > :wedMinTime and su.aWed = 1) or ' .
                    '(su.eMinTime <= :wedMaxTime and su.eMaxTime > :wedMinTime and su.eWed = 1))';
                $query->setParameter(':wedMinTime', $criteria->getWedMinTime()->format("H:i:s"));
                $query->setParameter(':wedMaxTime', $criteria->getWedMaxTime()->format("H:i:s"));
            }
            if ($criteria->isThuCheck()) {
                if ($andWhere != '') {
                    $andWhere .= ' OR ';
                }
                $andWhere .=
                    '((su.mMinTime <= :thuMaxTime and su.mMaxTime > :thuMinTime and su.mThu = 1) or ' .
                    '(su.aMinTime <= :thuMaxTime and su.aMaxTime > :thuMinTime and su.aThu = 1) or ' .
                    '(su.eMinTime <= :thuMaxTime and su.eMaxTime > :thuMinTime and su.eThu = 1))';
                $query->setParameter(':thuMinTime', $criteria->getThuMinTime()->format("H:i:s"));
                $query->setParameter(':thuMaxTime', $criteria->getThuMaxTime()->format("H:i:s"));
            }
            if ($criteria->isFriCheck()) {
                if ($andWhere != '') {
                    $andWhere .= ' OR ';
                }
                $andWhere .=
                    '((su.mMinTime <= :friMaxTime and su.mMaxTime > :friMinTime and su.mFri = 1) or ' .
                    '(su.aMinTime <= :friMaxTime and su.aMaxTime > :friMinTime and su.aFri = 1) or ' .
                    '(su.eMinTime <= :friMaxTime and su.eMaxTime > :friMinTime and su.eFri = 1))';
                $query->setParameter(':friMinTime', $criteria->getFriMinTime()->format("H:i:s"));
                $query->setParameter(':friMaxTime', $criteria->getFriMaxTime()->format("H:i:s"));
            }
            if ($criteria->isSatCheck()) {
                if ($andWhere != '') {
                    $andWhere .= ' OR ';
                }
                $andWhere .=
                    '((su.mMinTime <= :satMaxTime and su.mMaxTime > :satMinTime and su.mSat = 1) or ' .
                    '(su.aMinTime <= :satMaxTime and su.aMaxTime > :satMinTime and su.aSat = 1) or ' .
                    '(su.eMinTime <= :satMaxTime and su.eMaxTime > :satMinTime and su.eSat = 1))';
                $query->setParameter(':satMinTime', $criteria->getSatMinTime()->format("H:i:s"));
                $query->setParameter(':satMaxTime', $criteria->getSatMaxTime()->format("H:i:s"));
            }
            if ($criteria->isSunCheck()) {
                if ($andWhere != '') {
                    $andWhere .= ' OR ';
                }
                $andWhere .=
                    '((su.mMinTime <= :sunMaxTime and su.mMaxTime > :sunMinTime and su.mSun = 1) or ' .
                    '(su.aMinTime <= :sunMaxTime and su.aMaxTime > :sunMinTime and su.aSun = 1) or ' .
                    '(su.eMinTime <= :sunMaxTime and su.eMaxTime > :sunMinTime and su.eSun = 1))';
                $query->setParameter(':sunMinTime', $criteria->getSunMinTime()->format("H:i:s"));
                $query->setParameter(':sunMaxTime', $criteria->getSunMaxTime()->format("H:i:s"));
            }
            if ($andWhere != '') {
                $query->andWhere($andWhere);
            }
        }

        if ($centerPointOrigin) {
            $sqlDistanceOrigin = '(6378000 * acos(cos(radians(' . $centerPointOrigin->getLatitude() . ')) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(' . $centerPointOrigin->getLongitude() . ')) + sin(radians(' . $centerPointOrigin->getLatitude() . ')) * sin(radians(a.latitude))))';
            $query->andWhere($sqlDistanceOrigin . " <= su.maxDistance");
        }
        if ($centerPointDestination) {
            $sqlDistanceDestination = '(6378000 * acos(cos(radians(' . $centerPointDestination->getLatitude() . ')) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(' . $centerPointDestination->getLongitude() . ')) + sin(radians(' . $centerPointDestination->getLatitude() . ')) * sin(radians(a.latitude))))';
            $query->andWhere($sqlDistanceDestination . " <= su.maxDistance");
        }
        
        $acceptedVolunteers = $query->getQuery()->getResult();

        // we remove the volunteers that are already in the matchings (maybe find a way to do it in the sql request ???)
        $volunteers = [];
        foreach ($acceptedVolunteers as $acceptedVolunteer) {
            /**
             * @var SolidaryUser $acceptedVolunteer
             */
            foreach ($acceptedVolunteer->getSolidaryMatchings() as $solidaryMatching) {
                /**
                 * @var SolidaryMatching $solidaryMatching
                 */
                if ($solidaryMatching->getSolidary()->getId() === $solidary->getId()) {
                    // this volunteer is already in matchings
                    // we skip the parent loop and check the next volunteer
                    continue 2;
                }
            }
            $volunteers[] = $acceptedVolunteer;
        }
        return $volunteers;
    }

    /**
     * Return the short day name from the day number
     *
     * @param integer $day  The 0-based number of the day, starting with sunday
     * @return string       The 3 letters short name of the day
     */
    private function getShortDay(int $day): string
    {
        switch ($day) {
            case 0: return 'Sun';
            case 1: return 'Mon';
            case 2: return 'Tue';
            case 3: return 'Wed';
            case 4: return 'Thu';
            case 5: return 'Fri';
            case 6: return 'Sat';
        }
    }
}
