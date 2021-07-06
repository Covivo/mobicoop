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
use App\Geography\Entity\Address;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidaryUser;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
*/
class SolidaryUserRepository
{
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(SolidaryUser::class);
    }

    /**
     * Create the volunteers SolidaryMatchings for a Solidary record
     *
     * @param Solidary  $solidary   The solidary record
     * @return void
     */
    public function createSolidaryMatchings(Solidary $solidary)
    {
        // first we get the volunteers for the outward
        $outwardVolunteers = $this->getMatchingVolunteers($solidary->getProposal()->getCriteria(), $solidary->getProposal()->getWaypoints()[0]->getAddress());

        // then we get the volunteers for the return (if relevant)
        $returnVolunteers = null;
        if ($solidary->getProposal()->getProposalLinked()) {
            $returnVolunteers = $this->getMatchingVolunteers($solidary->getProposal()->getProposalLinked()->getCriteria(), $solidary->getProposal()->getProposalLinked()->getWaypoints()[0]->getAddress());
        }

        // create outward SolidaryMatchings
        $outwardMatchings = [];
        foreach ($outwardVolunteers as $volunteer) {
            /**
             * @var SolidaryUser $volunteer
             */
            $solidaryMatching = new SolidaryMatching();
            $solidaryMatching->setSolidaryUser($volunteer);
            $solidaryMatching->setSolidary($solidary);
            $solidaryMatching->setType($solidary->getProposal()->getType());
            $solidaryMatching->setCriteria($this->createMatchingCriteria($solidaryMatching));
            $solidary->addSolidaryMatching($solidaryMatching);
            $this->entityManager->persist($solidaryMatching);
            $this->entityManager->persist($solidary);
            $outwardMatchings[] = $solidaryMatching;
        }

        // create return SolidaryMatchings
        foreach ($returnVolunteers as $volunteer) {
            /**
             * @var SolidaryUser $volunteer
             */
            $solidaryMatching = new SolidaryMatching();
            $solidaryMatching->setSolidaryUser($volunteer);
            $solidaryMatching->setSolidary($solidary);
            $solidaryMatching->setType($solidary->getProposal()->getProposalLinked()->getType());
            $solidaryMatching->setCriteria($this->createMatchingCriteria($solidaryMatching));
            $solidary->addSolidaryMatching($solidaryMatching);
            // check if an outward is set for this volunteer => if so, link the solidary matchings
            foreach ($outwardMatchings as $outwardMatching) {
                /**
                 * @var SolidaryMatching $outwardMatching
                 */
                if ($volunteer->getId() === $outwardMatching->getSolidaryUser()->getId()) {
                    $solidaryMatching->setSolidaryMatchingLinked($outwardMatching);
                    break;
                }
            }
            $this->entityManager->persist($solidaryMatching);
            $this->entityManager->persist($solidary);
        }

        $this->entityManager->flush();
    }

    /**
     * Get the matching volunteers for a given criteria
     *
     * @param Criteria $criteria    The criteria
     * @param Address $centerPoint  The center point to use for geographical restriction
     * @return array|null           The volunteers as SolidaryUsers
     */
    private function getMatchingVolunteers(Criteria $criteria, Address $centerPoint)
    {
        // we want to get volunteers only
        $query = $this->repository->createQueryBuilder('su')
        ->join('su.address', 'a')
        ->where('su.volunteer = 1');
        
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
            if ($criteria->isMonCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :monMaxTime and su.mMaxTime > :monMinTime and su.mMon = 1) or ' .
                    '(su.aMinTime <= :monMaxTime and su.aMaxTime > :monMinTime and su.aMon = 1) or ' .
                    '(su.eMinTime <= :monMaxTime and su.eMaxTime > :monMinTime and su.eMon = 1))'
                )
                ->setParameter(':monMinTime', $criteria->getMonMinTime()->format("H:i:s"))
                ->setParameter(':monMaxTime', $criteria->getMonMaxTime()->format("H:i:s"));
            }
            if ($criteria->isTueCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :tueMaxTime and su.mMaxTime > :tueMinTime and su.mTue = 1) or ' .
                    '(su.aMinTime <= :tueMaxTime and su.aMaxTime > :tueMinTime and su.aTue = 1) or ' .
                    '(su.eMinTime <= :tueMaxTime and su.eMaxTime > :tueMinTime and su.eTue = 1))'
                )
                ->setParameter(':tueMinTime', $criteria->getTueMinTime()->format("H:i:s"))
                ->setParameter(':tueMaxTime', $criteria->getTueMaxTime()->format("H:i:s"));
            }
            if ($criteria->isWedCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :wedMaxTime and su.mMaxTime > :wedMinTime and su.mWed = 1) or ' .
                    '(su.aMinTime <= :wedMaxTime and su.aMaxTime > :wedMinTime and su.aWed = 1) or ' .
                    '(su.eMinTime <= :wedMaxTime and su.eMaxTime > :wedMinTime and su.eWed = 1))'
                )
                ->setParameter(':wedMinTime', $criteria->getWedMinTime()->format("H:i:s"))
                ->setParameter(':wedMaxTime', $criteria->getWedMaxTime()->format("H:i:s"));
            }
            if ($criteria->isThuCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :thuMaxTime and su.mMaxTime > :thuMinTime and su.mThu = 1) or ' .
                    '(su.aMinTime <= :thuMaxTime and su.aMaxTime > :thuMinTime and su.aThu = 1) or ' .
                    '(su.eMinTime <= :thuMaxTime and su.eMaxTime > :thuMinTime and su.eThu = 1))'
                )
                ->setParameter(':thuMinTime', $criteria->getThuMinTime()->format("H:i:s"))
                ->setParameter(':thuMaxTime', $criteria->getThuMaxTime()->format("H:i:s"));
            }
            if ($criteria->isFriCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :friMaxTime and su.mMaxTime > :friMinTime and su.mFri = 1) or ' .
                    '(su.aMinTime <= :friMaxTime and su.aMaxTime > :friMinTime and su.aFri = 1) or ' .
                    '(su.eMinTime <= :friMaxTime and su.eMaxTime > :friMinTime and su.eFri = 1))'
                )
                ->setParameter(':friMinTime', $criteria->getFriMinTime()->format("H:i:s"))
                ->setParameter(':friMaxTime', $criteria->getFriMaxTime()->format("H:i:s"));
            }
            if ($criteria->isSatCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :satMaxTime and su.mMaxTime > :satMinTime and su.mSat = 1) or ' .
                    '(su.aMinTime <= :satMaxTime and su.aMaxTime > :satMinTime and su.aSat = 1) or ' .
                    '(su.eMinTime <= :satMaxTime and su.eMaxTime > :satMinTime and su.eSat = 1))'
                )
                ->setParameter(':satMinTime', $criteria->getSatMinTime()->format("H:i:s"))
                ->setParameter(':satMaxTime', $criteria->getSatMaxTime()->format("H:i:s"));
            }
            if ($criteria->isSunCheck()) {
                $query->orWhere(
                    '((su.mMinTime <= :sunMaxTime and su.mMaxTime > :sunMinTime and su.mSun = 1) or ' .
                    '(su.aMinTime <= :sunMaxTime and su.aMaxTime > :sunMinTime and su.aSun = 1) or ' .
                    '(su.eMinTime <= :sunMaxTime and su.eMaxTime > :sunMinTime and su.eSun = 1))'
                )
                ->setParameter(':sunMinTime', $criteria->getSunMinTime()->format("H:i:s"))
                ->setParameter(':sunMaxTime', $criteria->getSunMaxTime()->format("H:i:s"));
            }
        }

        $sqlDistance = '(6378000 * acos(cos(radians(' . $centerPoint->getLatitude() . ')) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(' . $centerPoint->getLongitude() . ')) + sin(radians(' . $centerPoint->getLatitude() . ')) * sin(radians(a.latitude))))';
        $query->andWhere($sqlDistance . " <= su.maxDistance");

        return $query->getQuery()->getResult();
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

    /**
     * Create a Criteria for a SolidaryMatching
     *
     * @param SolidaryMatching $solidaryMatching    The SolidaryMatching
     * @return Criteria                             The resulting criteria
     */
    private function createMatchingCriteria(SolidaryMatching $solidaryMatching)
    {
        switch ($solidaryMatching->getType()) {
            case Proposal::TYPE_ONE_WAY:
            case Proposal::TYPE_OUTWARD:
                if ($solidaryMatching->getSolidary()->getAdminfrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    // punctual oneway / outward => criteria is the same than the solidary proposal criteria
                    return clone $solidaryMatching->getSolidary()->getProposal()->getCriteria();
                } else {
                    // regular => build criteria from matching days
                    return $this->createCriteriaForRegular($solidaryMatching->getSolidary()->getProposal()->getCriteria(), $solidaryMatching->getSolidaryUser());
                }
                break;
            case Proposal::TYPE_RETURN:
                if ($solidaryMatching->getSolidary()->getAdminfrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    // punctual return => criteria is the same than the solidary porposalLinked criteria
                    return clone $solidaryMatching->getSolidary()->getProposal()->getProposalLinked()->getCriteria();
                } else {
                    // regular => build criteria from matching days
                    return $this->createCriteriaForRegular($solidaryMatching->getSolidary()->getProposal()->getProposalLinked()->getCriteria(), $solidaryMatching->getSolidaryUser());
                }
                break;
        }
    }

    /**
     * Create a Criteria from a base criteria and a volunteer (find regular matching days and times)
     *
     * @param Criteria $baseCriteria    The base criteria
     * @param SolidaryUser $volunteer   The volunteer
     * @return void
     */
    private function createCriteriaForRegular(Criteria $baseCriteria, SolidaryUser $volunteer)
    {
        // we clone the base criteria and we replace the schedule by the availabilities
        $criteria = clone $baseCriteria;
        $criteria->setMonCheck(false);
        $criteria->setMonMinTime(null);
        $criteria->setMonMaxTime(null);
        $criteria->setMonTime(null);
        $criteria->setTueCheck(false);
        $criteria->setTueMinTime(null);
        $criteria->setTueMaxTime(null);
        $criteria->setTueTime(null);
        $criteria->setWedCheck(false);
        $criteria->setWedMinTime(null);
        $criteria->setWedMaxTime(null);
        $criteria->setWedTime(null);
        $criteria->setThuCheck(false);
        $criteria->setThuMinTime(null);
        $criteria->setThuMaxTime(null);
        $criteria->setThuTime(null);
        $criteria->setFriCheck(false);
        $criteria->setFriMinTime(null);
        $criteria->setFriMaxTime(null);
        $criteria->setFriTime(null);
        $criteria->setSatCheck(false);
        $criteria->setSatMinTime(null);
        $criteria->setSatMaxTime(null);
        $criteria->setSatTime(null);
        $criteria->setSunCheck(false);
        $criteria->setSunMinTime(null);
        $criteria->setSunMaxTime(null);
        $criteria->setSunTime(null);
        if (
            $baseCriteria->isMonCheck() &&
            (
                ($volunteer->hasMMon() && strtotime($baseCriteria->getMonMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getMonMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasAMon() && strtotime($baseCriteria->getMonMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getMonMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasEMon() && strtotime($baseCriteria->getMonMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getMonMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setMonCheck(true);
            $criteria->setMonMinTime($baseCriteria->getMonMinTime());
            $criteria->setMonMaxTime($baseCriteria->getMonMaxTime());
            $criteria->setMonTime($baseCriteria->getMonTime());
        }
        if (
            $baseCriteria->isTueCheck() &&
            (
                ($volunteer->hasMTue() && strtotime($baseCriteria->getTueMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getTueMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasATue() && strtotime($baseCriteria->getTueMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getTueMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasETue() && strtotime($baseCriteria->getTueMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getTueMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setTueCheck(true);
            $criteria->setTueMinTime($baseCriteria->getTueMinTime());
            $criteria->setTueMaxTime($baseCriteria->getTueMaxTime());
            $criteria->setTueTime($baseCriteria->getTueTime());
        }
        if (
            $baseCriteria->isWedCheck() &&
            (
                ($volunteer->hasMWed() && strtotime($baseCriteria->getWedMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getWedMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasAWed() && strtotime($baseCriteria->getWedMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getWedMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasEWed() && strtotime($baseCriteria->getWedMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getWedMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setWedCheck(true);
            $criteria->setWedMinTime($baseCriteria->getWedMinTime());
            $criteria->setWedMaxTime($baseCriteria->getWedMaxTime());
            $criteria->setWedTime($baseCriteria->getWedTime());
        }
        if (
            $baseCriteria->isThuCheck() &&
            (
                ($volunteer->hasMThu() && strtotime($baseCriteria->getThuMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getThuMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasAThu() && strtotime($baseCriteria->getThuMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getThuMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasEThu() && strtotime($baseCriteria->getThuMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getThuMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setThuCheck(true);
            $criteria->setThuMinTime($baseCriteria->getThuMinTime());
            $criteria->setThuMaxTime($baseCriteria->getThuMaxTime());
            $criteria->setThuTime($baseCriteria->getThuTime());
        }
        if (
            $baseCriteria->isFriCheck() &&
            (
                ($volunteer->hasMFri() && strtotime($baseCriteria->getFriMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getFriMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasAFri() && strtotime($baseCriteria->getFriMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getFriMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasEFri() && strtotime($baseCriteria->getFriMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getFriMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setFriCheck(true);
            $criteria->setFriMinTime($baseCriteria->getFriMinTime());
            $criteria->setFriMaxTime($baseCriteria->getFriMaxTime());
            $criteria->setFriTime($baseCriteria->getFriTime());
        }
        if (
            $baseCriteria->isSatCheck() &&
            (
                ($volunteer->hasMSat() && strtotime($baseCriteria->getSatMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getSatMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasASat() && strtotime($baseCriteria->getSatMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getSatMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasESat() && strtotime($baseCriteria->getSatMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getSatMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setSatCheck(true);
            $criteria->setSatMinTime($baseCriteria->getSatMinTime());
            $criteria->setSatMaxTime($baseCriteria->getSatMaxTime());
            $criteria->setSatTime($baseCriteria->getSatTime());
        }
        if (
            $baseCriteria->isSunCheck() &&
            (
                ($volunteer->hasMSun() && strtotime($baseCriteria->getSunMinTime()->format('H:i:s')) < strtotime($volunteer->getMMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getSunMaxTime()->format('H:i:s')) >= strtotime($volunteer->getMMinTime()->format('H:i:s'))) ||
                ($volunteer->hasASun() && strtotime($baseCriteria->getSunMinTime()->format('H:i:s')) < strtotime($volunteer->getAMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getSunMaxTime()->format('H:i:s')) >= strtotime($volunteer->getAMinTime()->format('H:i:s'))) ||
                ($volunteer->hasESun() && strtotime($baseCriteria->getSunMinTime()->format('H:i:s')) < strtotime($volunteer->getEMaxTime()->format('H:i:s')) && strtotime($baseCriteria->getSunMaxTime()->format('H:i:s')) >= strtotime($volunteer->getEMinTime()->format('H:i:s')))
            )
        ) {
            $criteria->setSunCheck(true);
            $criteria->setSunMinTime($baseCriteria->getSunMinTime());
            $criteria->setSunMaxTime($baseCriteria->getSunMaxTime());
            $criteria->setSunTime($baseCriteria->getSunTime());
        }
        return $criteria;
    }
}
