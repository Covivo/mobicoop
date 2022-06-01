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

namespace App\Solidary\Repository;

use App\Carpool\Entity\Criteria;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Service\SolidaryMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryUserRepository
{
    public const USE_DAY_RESTRICTION = true; // Restriction by the time range slot by slot (m,a,e) of a SolidaryVolunteer in matching
    public const USE_TIME_RESTRICTION = true; // Restriction by the time range slot by slot (m,a,e) of a SolidaryVolunteer in matching
    public const USE_GEOGRAPHIC_RESTRICTION = true; // Restriction by the maxDistance of a SolidaryVolunteer in matching

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
     * Get a SolidaryUser by its User id.
     *
     * @param int $id The user id
     *
     * @return null|SolidaryUser The SolidaryUser if found, null if not found
     */
    public function findByUserId(int $id): ?SolidaryUser
    {
        $query = $this->repository->createQueryBuilder('su')
            ->join('su.user', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Get a SolidaryUser by its email.
     */
    public function findByEmail(string $email): ?SolidaryUser
    {
        $query = $this->repository->createQueryBuilder('v')
            ->join('v.user', 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find the matching SolidaryUser for a Solidary Transport Search.
     */
    public function findForASolidaryTransportSearch(SolidarySearch $solidaryTransportSearch): ?array
    {
        // Get the criteria of the beneficiary's proposal
        $criteria = $solidaryTransportSearch->getSolidary()->getProposal()->getCriteria();

        // Only the volunteer
        $query = $this->repository->createQueryBuilder('su')
            ->join('su.address', 'a')
            ->where('su.volunteer = 1')
        ;

        // Get the Structure
        $structure = $solidaryTransportSearch->getSolidary()->getSolidaryUserStructure()->getStructure();

        if (Criteria::FREQUENCY_PUNCTUAL == $criteria->getFrequency()) {
            // Punctual journey

            // Date

            // Get the slot of the MinTime and MaxTime for the current structure
            $slot = $this->solidaryMatcher->getHourSlot($criteria->getMinTime(), $criteria->getMaxTime(), $structure);

            // We need to determine the weekday of the fromDate and take only the volunteer available that day on the slot
            if (self::USE_DAY_RESTRICTION) {
                $weekDay = $criteria->getFromDate()->format('w');

                switch ($weekDay) {
                    case 0:$query->andWhere('su.'.$slot.'Sun = 1');

break;

                    case 1: $query->andWhere('su.'.$slot.'Mon = 1');

break;

                    case 2: $query->andWhere('su.'.$slot.'Tue = 1');

break;

                    case 3: $query->andWhere('su.'.$slot.'Wed = 1');

break;

                    case 4: $query->andWhere('su.'.$slot.'Thu = 1');

break;

                    case 5: $query->andWhere('su.'.$slot.'Fri = 1');

break;

                    case 6: $query->andWhere('su.'.$slot.'Sat = 1');

break;
                }
            }
            if (self::USE_TIME_RESTRICTION) {
                $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getMinTime()->format('H:i:s').'\'');
                $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getMaxTime()->format('H:i:s').'\'');
            }
        } else {
            // Regular journey

            // If the SolidaryUser can drive on the particular days of the critera
            // We look also if the SolidaryUser has set a maching min and max time for the maching hour slot
            if ($criteria->isMonCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getMonMinTime(), $criteria->getMonMaxTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Mon = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getMonMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getMonMaxTime()->format('H:i:s').'\'');
                }
            }
            if ($criteria->isTueCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getTueMinTime(), $criteria->getTueMaxTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Tue = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getTueMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getTueMaxTime()->format('H:i:s').'\'');
                }
            }
            if ($criteria->isWedCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getWedMinTime(), $criteria->getWedMaxTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Wed = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getWedMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getWedMaxTime()->format('H:i:s').'\'');
                }
            }
            if ($criteria->isThuCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getThuMinTime(), $criteria->getThuMaxTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Thu = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getThuMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getThuMaxTime()->format('H:i:s').'\'');
                }
            }
            if ($criteria->isFriCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getFriMinTime(), $criteria->getFriMinTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Fri = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getFriMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getFriMaxTime()->format('H:i:s').'\'');
                }
            }
            if ($criteria->isSatCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getSatMinTime(), $criteria->getSatMaxTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Sat = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getSatMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getSatMaxTime()->format('H:i:s').'\'');
                }
            }
            if ($criteria->isSunCheck()) {
                $slot = $this->solidaryMatcher->getHourSlot($criteria->getSunMinTime(), $criteria->getSunMaxTime(), $structure);
                if (self::USE_DAY_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'Sun = 1');
                }
                if (self::USE_TIME_RESTRICTION) {
                    $query->andWhere('su.'.$slot.'MinTime <= \''.$criteria->getSunMinTime()->format('H:i:s').'\'');
                    $query->andWhere('su.'.$slot.'MaxTime >= \''.$criteria->getSunMaxTime()->format('H:i:s').'\'');
                }
            }
        }// end if punctual/regular

        // Geographic criteria
        if (self::USE_GEOGRAPHIC_RESTRICTION) {
            // The origin of the Proposal used in this search must be under the maxDistance of the SolidaryUser volunteer
            // If the search is on the return, we use the destination instead
            $waypoints = $solidaryTransportSearch->getSolidary()->getProposal()->getWaypoints();
            $address = null;
            if ('outward' == $solidaryTransportSearch->getWay()) {
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
            $sqlDistance = '(6378000 * acos(cos(radians('.$address->getLatitude().')) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians('.$address->getLongitude().')) + sin(radians('.$address->getLatitude().')) * sin(radians(a.latitude))))';
            $query->andWhere($sqlDistance.' <= su.maxDistance');
        }

        $queryResults = $query->getQuery()->getResult();

        // We need to build and persist all the new results as SolidaryMatching.
        $solidaryMatchings = $this->solidaryMatcher->buildSolidaryMatchingsForTransport($solidaryTransportSearch->getSolidary(), $queryResults);

        // We build the array of SolidaryResult
        $results = [];
        foreach ($solidaryMatchings as $solidaryMatching) {
            $results[] = $this->solidaryMatcher->buildSolidaryResultTransport($solidaryMatching);
        }

        return $results;
    }

    /**
     * Find all solidary users.
     *
     * @param array $filters Optionnal Filters on SolidaryUser
     *
     * @return SolidaryUser[]
     */
    public function findSolidaryUsers(array $filters): array
    {
        $query = $this->repository->createQueryBuilder('su')
            ->join('su.user', 'u')
        ;

        // Filters
        if (!is_null($filters)) {
            foreach ($filters as $filter => $value) {
                if ('q' !== $filter) {
                    $query->andWhere('u.'.$filter." like '%".$value."%'");
                } else {
                    $query->andWhere("u.givenName like '%".$value."%' or u.familyName like '%".$value."%' or CONCAT(u.givenName,' ',u.familyName) like '%".$value."%'");
                }
            }
        }

        return $query->getQuery()->getResult();
    }
}
