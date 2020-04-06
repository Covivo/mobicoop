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

namespace App\Solidary\Repository;

use App\Carpool\Entity\Criteria;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Entity\SolidaryResult\SolidaryResult;
use App\Solidary\Entity\SolidaryResult\SolidaryResultTransport;
use App\Solidary\Service\SolidaryMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method SolidaryUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SolidaryUser|null findOneBy(array $criteria, array $orderBy = null)
 */
class SolidaryUserRepository
{
    const USE_DAY_RESTRICTION = true; // Restriction by the time range slot by slot (m,a,e) of a SolidaryVolunteer in matching
    const USE_TIME_RESTRICTION = true; // Restriction by the time range slot by slot (m,a,e) of a SolidaryVolunteer in matching
    const USE_GEOGRAPHIC_RESTRICTION = true; // Restriction by the maxDistance of a SolidaryVolunteer in matching


    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    private $solidaryMatcher;

    public function __construct(EntityManagerInterface $entityManager, SolidaryMatcher $solidaryMatcher)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(SolidaryUser::class);
        $this->solidaryMatcher = $solidaryMatcher;
    }


    public function find(int $id): ?SolidaryUser
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }


    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?SolidaryUser
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get a SolidaryUser by its email
     *
     * @param string $email
     * @return SolidaryUser|null
     */
    public function findByEmail(string $email)
    {
        $query = $this->repository->createQueryBuilder('v')
        ->join('v.user', 'u')
        ->where('u.email = :email')
        ->setParameter('email', $email);

        return $query->getQuery()->getResult();
    }

    /**
     * Find the matching SolidaryUser for a Solidary Transport Search
     *
     * @param SolidarySearch $solidaryTransportSearch
     * @return array|null
     */
    public function findForASolidaryTransportSearch(SolidarySearch $solidaryTransportSearch): array
    {

        // Get the criteria of the beneficiary's proposal
        $criteria = $solidaryTransportSearch->getSolidary()->getProposal()->getCriteria();

        // Only the volunteer
        $query = $this->repository->createQueryBuilder('su')
                ->join('su.address', 'a')
                ->where('su.volunteer = 1');

        if ($criteria->getFrequency()==Criteria::FREQUENCY_PUNCTUAL) {
            // Punctual journey

            // Date

            // MinTime and MaxTime
            $slot = $this->getHourSlot($criteria->getMinTime(), $criteria->getMaxTime());
            // We need to determine the weekday of the fromDate and take only the volunteer available that day on the slot
            if (self::USE_DAY_RESTRICTION) {
                $weekDay = $criteria->getFromDate()->format('w');
                switch ($weekDay) {
                    case 0:$query->andWhere('su.'.$slot.'Sun = 1');break;
                    case 1: $query->andWhere('su.'.$slot.'Mon = 1');break;
                    case 2: $query->andWhere('su.'.$slot.'Tue = 1');break;
                    case 3: $query->andWhere('su.'.$slot.'Wed = 1');break;
                    case 4: $query->andWhere('su.'.$slot.'Thu = 1');break;
                    case 5: $query->andWhere('su.'.$slot.'Fri = 1');break;
                    case 6: $query->andWhere('su.'.$slot.'Sat = 1');break;
                }
            }
            if (self::USE_TIME_RESTRICTION) {
                $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getMinTime()->format("H:i:s").'\'');
                $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getMaxTime()->format("H:i:s").'\'');
            }
        } else {

            // Regular journey

            // If the SolidaryUser can drive on the particular days of the critera
            // We look also if the SolidaryUser has set a maching min and max time for the maching hour slot
            if ($criteria->isMonCheck()) {
                $slot = $this->getHourSlot($criteria->getMonMinTime(), $criteria->getMonMaxTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Mon = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getMonMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getMonMaxTime()->format("H:i:s").'\'');
                }
            }
            if ($criteria->isTueCheck()) {
                $slot = $this->getHourSlot($criteria->getTueMinTime(), $criteria->getTueMaxTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Tue = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getTueMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getTueMaxTime()->format("H:i:s").'\'');
                }
            }
            if ($criteria->isWedCheck()) {
                $slot = $this->getHourSlot($criteria->getWedMinTime(), $criteria->getWedMaxTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Wed = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getWedMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getWedMaxTime()->format("H:i:s").'\'');
                }
            }
            if ($criteria->isThuCheck()) {
                $slot = $this->getHourSlot($criteria->getThuMinTime(), $criteria->getThuMaxTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Thu = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getThuMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getThuMaxTime()->format("H:i:s").'\'');
                }
            }
            if ($criteria->isFriCheck()) {
                $slot = $this->getHourSlot($criteria->getFriMinTime(), $criteria->getFriMinTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Fri = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getFriMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getFriMaxTime()->format("H:i:s").'\'');
                }
            }
            if ($criteria->isSatCheck()) {
                $slot = $this->getHourSlot($criteria->getSatMinTime(), $criteria->getSatMaxTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Sat = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getSatMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getSatMaxTime()->format("H:i:s").'\'');
                }
            }
            if ($criteria->isSunCheck()) {
                $slot = $this->getHourSlot($criteria->getSunMinTime(), $criteria->getSunMaxTime());
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Sun = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getSunMinTime()->format("H:i:s").'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getSunMaxTime()->format("H:i:s").'\'');
                }
            }
        }// end if punctual/regular

        // Geographic criteria
        if (self::USE_GEOGRAPHIC_RESTRICTION) {
            // The origin of the Proposal used in this search must be under the maxDistance of the SolidaryUser volunteer
            // If the search is on the return, we use the destination instead
            $waypoints = $solidaryTransportSearch->getSolidary()->getProposal()->getWaypoints();
            $address = null;
            if ($solidaryTransportSearch->getWay()=="outward") {
                $address = $waypoints[0]->getAddress();
            } else {
                foreach ($waypoints as $waypoint) {
                    if ($waypoint->isDestination()) {
                        $address = $waypoint->getAddress();
                    }
                }
            }
            if (is_null($address)) {
                throw new SolidaryException(SolidaryException::NO_VALID_ADDRESS);
            }
            $sqlDistance = '(6378 * acos(cos(radians(' . $address->getLatitude() . ')) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(' . $address->getLongitude() . ')) + sin(radians(' . $address->getLatitude() . ')) * sin(radians(a.latitude))))';
            $query->andWhere($sqlDistance . " <= su.maxDistance");
        }
        
        $queryResults = $query->getQuery()->getResult();

        
        // We need to build and persist all the new results as SolidaryMatching.
        $solidaryMatchings = $this->solidaryMatcher->buildSolidaryMatchings($solidaryTransportSearch->getSolidary(), $queryResults);

        // We build the array of SolidaryResult
        $results = [];
        foreach ($solidaryMatchings as $solidaryMatching) {
            $results[] = $this->buildSolidaryResultTransport($solidaryMatching);
        }

        return $results;
    }

    /**
     * Get the hour slot of this time
     * m : morning, a : afternoon, e : evening
     *
     * @param \DateTimeInterface $time
     * @return string
     */
    private function getHourSlot(\DateTimeInterface $mintime, \DateTimeInterface $maxtime): string
    {
        $hoursSlots = Criteria::getHoursSlots();
        foreach ($hoursSlots as $slot => $hoursSlot) {
            if ($hoursSlot['min']<=$mintime && $maxtime<=$hoursSlot['max']) {
                return $slot;
            }
        }
        //should not append
        throw new SolidaryException(SolidaryException::INVALID_HOUR_SLOT);
        return "";
    }


    /**
     * Build a SolidaryResult with a SolidaryResultTransport from a SolidaryMatching
     *
     * @param SolidaryMatching $solidaryUser    The Solidary User
     * @return SolidaryResult
     */
    public function buildSolidaryResultTransport(SolidaryMatching $solidaryMatching): SolidaryResult
    {
        $solidaryResult = new SolidaryResult();
        $solidaryResultTransport = new SolidaryResultTransport();
        
        // The volunteer
        $solidaryResultTransport->setVolunteer($solidaryMatching->getSolidaryUser()->getUser()->getGivenName()." ".$solidaryMatching->getSolidaryUser()->getUser()->getFamilyName());

        // Home address of the volunteer
        $addresses = $solidaryMatching->getSolidaryUser()->getUser()->getAddresses();
        foreach ($addresses as $address) {
            if ($address->isHome()) {
                $solidaryResultTransport->setHome($address->getAddressLocality());
                break;
            }
        }
        
        // Schedule of the volunteer
        $solidaryResultTransport->setSchedule($this->getBuildedSchedule($solidaryMatching->getSolidaryUser()));

        $solidaryResult->setSolidaryResultTransport($solidaryResultTransport);

        // We set the source solidaryMatching
        $solidaryResult->setSolidaryMatching($solidaryMatching);


        return $solidaryResult;
    }

    /**
     * Get the builded schedule of a SolidaryUser
     *
     * @param SolidaryUser $solidaryUser
     * @return array
     */
    public function getBuildedSchedule(SolidaryUser $solidaryUser): array
    {
        ($solidaryUser->hasMMon()) ? $schedule['mon']['m'] = true : $schedule['mon']['m'] = false;
        ($solidaryUser->hasMTue()) ? $schedule['tue']['m'] = true : $schedule['tue']['m'] = false;
        ($solidaryUser->hasMWed()) ? $schedule['wed']['m'] = true : $schedule['wed']['m'] = false;
        ($solidaryUser->hasMThu()) ? $schedule['thu']['m'] = true : $schedule['thu']['m'] = false;
        ($solidaryUser->hasMFri()) ? $schedule['fri']['m'] = true : $schedule['fri']['m'] = false;
        ($solidaryUser->hasMSat()) ? $schedule['sat']['m'] = true : $schedule['sat']['m'] = false;
        ($solidaryUser->hasMSun()) ? $schedule['sun']['m'] = true : $schedule['sun']['m'] = false;
        ($solidaryUser->hasAMon()) ? $schedule['mon']['a'] = true : $schedule['mon']['a'] = false;
        ($solidaryUser->hasATue()) ? $schedule['tue']['a'] = true : $schedule['tue']['a'] = false;
        ($solidaryUser->hasAWed()) ? $schedule['wed']['a'] = true : $schedule['wed']['a'] = false;
        ($solidaryUser->hasAThu()) ? $schedule['thu']['a'] = true : $schedule['thu']['a'] = false;
        ($solidaryUser->hasAFri()) ? $schedule['fri']['a'] = true : $schedule['fri']['a'] = false;
        ($solidaryUser->hasASat()) ? $schedule['sat']['a'] = true : $schedule['sat']['a'] = false;
        ($solidaryUser->hasASun()) ? $schedule['sun']['a'] = true : $schedule['sun']['a'] = false;
        ($solidaryUser->hasEMon()) ? $schedule['mon']['e'] = true : $schedule['mon']['e'] = false;
        ($solidaryUser->hasETue()) ? $schedule['tue']['e'] = true : $schedule['tue']['e'] = false;
        ($solidaryUser->hasEWed()) ? $schedule['wed']['e'] = true : $schedule['wed']['e'] = false;
        ($solidaryUser->hasEThu()) ? $schedule['thu']['e'] = true : $schedule['thu']['e'] = false;
        ($solidaryUser->hasEFri()) ? $schedule['fri']['e'] = true : $schedule['fri']['e'] = false;
        ($solidaryUser->hasESat()) ? $schedule['sat']['e'] = true : $schedule['sat']['e'] = false;
        ($solidaryUser->hasESun()) ? $schedule['sun']['e'] = true : $schedule['sun']['e'] = false;

        return $schedule;
    }
}
