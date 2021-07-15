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

namespace App\Solidary\Admin\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Solidary\Admin\Repository\SolidaryUserRepository;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\Structure;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Solidary transport matcher in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryTransportMatcher
{
    private $entityManager;
    private $solidaryUserRepository;

    /**
    * Constructor
    */
    public function __construct(EntityManagerInterface $entityManager, SolidaryUserRepository $solidaryUserRepository)
    {
        $this->entityManager = $entityManager;
        $this->solidaryUserRepository = $solidaryUserRepository;
    }

    /**
     * Match a solidary record with transport volunteers
     *
     * @param Solidary $solidary    The solidary record
     * @return void
     */
    public function match(Solidary $solidary)
    {
        // first we get the volunteers for the outward
        $outwardVolunteers = $this->solidaryUserRepository->getMatchingVolunteers($solidary, $solidary->getProposal()->getType());

        // then we get the volunteers for the return (if relevant)
        $returnVolunteers = [];
        if ($solidary->getProposal()->getProposalLinked()) {
            $returnVolunteers = $this->solidaryUserRepository->getMatchingVolunteers($solidary, $solidary->getProposal()->getProposalLinked()->getType());
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
     * Match all the solidary records of a given structure
     *
     * @param Structure $structure  The structure
     * @return void
     */
    public function matchForStructure(Structure $structure)
    {
        foreach ($structure->getSolidaryUserStructures() as $solidaryUserStructure) {
            /**
             * @var SolidaryUserStructure $solidaryUserStructure
             */
            foreach ($solidaryUserStructure->getSolidaries() as $solidary) {
                $this->match($solidary);
            }
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
                    // punctual return => criteria is the same than the solidary proposalLinked criteria
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
