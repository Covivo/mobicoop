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

namespace App\Carpool\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Dynamic;
use App\Carpool\Entity\Position;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Exception\DynamicException;
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\GeoTools;
use App\User\Exception\UserNotFoundException;
use App\User\Service\UserManager;
use CrEOF\Spatial\PHP\Types\Geography\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Dynamic ad manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class DynamicManager
{
    const REACH_DISTANCE = 500; // distance in metres between the last position and a waypoint to consider it reached

    private $entityManager;
    private $proposalManager;
    private $userManager;
    private $resultManager;
    private $geoTools;
    private $geoRouter;
    private $params;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalManager $proposalManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalManager $proposalManager, UserManager $userManager, ResultManager $resultManager, GeoTools $geoTools, GeoRouter $geoRouter, array $params, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->resultManager = $resultManager;
        $this->geoTools = $geoTools;
        $this->geoRouter = $geoRouter;
        $this->params = $params;
        $this->logger = $logger;
    }

    /**
     * Get a dynamic ad.
     *
     * @param integer $id   The dynamic ad id.
     * @return Dynamic      The dynamic ad.
     */
    public function getDynamic(int $id)
    {
        $proposal = $this->proposalManager->get($id);
        $dynamic = new Dynamic();
        $dynamic->setProposal($proposal);
        $dynamic->setUser($proposal->getUser());
        $dynamic->setRole($proposal->getCriteria()->isDriver() ? Dynamic::ROLE_DRIVER : Dynamic::ROLE_PASSENGER);
        $dynamic->setId($proposal->getId());
        return $dynamic;
    }

    /**
     * Create a dynamic ad.
     *
     * @param Dynamic $dynamic  The dynamic ad to create
     * @return Dynamic      The created Dynamic ad.
     */
    public function createDynamic(Dynamic $dynamic)
    {
        // set User
        if (is_null($dynamic->getUser())) {
            // userId must be set
            if (is_null($dynamic->getUserId())) {
                throw new DynamicException('UserId must be provided');
            }
            if (!$user = $this->userManager->getUser($dynamic->getUserId())) {
                throw new UserNotFoundException('User #' . $dynamic->getUserId() . ' not found');
            }
            $dynamic->setUser($user);
        }
        // set Seats
        if (is_null($dynamic->getSeats())) {
            if ($dynamic->getRole() == Dynamic::ROLE_DRIVER) {
                $dynamic->setSeats($this->params['defaultSeatsDriver']);
            } else {
                $dynamic->setSeats($this->params['defaultSeatsPassenger']);
            }
        }
        // set Date
        $dynamic->setDate(new \DateTime('UTC'));

        // creation of the proposal
        $this->logger->info("DynamicManager : start " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        $proposal = new Proposal();
        $criteria = new Criteria();
        $position = new Position();
        $direction = new Direction();

        // special dynamic properties
        $proposal->setDynamic(true);
        $proposal->setActive(true);
        $proposal->setType(Proposal::TYPE_ONE_WAY);

        // comment
        $proposal->setComment($dynamic->getComment());
        
        // criteria

        // driver / passenger / seats
        $criteria->setDriver($dynamic->getRole() == Dynamic::ROLE_DRIVER);
        $criteria->setPassenger($dynamic->getRole() == Dynamic::ROLE_PASSENGER);
        $criteria->setSeatsDriver($dynamic->getRole() == Dynamic::ROLE_DRIVER ? $dynamic->getSeats() : 0);
        $criteria->setSeatsPassenger($dynamic->getRole() == Dynamic::ROLE_PASSENGER ? $dynamic->getSeats() : 0);
        
        // prices
        $criteria->setPriceKm($dynamic->getPriceKm());
        if ($dynamic->getRole() == Dynamic::ROLE_DRIVER) {
            $criteria->setDriverPrice($dynamic->getPrice());
        }
        if ($dynamic->getRole() == Dynamic::ROLE_PASSENGER) {
            $criteria->setPassengerPrice($dynamic->getPrice());
        }

        // dates and times

        // if the date is not set we use the current date
        $criteria->setFromDate($dynamic->getDate());
        $criteria->setFromTime($dynamic->getDate());
        $criteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);

        // waypoints
        foreach ($dynamic->getWaypoints() as $waypointPosition => $point) {
            $waypoint = new Waypoint();
            $address = new Address();
            if (isset($point['houseNumber'])) {
                $address->setHouseNumber($point['houseNumber']);
            }
            if (isset($point['street'])) {
                $address->setStreet($point['street']);
            }
            if (isset($point['streetAddress'])) {
                $address->setStreetAddress($point['streetAddress']);
            }
            if (isset($point['postalCode'])) {
                $address->setPostalCode($point['postalCode']);
            }
            if (isset($point['subLocality'])) {
                $address->setSubLocality($point['subLocality']);
            }
            if (isset($point['addressLocality'])) {
                $address->setAddressLocality($point['addressLocality']);
            }
            if (isset($point['localAdmin'])) {
                $address->setLocalAdmin($point['localAdmin']);
            }
            if (isset($point['county'])) {
                $address->setCounty($point['county']);
            }
            if (isset($point['macroCounty'])) {
                $address->setMacroCounty($point['macroCounty']);
            }
            if (isset($point['region'])) {
                $address->setRegion($point['region']);
            }
            if (isset($point['macroRegion'])) {
                $address->setMacroRegion($point['macroRegion']);
            }
            if (isset($point['addressCountry'])) {
                $address->setAddressCountry($point['addressCountry']);
            }
            if (isset($point['countryCode'])) {
                $address->setCountryCode($point['countryCode']);
            }
            if (isset($point['latitude'])) {
                $address->setLatitude($point['latitude']);
            }
            if (isset($point['longitude'])) {
                $address->setLongitude($point['longitude']);
            }
            if (isset($point['elevation'])) {
                $address->setElevation($point['elevation']);
            }
            if (isset($point['name'])) {
                $address->setName($point['name']);
            }
            if (isset($point['home'])) {
                $address->setHome($point['home']);
            }
            $waypoint->setAddress($address);
            $waypoint->setPosition($waypointPosition);
            $waypoint->setDestination($waypointPosition == count($dynamic->getWaypoints())-1);
            $proposal->addWaypoint($waypoint);

            if ($waypointPosition == 0) {
                // init position => the origin of the proposal
                $position->setAddress(clone $address);
                $waypoint->setReached(true);

                // direction
                $direction->setPoints([$address]);
                // $direction->setBboxMinLon($address->getLongitude());
                // $direction->setBboxMinLat($address->getLatitude());
                // $direction->setBboxMaxLon($address->getLongitude());
                // $direction->setBboxMaxLat($address->getLatitude());
                $direction->setDistance(0);
                $direction->setDuration(0);
                $direction->setDetail("");
                $direction->setSnapped("");
                $direction->setFormat("Dynamic");
                $position->setDirection($direction);
            }
        }

        $proposal->setCriteria($criteria);
        
        $proposal = $this->proposalManager->prepareProposal($proposal);
        $this->logger->info("DynamicManager : end creating ad " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $this->entityManager->persist($proposal);
        $this->logger->info("DynamicManager : end persisting ad " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        $position->setProposal($proposal);
        $this->entityManager->persist($position);
        $this->entityManager->flush();

        $dynamic->setId($proposal->getId());
        return $dynamic;
    }

    /**
     * Update a dynamic ad => update the current position
     *
     * @param int $id           The id of the dynamic ad to update
     * @param Dynamic $dynamic  The dynamic ad data to make the update
     * @return Dynamic      The updated Dynamic ad.
     */
    public function updateDynamic(int $id, Dynamic $dynamicData)
    {
        // we get the original dynamic ad
        $dynamic = $this->getDynamic($id);

        // dynamic ad ?
        if (!$dynamic->getProposal()->isDynamic()) {
            throw new DynamicException('This ad is not dynamic !');
        }

        // still active ?
        if (!$dynamic->getProposal()->isActive()) {
            throw new DynamicException('This ad is not active !');
        }

        // we update the position
        $dynamic->setLatitude($dynamicData->getLatitude());
        $dynamic->setLongitude($dynamicData->getLongitude());

        $address = new Address();
        $address->setLatitude($dynamic->getLatitude());
        $address->setLongitude($dynamic->getLongitude());
        $dynamic->getProposal()->getPosition()->setAddress($address);

        // we search if we have reached a waypoint
        foreach ($dynamic->getProposal()->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if (!$waypoint->isReached()) {
                if ($this->geoTools->haversineGreatCircleDistance($address->getLatitude(), $address->getLongitude(), $waypoint->getAddress()->getLatitude(), $waypoint->getAddress()->getLongitude())<self::REACH_DISTANCE) {
                    $waypoint->setReached(true);
                    // destination ? stop the dynamic !
                    if ($waypoint->isDestination()) {
                        $dynamic->getProposal()->setActive(false);
                    }
                    $this->entityManager->persist($waypoint);
                }
            }
        }

        // we update the direction
        $points = $dynamic->getProposal()->getPosition()->getDirection()->getGeoJsonDetail()->getPoints();
        $points[] = $address;

        // $addresses = [];
        // foreach ($points as $point) {
        //     $waypoint = new Address();
        //     $waypoint->setLatitude($point->getLatitude());
        //     $waypoint->setLongitude($point->getLongitude());
        //     $addresses[] = $waypoint;
        // }
        // $routes = $this->geoRouter->getRoutes($addresses);
        // $dynamic->getProposal()->getPosition()->setDirection($routes[0]);

        $dynamic->getProposal()->getPosition()->getDirection()->setSaveGeoJson(true);
        $dynamic->getProposal()->getPosition()->getDirection()->setDetailUpdatable(true);
        $dynamic->getProposal()->getPosition()->getDirection()->setPoints($points);
        // here we force the update because none of the properties from the entity could be updated, but we need to compute GeoJson
        $dynamic->getProposal()->getPosition()->getDirection()->setUpdatedDate(new \DateTime("UTC"));

        $this->entityManager->persist($dynamic->getProposal());
        $this->entityManager->flush();

        return $dynamic;
    }
}
