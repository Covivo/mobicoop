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

namespace App\Journey\Service;

use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Carpool\Service\ProposalManager;
use App\Journey\Entity\Journey;
use App\Journey\Repository\JourneyRepository;
use App\Service\FileManager;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

/**
 * Journey manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class JourneyManager
{
    private $entityManager;
    private $fileManager;
    private $journeyRepository;
    private $proposalManager;
    private $adManager;
    private $popularJourneyHomeMaxNumber;
    private $popularJourneyMaxNumber;
    private $popularJourneyMinOccurences;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileManager $fileManager,
        JourneyRepository $journeyRepository,
        ProposalManager $proposalManager,
        AdManager $adManager,
        int $popularJourneyMaxNumber,
        int $popularJourneyHomeMaxNumber,
        int $popularJourneyMinOccurences
    ) {
        $this->entityManager = $entityManager;
        $this->fileManager = $fileManager;
        $this->journeyRepository = $journeyRepository;
        $this->proposalManager = $proposalManager;
        $this->adManager = $adManager;
        $this->popularJourneyHomeMaxNumber = $popularJourneyHomeMaxNumber;
        $this->popularJourneyMaxNumber = $popularJourneyMaxNumber;
        $this->popularJourneyMinOccurences = $popularJourneyMinOccurences;
    }

    /**
     * Hydrate journey.
     */
    public function hydrate()
    {
        set_time_limit(60);

        $conn = $this->entityManager->getConnection();

        // delete existing journeys
        $sql = 'truncate journey;';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        // insert journeys
        $sql = "
        insert into journey (proposal_id, user_id, frequency, type, role, from_date, origin, latitude_origin, longitude_origin, destination, latitude_destination, longitude_destination, created_date)
        select p.id, 0, 0, IF(p.type=1,1,2), 0, now(), ao.address_locality, ao.latitude, ao.longitude, ad.address_locality, ad.latitude, ad.longitude, now() from address ao
        left join waypoint wo on wo.address_id = ao.id left join proposal p on wo.proposal_id = p.id left join waypoint wd on wd.proposal_id = p.id
        left join address ad on wd.address_id = ad.id
        where p.private <> 1 and ao.address_locality is not null and ao.address_locality <> '' and ad.address_locality is not null and ad.address_locality <> '' and wo.proposal_id is not null and wo.position = 0 and wd.destination = 1
        ;
        ";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        // add days, date and time
        $sql = "
        update journey j
        inner join proposal p on p.id = j.proposal_id
        inner join criteria c on c.id = p.criteria_id
        left join proposal pr on pr.id = p.proposal_linked_id
        left join criteria cr on cr.id = pr.criteria_id
        set j.frequency = c.frequency, j.role = IF(c.driver=1 AND c.passenger=1,3,IF(c.driver=1,1,2)), j.from_date = c.from_date, j.to_date = c.to_date, j.time = c.from_time,
        j.days = IF(c.frequency=2,
        (
            CONCAT(
                '{',
                CONCAT('\"mon\":\"',IF((c.mon_check=1 AND c.mon_time IS NOT NULL) OR (cr.mon_check=1 AND cr.mon_time IS NOT NULL),1,0),'\",'),
                CONCAT('\"tue\":\"',IF((c.tue_check=1 AND c.tue_time IS NOT NULL) OR (cr.tue_check=1 AND cr.tue_time IS NOT NULL),1,0),'\",'),
                CONCAT('\"wed\":\"',IF((c.wed_check=1 AND c.wed_time IS NOT NULL) OR (cr.wed_check=1 AND cr.wed_time IS NOT NULL),1,0),'\",'),
                CONCAT('\"thu\":\"',IF((c.thu_check=1 AND c.thu_time IS NOT NULL) OR (cr.thu_check=1 AND cr.thu_time IS NOT NULL),1,0),'\",'),
                CONCAT('\"fri\":\"',IF((c.fri_check=1 AND c.fri_time IS NOT NULL) OR (cr.fri_check=1 AND cr.fri_time IS NOT NULL),1,0),'\",'),
                CONCAT('\"sat\":\"',IF((c.sat_check=1 AND c.sat_time IS NOT NULL) OR (cr.sat_check=1 AND cr.sat_time IS NOT NULL),1,0),'\",'),
                CONCAT('\"sun\":\"',IF((c.sun_check=1 AND c.sun_time IS NOT NULL) OR (cr.sun_check=1 AND cr.sun_time IS NOT NULL),1,0),'\"'),
                '}'
            )
        ),null),
        j.outward_times = IF(c.frequency=2,
        (
            CONCAT(
                '{',
                CONCAT('\"mon\":',IF(c.mon_check=1 AND c.mon_time IS NOT NULL,CONCAT('\"',c.mon_time,'\"'),'null'),','),
                CONCAT('\"tue\":',IF(c.tue_check=1 AND c.tue_time IS NOT NULL,CONCAT('\"',c.tue_time,'\"'),'null'),','),
                CONCAT('\"wed\":',IF(c.wed_check=1 AND c.wed_time IS NOT NULL,CONCAT('\"',c.wed_time,'\"'),'null'),','),
                CONCAT('\"thu\":',IF(c.thu_check=1 AND c.thu_time IS NOT NULL,CONCAT('\"',c.thu_time,'\"'),'null'),','),
                CONCAT('\"fri\":',IF(c.fri_check=1 AND c.fri_time IS NOT NULL,CONCAT('\"',c.fri_time,'\"'),'null'),','),
                CONCAT('\"sat\":',IF(c.sat_check=1 AND c.sat_time IS NOT NULL,CONCAT('\"',c.sat_time,'\"'),'null'),','),
                CONCAT('\"sun\":',IF(c.sun_check=1 AND c.sun_time IS NOT NULL,CONCAT('\"',c.sun_time,'\"'),'null')),
                '}'
            )
        ),null),
        j.return_times = IF(c.frequency=2 AND j.type=2,
        (
            CONCAT(
                '{',
                CONCAT('\"mon\":',IF(cr.mon_check=1 AND cr.mon_time IS NOT NULL,CONCAT('\"',cr.mon_time,'\"'),'null'),','),
                CONCAT('\"tue\":',IF(cr.tue_check=1 AND cr.tue_time IS NOT NULL,CONCAT('\"',cr.tue_time,'\"'),'null'),','),
                CONCAT('\"wed\":',IF(cr.wed_check=1 AND cr.wed_time IS NOT NULL,CONCAT('\"',cr.wed_time,'\"'),'null'),','),
                CONCAT('\"thu\":',IF(cr.thu_check=1 AND cr.thu_time IS NOT NULL,CONCAT('\"',cr.thu_time,'\"'),'null'),','),
                CONCAT('\"fri\":',IF(cr.fri_check=1 AND cr.fri_time IS NOT NULL,CONCAT('\"',cr.fri_time,'\"'),'null'),','),
                CONCAT('\"sat\":',IF(cr.sat_check=1 AND cr.sat_time IS NOT NULL,CONCAT('\"',cr.sat_time,'\"'),'null'),','),
                CONCAT('\"sun\":',IF(cr.sun_check=1 AND cr.sun_time IS NOT NULL,CONCAT('\"',cr.sun_time,'\"'),'null')),
                '}'
            )
        ),null);
        ";

        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        // remove unwanted journeys
        $sql = 'delete from journey where frequency=0;';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        $sql = 'delete from journey where frequency=1 and from_date<CURDATE();';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        $sql = 'delete from journey where frequency<>1 and to_date<CURDATE();';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        // add user
        $sql = "
        update journey j inner join proposal p on p.id = j.proposal_id inner join user u on u.id = p.user_id set j.user_id = u.id, j.user_name =
        CONCAT(
            UPPER(LEFT(TRIM(u.given_name),1)),
            LOWER(RIGHT(TRIM(u.given_name),CHAR_LENGTH(TRIM(u.given_name))-1)),
            ' ',
            UPPER(LEFT(TRIM(u.family_name),1)),
            '.'
        ),
        j.age = TIMESTAMPDIFF(YEAR, u.birth_date, CURDATE())
        ;
        ";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }

    /**
     * Get all cities with given first letter.
     *
     * @param null|string $letter The starting letter
     *
     * @return array The cities found
     */
    public function getCities(?string $letter): array
    {
        $conn = $this->entityManager->getConnection();
        $sql = 'SELECT distinct origin as city FROM journey';
        if ($letter) {
            $sql .= " WHERE origin like '".$letter."%' order by origin asc";
        }
        $stmt = $conn->prepare($sql);
        $origins = $stmt->executeQuery()->fetchAllAssociative();
        $sql = 'SELECT distinct destination as city FROM journey';
        if ($letter) {
            $sql .= " WHERE destination like '".$letter."%' order by destination asc";
        }
        $stmt = $conn->prepare($sql);
        $destinations = $stmt->executeQuery()->fetchAllAssociative();
        $cities = array_merge($origins, $destinations);
        $result = [];
        foreach ($cities as $city) {
            $result[] = $city['city'];
        }
        $result = array_map(function ($word) {
            return ucfirst(strtolower($word));
        }, $result);
        sort($result, SORT_STRING);

        return array_unique($result);
    }

    /**
     * Get all journeys for the given origin.
     *
     * @param string $origin        The origin
     * @param string $operationName The api operation name (needed for pagination)
     * @param array  $context       The api context (needed for pagination)
     *
     * @return Journey[] The journeys found
     */
    public function getFrom(string $origin, string $operationName, array $context = []): array
    {
        // first we search the city in the journeys as origin
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT * FROM journey WHERE LOWER(LEFT(TRIM(origin),1)) like '".strtolower(substr($origin, 0, 1))."'";
        $stmt = $conn->prepare($sql);
        $journeys = $stmt->executeQuery()->fetchAllAssociative();
        // maybe we will find more than one city corresponding (accents etc...)
        $cities = [];
        foreach ($journeys as $journey) {
            if ($this->fileManager->sanitize($journey['origin'], true, false, '-') === $origin) {
                $cities[] = $journey['origin'];
            }
        }
        $cities = array_unique($cities);
        // then we search with the 'real' spellings
        return $this->journeyRepository->getAllFrom($cities, $operationName, $context);
    }

    /**
     * Get all destinations for the given origin.
     *
     * @param string $origin        The origin
     * @param string $operationName The api operation name (needed for pagination)
     * @param array  $context       The api context (needed for pagination)
     *
     * @return Journey[] The journeys found
     */
    public function getDestinationsForOrigin(string $origin): array
    {
        // first we search the city in the journeys as origin
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT * FROM journey WHERE LOWER(LEFT(TRIM(origin),1)) like '".strtolower(substr($origin, 0, 1))."'";
        $stmt = $conn->prepare($sql);
        $journeys = $stmt->executeQuery()->fetchAllAssociative();
        // maybe we will find more than one city corresponding (accents etc...)
        $cities = [];
        foreach ($journeys as $journey) {
            if ($this->fileManager->sanitize($journey['origin'], true, false, '-') === $origin) {
                $cities[] = $journey['origin'];
            }
        }
        $cities = array_unique($cities);
        // then we search the destinations with the 'real' spellings
        return $this->journeyRepository->getDestinationsForOrigin($cities);
    }

    /**
     * Get all journeys for the given destination.
     *
     * @param string $destination   The destination
     * @param string $operationName The api operation name (needed for pagination)
     * @param array  $context       The api context (needed for pagination)
     *
     * @return Journey[] The journeys found
     */
    public function getTo(string $destination, string $operationName, array $context = []): array
    {
        // first we search the city in the journeys as destination
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT * FROM journey WHERE LOWER(LEFT(TRIM(destination),1)) like '".strtolower(substr($destination, 0, 1))."'";
        $stmt = $conn->prepare($sql);
        $journeys = $stmt->executeQuery()->fetchAllAssociative();
        // maybe we will find more than one city corresponding (accents etc...)
        $cities = [];
        foreach ($journeys as $journey) {
            if ($this->fileManager->sanitize($journey['destination'], true, false, '-') === $destination) {
                $cities[] = $journey['destination'];
            }
        }
        $cities = array_unique($cities);
        // then we search with the 'real' spellings
        return $this->journeyRepository->getAllTo($cities, $operationName, $context);
    }

    /**
     * Get all origins for the given destination.
     *
     * @param string $destination   The destination
     * @param string $operationName The api operation name (needed for pagination)
     * @param array  $context       The api context (needed for pagination)
     *
     * @return Journey[] The journeys found
     */
    public function getOriginsForDestination(string $destination): array
    {
        // first we search the city in the journeys as destination
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT * FROM journey WHERE LOWER(LEFT(TRIM(destination),1)) like '".strtolower(substr($destination, 0, 1))."'";
        $stmt = $conn->prepare($sql);
        $journeys = $stmt->executeQuery()->fetchAllAssociative();
        // maybe we will find more than one city corresponding (accents etc...)
        $cities = [];
        foreach ($journeys as $journey) {
            if ($this->fileManager->sanitize($journey['destination'], true, false, '-') === $destination) {
                $cities[] = $journey['destination'];
            }
        }
        $cities = array_unique($cities);
        // then we search the destinations with the 'real' spellings
        return $this->journeyRepository->getOriginsForDestination($cities);
    }

    /**
     * Get all journeys for the given origin and destination.
     *
     * @param string $origin        The origin
     * @param string $destination   The destination
     * @param string $operationName The api operation name (needed for pagination)
     * @param array  $context       The api context (needed for pagination)
     *
     * @return Journey[] The journeys found
     */
    public function getFromTo(string $origin, string $destination, string $operationName, array $context = []): array
    {
        // first we search the city in the journeys as origin and destination
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT * FROM journey WHERE LOWER(LEFT(TRIM(origin),1)) like '".strtolower(substr($origin, 0, 1))."'";
        $stmt = $conn->prepare($sql);
        $journeysOrigin = $stmt->executeQuery()->fetchAllAssociative();
        $sql = "SELECT * FROM journey WHERE LOWER(LEFT(TRIM(destination),1)) like '".strtolower(substr($destination, 0, 1))."'";
        $stmt = $conn->prepare($sql);
        $journeysDestination = $stmt->executeQuery()->fetchAllAssociative();
        // maybe we will find more than one city corresponding (accents etc...)
        $citiesOrigin = [];
        foreach ($journeysOrigin as $journey) {
            if ($this->fileManager->sanitize($journey['origin'], true, false, '-') === $origin) {
                $citiesOrigin[] = $journey['origin'];
            }
        }
        $citiesDestination = [];
        foreach ($journeysDestination as $journey) {
            if ($this->fileManager->sanitize($journey['destination'], true, false, '-') === $destination) {
                $citiesDestination[] = $journey['destination'];
            }
        }
        $citiesOrigin = array_unique($citiesOrigin);
        $citiesDestination = array_unique($citiesDestination);
        // then we search with the 'real' spellings
        return $this->journeyRepository->getAllFromTo($citiesOrigin, $citiesDestination, $operationName, $context);
    }

    /**
     * Return de most popular journeys (see .env for the max number and criteria).
     *
     * @param bool $home true if it's for home
     *
     * @return null|Journey[]
     */
    public function getPopularJourneys(bool $home = false): ?array
    {
        if (!$home) {
            return $this->journeyRepository->getPopularJourneys($this->popularJourneyMinOccurences, $this->popularJourneyMaxNumber);
        }
        // For Home, we are inducing a little bit of randomization. We take x times (see Journey.php constant) the max home number of items
        // we shuffle it and return the right amount of journeys
        $journeys = $this->journeyRepository->getPopularJourneys($this->popularJourneyMinOccurences, $this->popularJourneyHomeMaxNumber * Journey::POPULAR_RANDOMIZATION_FACTOR);
        shuffle($journeys);

        return array_slice($journeys, 0, $this->popularJourneyHomeMaxNumber);
    }

    public function findCarpools(int $proposalId, User $user)
    {
        // We get the original Proposal
        $proposal = $this->proposalManager->get($proposalId);
        if (!$proposal) {
            throw new LogicException('Unknown Proposal');
        }

        // Make a new "search" Ad with this Proposal. Same structure that a simple search.
        $ad = new Ad();
        $ad->setUser($user);
        $ad->setUserId($user->getId());

        // It's a search without a specific role
        $ad->setSearch(true);
        $ad->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setOneWay($proposal->getProposalLinked() ? false : true);

        // In a Proposal, we have true waypoint. For an Ad, outwardWaypoints are in fact an array of Address
        $outwardWaypoint = [];
        $waypointsProposal = $proposal->getWaypoints();
        foreach ($waypointsProposal as $waypointP) {
            $outwardWaypoint[] = clone $waypointP->getAddress();
        }
        $ad->setOutwardWaypoints($outwardWaypoint);

        // Like in a simple search, we use "now" as outwardDateTime
        $ad->setOutwardDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $ad->setFilters(['page' => 1]);
        $ad->setPaused(false);

        $ad = $this->adManager->createAd($ad, true, false);

        $journey = new Journey();
        $journey->setProposalId($ad->getId());

        return $journey;
    }
}
