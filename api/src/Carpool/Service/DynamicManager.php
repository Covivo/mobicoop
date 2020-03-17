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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Dynamic;
use App\Carpool\Entity\DynamicAsk;
use App\Carpool\Entity\Position;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Exception\DynamicException;
use App\Carpool\Repository\MatchingRepository;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Communication\Service\InternalMessageManager;
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\GeoTools;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Dynamic ad manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class DynamicManager
{
    private $entityManager;
    private $proposalManager;
    private $userManager;
    private $resultManager;
    private $geoTools;
    private $geoRouter;
    private $params;
    private $logger;
    private $matchingRepository;
    private $internalMessageManager;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalManager $proposalManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProposalManager $proposalManager,
        UserManager $userManager,
        ResultManager $resultManager,
        GeoTools $geoTools,
        GeoRouter $geoRouter,
        array $params,
        LoggerInterface $logger,
        MatchingRepository $matchingRepository,
        InternalMessageManager $internalMessageManager
    ) {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->resultManager = $resultManager;
        $this->geoTools = $geoTools;
        $this->geoRouter = $geoRouter;
        $this->params = $params;
        $this->logger = $logger;
        $this->matchingRepository = $matchingRepository;
        $this->internalMessageManager = $internalMessageManager;
    }




    /****************
     *  DYNAMIC AD  *
     ****************/

    /**
     * Get a dynamic ad.
     *
     * @param integer $id   The dynamic ad id.
     * @return Dynamic      The dynamic ad.
     */
    public function getDynamic(int $id)
    {
        if (!$proposal = $this->proposalManager->get($id)) {
            throw new DynamicException("Dynamic ad not found");
        }
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
        // todo : check if the user has already a dynamic ad pending

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

        $proposal->setUser($dynamic->getUser());

        // special dynamic properties
        $proposal->setDynamic(true);
        $proposal->setActive(true);
        $proposal->setFinished(false);
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

        // we use the current date
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
                // we double this waypoint : it will be a floating waypoint that will reflect the current position of the user (useful for matching)
                // the position of this waypoint will always be 0
                $floatingWaypoint = clone $waypoint;
                $proposal->addWaypoint($floatingWaypoint);
                $position->setAddress(clone $address);
                $position->setPoints([$position->getAddress()]);
                $waypoint->setReached(true);

                // direction
                $direction->setPoints([$address]);
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

        // we compute the results
        
        // default order
        $dynamic->setFilters([
            'order'=>[
                'criteria'=>'date',
                'value'=>'ASC'
            ]
        
        ]);

        $dynamic->setResults(
            $this->resultManager->orderResults(
                $this->resultManager->filterResults(
                    $this->resultManager->createAdResults($proposal),
                    $dynamic->getFilters()
                ),
                $dynamic->getFilters()
            )
        );

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

        // not finished ?
        if ($dynamic->getProposal()->isFinished()) {
            throw new DynamicException('This ad is finished !');
        }

        // TODO : check if the position is valid :
        // - valid coordinates
        // - not too far from the last point
        // - etc...

        // we update the position
        $dynamic->setLatitude($dynamicData->getLatitude());
        $dynamic->setLongitude($dynamicData->getLongitude());

        // update the address geographic coordinates
        $dynamic->getProposal()->getPosition()->getAddress()->setLongitude($dynamic->getLongitude());
        $dynamic->getProposal()->getPosition()->getAddress()->setLatitude($dynamic->getLatitude());

        // we search if we have reached a waypoint
        foreach ($dynamic->getProposal()->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if (!$waypoint->isReached() && $waypoint->getPosition()>0) {
                // position>0 to exclude the floating waypoint
                if ($this->geoTools->haversineGreatCircleDistance($dynamic->getLatitude(), $dynamic->getLongitude(), $waypoint->getAddress()->getLatitude(), $waypoint->getAddress()->getLongitude())<$this->params['dynamicReachedDistance']) {
                    $waypoint->setReached(true);
                    // destination ? stop the dynamic !
                    if ($waypoint->isDestination()) {
                        $dynamic->getProposal()->setFinished(true);
                    }
                    $this->entityManager->persist($waypoint);
                    $this->entityManager->flush();
                }
            }
            if ($waypoint->getPosition() == 0) {
                // update the floating waypoint address
                $waypoint->getAddress()->setAddressLocality(""); // for now remove the locality just for testing purpose !
                $waypoint->getAddress()->setLongitude($dynamic->getLongitude());
                $waypoint->getAddress()->setLatitude($dynamic->getLatitude());
                $this->entityManager->persist($waypoint);
                $this->entityManager->flush();
            }
        }

        // we update the direction of the position

        // first we get all the past points that are stored as a linestring in the geoJsonPoints property
        $points = $dynamic->getProposal()->getPosition()->getGeoJsonPoints()->getPoints();
        // then we add the last point (must be an object that have longitude and latitude properties, like an Address)
        $address = new Address();
        $address->setLatitude($dynamic->getLatitude());
        $address->setLongitude($dynamic->getLongitude());
        $points[] = $address;
        $dynamic->getProposal()->getPosition()->setPoints($points);
        // here we force the update because maybe none of the properties from the entity could be updated, but we need to compute GeoJson
        $dynamic->getProposal()->getPosition()->setAutoUpdatedDate();

        // we create an array of Addresses to compute the real direction using the georouter
        $addresses = [];
        foreach ($points as $point) {
            $waypoint = new Address();
            $waypoint->setLatitude($point->getLatitude());
            $waypoint->setLongitude($point->getLongitude());
            $addresses[] = $waypoint;
        }
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            // we have a direction
            /**
             * @var Direction $newDirection
             */
            $newDirection = $routes[0];
            $dynamic->getProposal()->getPosition()->getDirection()->setDistance($newDirection->getDistance());
            $dynamic->getProposal()->getPosition()->getDirection()->setDuration($newDirection->getDuration());
            $dynamic->getProposal()->getPosition()->getDirection()->setAscend($newDirection->getAscend());
            $dynamic->getProposal()->getPosition()->getDirection()->setDescend($newDirection->getDescend());
            $dynamic->getProposal()->getPosition()->getDirection()->setBboxMinLon($newDirection->getBboxMinLon());
            $dynamic->getProposal()->getPosition()->getDirection()->setBboxMinLat($newDirection->getBboxMinLat());
            $dynamic->getProposal()->getPosition()->getDirection()->setBboxMaxLon($newDirection->getBboxMaxLon());
            $dynamic->getProposal()->getPosition()->getDirection()->setBboxMaxLat($newDirection->getBboxMaxLat());
            $dynamic->getProposal()->getPosition()->getDirection()->setDetail($newDirection->getDetail());
            $dynamic->getProposal()->getPosition()->getDirection()->setFormat($newDirection->getFormat());
            $dynamic->getProposal()->getPosition()->getDirection()->setSnapped($newDirection->getSnapped());
            $dynamic->getProposal()->getPosition()->getDirection()->setBearing($newDirection->getBearing());
            // the following is needed to compute the geoJson in the direction automatic update trigger
            $dynamic->getProposal()->getPosition()->getDirection()->setPoints($routes[0]->getPoints());
            $dynamic->getProposal()->getPosition()->getDirection()->setSaveGeoJson(true);
            $dynamic->getProposal()->getPosition()->getDirection()->setDetailUpdatable(true);
            // here we force the update because maybe none of the properties from the entity could be updated, but we need to compute GeoJson
            $dynamic->getProposal()->getPosition()->getDirection()->setAutoUpdatedDate();
        } else {
            // the last point introduced an error as we couldn't compute the direction !
            // we send an exeption...
            throw new DynamicException("Bad geographic position... Point ignored !");
        }

        // update the matchings
        // (and update the proposal direction (= direction from the current point to the destination))
        $dynamic->setProposal($this->proposalManager->updateMatchingsForProposal($dynamic->getProposal(), $address));

        // persist the updates
        $this->entityManager->persist($dynamic->getProposal());
        $this->entityManager->flush();

        // we get the asks related to the dynamic ad
        $asks = [];
        if ($dynamic->getRole() == Dynamic::ROLE_DRIVER) {
            // the user is driver, we search the matching requests
            foreach ($dynamic->getProposal()->getMatchingRequests() as $matching) {
                echo $matching->getId() . " ";
                foreach ($matching->getAsks() as $ask) {
                    // there's an ask, the initiator of the ask is the passenger => the user of the ask
                    $asks[] = [
                        'id' => $ask->getId(),
                        'status' => $ask->getStatus(),
                        'user' => [
                            'givenName' => $ask->getUser()->getGivenName(),
                            'shortFamilyName' => $ask->getUser()->getShortFamilyName(),
                        ],
                        'messages' => $this->getThread($ask)
                    ];
                }
            }
        } else {
            // the user is driver, we search the matching offers
            foreach ($dynamic->getProposal()->getMatchingOffers() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    // there's an ask, the recipient of the ask is the driver => the userRelated of the ask
                    $asks[] = [
                        'id' => $ask->getId(),
                        'status' => $ask->getStatus(),
                        'user' => [
                            'givenName' => $ask->getUserRelated()->getGivenName(),
                            'shortFamilyName' => $ask->getUserRelated()->getShortFamilyName(),
                        ]
                    ];
                }
            }
        }
        $dynamic->setAsks($asks);

        // we compute the results

        // default order
        $dynamic->setFilters([
            'order'=>[
                'criteria'=>'date',
                'value'=>'ASC'
            ]
        
        ]);

        $dynamic->setResults(
            $this->resultManager->orderResults(
                $this->resultManager->filterResults(
                    $this->resultManager->createAdResults($dynamic->getProposal()),
                    $dynamic->getFilters()
                ),
                $dynamic->getFilters()
            )
        );

        return $dynamic;
    }




    /****************
     *  DYNAMIC ASK *
     ****************/

    /**
     * Create an ask for a dynamic ad.
     *
     * @param DynamicAsk    $dynamic    The ask to create
     * @return DynamicAsk               The created ask.
     */
    public function createDynamicAsk(DynamicAsk $dynamicAsk)
    {
        // todo : check that another ask is not already made

        $ask = new Ask();
        $ask->setStatus(Ask::STATUS_PENDING_AS_PASSENGER);
        $ask->setType(Proposal::TYPE_ONE_WAY);
        $ask->setUser($dynamicAsk->getUser());
        $matching = $this->matchingRepository->find($dynamicAsk->getMatchingId());
        $ask->setMatching($matching);
        $ask->setUserRelated($matching->getProposalOffer()->getUser());

        // we use the matching criteria
        $criteria = clone $matching->getCriteria();
        $ask->setCriteria($criteria);
        
        // we use the matching waypoints
        $waypoints = $matching->getWaypoints();
        foreach ($waypoints as $waypoint) {
            $newWaypoint = clone $waypoint;
            $ask->addWaypoint($newWaypoint);
        }

        // Ask History
        $askHistory = new AskHistory();
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $ask->addAskHistory($askHistory);

        // message
        if (!is_null($dynamicAsk->getMessage())) {
            $message = new Message();
            $message->setUser($dynamicAsk->getUser());
            $message->setText($dynamicAsk->getMessage());
            $recipient = new Recipient();
            $recipient->setUser($ask->getUserRelated());
            $recipient->setStatus(Recipient::STATUS_PENDING);
            $message->addRecipient($recipient);
            $this->entityManager->persist($message);
            $askHistory->setMessage($message);
        }

        $this->entityManager->persist($ask);
        $this->entityManager->flush($ask);
        
        // todo : dispatch en event ?
        return $ask;
    }

    /**
     * Update an ask for a dynamic ad
     *
     * @param int           $id         The id of the ask to update
     * @param DynamicAsk    $dynamic    The ask data to make the update
     * @return DynamicAsk   The updated ask.
     */
    public function updateDynamicAsk(int $id, DynamicAsk $dynamicAskData)
    {
    }

    /**
     * Get all the messages related to an ask
     *
     * @param Ask $ask  The ask
     * @return array The messages
     */
    private function getThread(Ask $ask)
    {
        $thread = [];
        if (!is_null($ask->getAskHistories()[0]->getMessage())) {
            $messages = $this->internalMessageManager->getCompleteThread($ask->getAskHistories()[0]->getMessage()->getId());
            foreach ($messages as $message) {
                /**
                 * @var Message $message
                 */
                $thread[] = [
                    "text" => $message->getText(),
                    "user" => [
                        'givenName' => $message->getUser()->getGivenName(),
                        'shortFamilyName' => $message->getUser()->getShortFamilyName(),
                    ]
                ];
            }
        }
        return $thread;
    }
}
