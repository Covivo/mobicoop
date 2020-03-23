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
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Dynamic;
use App\Carpool\Entity\DynamicAsk;
use App\Carpool\Entity\DynamicProof;
use App\Carpool\Entity\Matching;
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
use App\Carpool\Entity\Result;
use App\Carpool\Repository\AskHistoryRepository;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Geography\Service\GeoSearcher;

/**
 * Dynamic ad manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class DynamicManager
{
    private $entityManager;
    private $proposalManager;
    private $proposalMatcher;
    private $askManager;
    private $resultManager;
    private $geoTools;
    private $geoRouter;
    private $geoSearcher;
    private $params;
    private $logger;
    private $matchingRepository;
    private $askRespository;
    private $askHistoryRepository;
    private $carpoolProofRepository;
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
        ProposalMatcher $proposalMatcher,
        AskManager $askManager,
        ResultManager $resultManager,
        GeoTools $geoTools,
        GeoRouter $geoRouter,
        GeoSearcher $geoSearcher,
        array $params,
        LoggerInterface $logger,
        MatchingRepository $matchingRepository,
        AskRepository $askRespository,
        AskHistoryRepository $askHistoryRepository,
        CarpoolProofRepository $carpoolProofRepository,
        InternalMessageManager $internalMessageManager
    ) {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->askManager = $askManager;
        $this->resultManager = $resultManager;
        $this->geoTools = $geoTools;
        $this->geoRouter = $geoRouter;
        $this->geoSearcher = $geoSearcher;
        $this->params = $params;
        $this->logger = $logger;
        $this->matchingRepository = $matchingRepository;
        $this->askRespository = $askRespository;
        $this->askHistoryRepository = $askHistoryRepository;
        $this->carpoolProofRepository = $carpoolProofRepository;
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
        // first we check if the user has already a dynamic ad pending
        if ($this->proposalManager->hasPendingDynamic($dynamic->getUser())) {
            throw new DynamicException("This user has already a pending dynamic ad");
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
            $address = $this->getAddressByPartialAddressArray($point);
            $waypoint->setAddress($address);
            $waypoint->setPosition($waypointPosition);
            $waypoint->setDestination($waypointPosition == count($dynamic->getWaypoints())-1);
            $proposal->addWaypoint($waypoint);

            if ($waypointPosition == 0) {
                // init position => the origin of the proposal
                // we double this waypoint : it will be a floating waypoint that will reflect the current position of the user (useful for matching)
                // the position of this waypoint will always be 0
                $floatingWaypoint = clone $waypoint;
                $floatingWaypoint->setFloating(true);
                $proposal->addWaypoint($floatingWaypoint);
                $position->setWaypoint($floatingWaypoint);
                $position->setPoints([$address]);
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
     * @param int $id               The id of the dynamic ad to update
     * @param Dynamic $dynamicData  The dynamic ad data to make the update
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
        $dynamic->getProposal()->getPosition()->getWaypoint()->getAddress()->setLongitude($dynamic->getLongitude());
        $dynamic->getProposal()->getPosition()->getWaypoint()->getAddress()->setLatitude($dynamic->getLatitude());

        // we search if we have reached a waypoint
        foreach ($dynamic->getProposal()->getWaypoints() as $waypoint) {
            /**
             * @var Waypoint $waypoint
             */
            if (!$waypoint->isReached() && !$waypoint->isFloating()) {
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
            if ($waypoint->isFloating()) {
                // update the floating waypoint address
                // we reverse geocode, to get a full address
                if ($addresses = $this->geoSearcher->reverseGeoCode($dynamic->getLatitude(), $dynamic->getLongitude())) {
                    if (count($addresses)>0) {
                        $reversedGeocodeAddress = $addresses[0];
                    }
                }
                $waypoint->getAddress()->setLongitude($dynamic->getLongitude());
                $waypoint->getAddress()->setLatitude($dynamic->getLatitude());
                if (isset($reversedGeocodeAddress)) {
                    $waypoint->getAddress()->setStreetAddress($reversedGeocodeAddress->getStreetAddress());
                    $waypoint->getAddress()->setPostalCode($reversedGeocodeAddress->getPostalCode());
                    $waypoint->getAddress()->setAddressLocality($reversedGeocodeAddress->getAddressLocality());
                    $waypoint->getAddress()->setAddressCountry($reversedGeocodeAddress->getAddressCountry());
                    $waypoint->getAddress()->setElevation($reversedGeocodeAddress->getElevation());
                    $waypoint->getAddress()->setHouseNumber($reversedGeocodeAddress->getHouseNumber());
                    $waypoint->getAddress()->setStreet($reversedGeocodeAddress->getStreet());
                    $waypoint->getAddress()->setSubLocality($reversedGeocodeAddress->getSubLocality());
                    $waypoint->getAddress()->setLocalAdmin($reversedGeocodeAddress->getLocalAdmin());
                    $waypoint->getAddress()->setCounty($reversedGeocodeAddress->getCounty());
                    $waypoint->getAddress()->setMacroCounty($reversedGeocodeAddress->getMacroCounty());
                    $waypoint->getAddress()->setRegion($reversedGeocodeAddress->getRegion());
                    $waypoint->getAddress()->setMacroRegion($reversedGeocodeAddress->getMacroRegion());
                    $waypoint->getAddress()->setCountryCode($reversedGeocodeAddress->getCountryCode());
                    $waypoint->getAddress()->setVenue($reversedGeocodeAddress->getVenue());
                }
                
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

        // we get the asks related to the dynamic ad
        // we include the corresponding result
        $asks = [];
        if ($dynamic->getRole() == Dynamic::ROLE_DRIVER) {
            // the user is driver, we search the matching requests
            foreach ($dynamic->getProposal()->getMatchingRequests() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    // there's an ask, the initiator of the ask is the passenger => the user of the ask
                    // if the pickup hasn't been made yet, we compute the direction between the driver and the passenger
                    $pickUpDuration = null;
                    $pickUpDistance = null;
                    if (is_null($ask->getCarpoolProof())) {
                        $addresses = [];
                        $addresses[] = $matching->getProposalOffer()->getPosition()->getWaypoint()->getAddress();
                        $addresses[] = $matching->getProposalRequest()->getPosition()->getWaypoint()->getAddress();
                        if ($routes = $this->geoRouter->getRoutes($addresses)) {
                            $pickUpDuration = $routes[0]->getDuration();
                            $pickUpDistance = $routes[0]->getDistance();
                        }
                    }
                    $asks[] = [
                        'id' => $ask->getId(),
                        'status' => $ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER ? DynamicAsk::STATUS_PENDING : ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER ? DynamicAsk::STATUS_ACCEPTED : DynamicAsk::STATUS_DECLINED),
                        'user' => [
                            'id' => $ask->getUser()->getId(),
                            'givenName' => $ask->getUser()->getGivenName(),
                            'shortFamilyName' => $ask->getUser()->getShortFamilyName(),
                            'position' => $matching->getProposalRequest()->getPosition()->getWaypoint()->getAddress()
                        ],
                        'result' => $this->getResult($matching, $dynamic->getResults()),
                        'messages' => $this->getThread($ask),
                        'priceKm' => $ask->getCriteria()->getPriceKm(),
                        'price' => $ask->getCriteria()->getPassengerComputedRoundedPrice(),
                        'duration' => $matching->getDropOffDuration()-$matching->getPickUpDuration(),
                        'pickUpDuration' => $pickUpDuration,
                        'pickUpDistance' => $pickUpDistance
                    ];
                }
            }
        } else {
            // the user is passenger, we search the matching offers
            foreach ($dynamic->getProposal()->getMatchingOffers() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    // there's an ask, the recipient of the ask is the driver => the userRelated of the ask
                    // if the pickup hasn't been made yet, we compute the direction between the driver and the passenger
                    $pickUpDuration = null;
                    $pickUpDistance = null;
                    if (is_null($ask->getCarpoolProof())) {
                        $addresses = [];
                        $addresses[] = $matching->getProposalOffer()->getPosition()->getWaypoint()->getAddress();
                        $addresses[] = $matching->getProposalRequest()->getPosition()->getWaypoint()->getAddress();
                        if ($routes = $this->geoRouter->getRoutes($addresses)) {
                            $pickUpDuration = $routes[0]->getDuration();
                            $pickUpDistance = $routes[0]->getDistance();
                        }
                    }
                    $asks[] = [
                        'id' => $ask->getId(),
                        'status' => $ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER ? DynamicAsk::STATUS_PENDING : ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER ? DynamicAsk::STATUS_ACCEPTED : DynamicAsk::STATUS_DECLINED),
                        'user' => [
                            'id' => $ask->getUserRelated()->getId(),
                            'givenName' => $ask->getUserRelated()->getGivenName(),
                            'shortFamilyName' => $ask->getUserRelated()->getShortFamilyName(),
                            'position' => $matching->getProposalOffer()->getPosition()->getWaypoint()->getAddress()
                        ],
                        'result' => $this->getResult($matching, $dynamic->getResults()),
                        'messages' => $this->getThread($ask),
                        'priceKm' => $ask->getCriteria()->getPriceKm(),
                        'price' => $ask->getCriteria()->getPassengerComputedRoundedPrice(),
                        'duration' => $matching->getDropOffDuration()-$matching->getPickUpDuration(),
                        'pickUpDuration' => $pickUpDuration,
                        'pickUpDistance' => $pickUpDistance
                    ];
                }
            }
        }
        $dynamic->setAsks($asks);

        return $dynamic;
    }

    /**
     * Get a result from a Matching
     *
     * @param Matching $matching    The matching
     * @param array $results        The array of results
     * @return Result|null          The result found
     */
    private function getResult(Matching $matching, array $results)
    {
        foreach ($results as $result) {
            /**
             * @var Result $result
             */
            if (!is_null($result->getResultDriver()) && !is_null($result->getResultDriver()->getOutward())) {
                if ($result->getResultDriver()->getOutward()->getMatchingId() == $matching->getId()) {
                    return $result;
                }
            }
            if (!is_null($result->getResultPassenger()) && !is_null($result->getResultPassenger()->getOutward())) {
                if ($result->getResultPassenger()->getOutward()->getMatchingId() == $matching->getId()) {
                    return $result;
                }
            }
        }
        return null;
    }




    /****************
     *  DYNAMIC ASK *
     ****************/


    /**
     * Get a dynamic ask.
     *
     * @param integer $id   The dynamic ask id.
     * @return DynamicAsk   The dynamic ask.
     */
    public function getDynamicAsk(int $id)
    {
        if (!$ask = $this->askRespository->find($id)) {
            throw new DynamicException("Dynamic ask not found");
        }
        $dynamicAsk = new DynamicAsk();
        $dynamicAsk->setId($ask->getId());
        $dynamicAsk->setUser($ask->getUser());
        $dynamicAsk->setCarpooler($ask->getUserRelated());
        $dynamicAsk->setMatchingId($ask->getMatching()->getId());
        $dynamicAsk->setStatus($ask->getStatus());
        return $dynamicAsk;
    }

    /**
     * Create an ask for a dynamic ad.
     *
     * @param DynamicAsk    $dynamicAsk The ask to create
     * @return DynamicAsk               The created ask.
     */
    public function createDynamicAsk(DynamicAsk $dynamicAsk)
    {
        // only the passenger can create an ask
        $matching = $this->matchingRepository->find($dynamicAsk->getMatchingId());
        if ($dynamicAsk->getUser()->getId() != $matching->getProposalRequest()->getUser()->getId()) {
            throw new DynamicException("Only the passenger can create the dynamic ask");
        }

        // check that another ask is not already made
        if ($this->askManager->hasPendingDynamicAsk($dynamicAsk->getUser())) {
            throw new DynamicException("This user has already a pending dynamic ask");
        }

        // check that another ask has not been made on this particular ad
        if ($this->askManager->hasRefusedDynamicAsk($dynamicAsk->getUser(), $matching)) {
            throw new DynamicException("This user has already a refused dynamic ask on this matching");
        }

        $ask = new Ask();
        $ask->setStatus(Ask::STATUS_PENDING_AS_PASSENGER);
        $ask->setType(Proposal::TYPE_ONE_WAY);
        $ask->setUser($dynamicAsk->getUser());
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

        // disable the passenger dynamic ad to avoid asks to other drivers
        $matching->getProposalRequest()->setActive(false);
        $this->entityManager->persist($matching->getProposalRequest());

        $this->entityManager->flush();
        
        // todo : dispatch en event ?

        $dynamicAsk->setId($ask->getId());
        $dynamicAsk->setStatus(DynamicAsk::STATUS_PENDING);

        return $dynamicAsk;
    }

    /**
     * Update an ask for a dynamic ad => only by the driver
     *
     * @param int           $id             The id of the ask to update
     * @param DynamicAsk    $dynamicAskData The ask data to make the update
     * @return DynamicAsk   The updated ask.
     */
    public function updateDynamicAsk(int $id, DynamicAsk $dynamicAskData)
    {
        // get the ask
        $ask = $this->askRespository->find($id);

        // only the driver can update an ask
        if ($ask->getUserRelated()->getId() != $dynamicAskData->getUser()->getId()) {
            throw new DynamicException("Only the driver can update the dynamic ask");
        }

        // here the driver should only accept or decline the ask
        if ($dynamicAskData->getStatus() != DynamicAsk::STATUS_ACCEPTED && $dynamicAskData->getStatus() != DynamicAsk::STATUS_DECLINED) {
            throw new DynamicException("Only accept or decline are permitted.");
        }
        
        $ask->setStatus($dynamicAskData->getStatus() == DynamicAsk::STATUS_ACCEPTED ? Ask::STATUS_ACCEPTED_AS_DRIVER : Ask::STATUS_DECLINED_AS_DRIVER);
        $dynamicAskData->setId($id);

        // Ask History
        $askHistory = new AskHistory();
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $ask->addAskHistory($askHistory);

        // message => the driver is the userRelated, the passenger is the user
        if (!is_null($dynamicAskData->getMessage())) {
            $message = new Message();
            $message->setUser($ask->getUserRelated());
            $message->setText($dynamicAskData->getMessage());
            // we search the previous message if it exists
            if ($lastAskHistoryWithMessage = $this->askHistoryRepository->findLastAskHistoryWithMessage($ask)) {
                if (!is_null($lastAskHistoryWithMessage->getMessage()->getMessage())) {
                    // the linked message has a parent => it is also the parent of our new message
                    $message->setMessage($lastAskHistoryWithMessage->getMessage()->getMessage());
                } else {
                    // no parent => we use the message linked as parent for our new message
                    $message->setMessage($lastAskHistoryWithMessage->getMessage());
                }
            }
            $recipient = new Recipient();
            $recipient->setUser($ask->getUser());
            $recipient->setStatus(Recipient::STATUS_PENDING);
            $message->addRecipient($recipient);
            $this->entityManager->persist($message);
            $askHistory->setMessage($message);
        }

        $this->entityManager->persist($ask);

        if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER) {
            // dynamic carpooling accepted : update the ad to include the passenger path

            $proposal = $ask->getMatching()->getProposalOffer();

            // waypoints :
            // - we remove all the previous waypoints
            // - we use the waypoints of the ask
            $newWaypoints = [];
            foreach ($ask->getWaypoints() as $point) {
                $waypoint = clone $point;
                // we search in the original waypoints if the current waypoint has been reached by the driver
                foreach ($proposal->getWaypoints() as $curWaypoint) {
                    if (
                        $curWaypoint->getAddress()->getLongitude() == $point->getAddress()->getLongitude() &&
                        $curWaypoint->getAddress()->getLatitude() == $point->getAddress()->getLatitude()
                        ) {
                        if ($curWaypoint->isReached()) {
                            $waypoint->setReached(true);
                        }
                        break;
                    }
                }
                $newWaypoints[] = $waypoint;
            }
            foreach ($proposal->getWaypoints() as $waypoint) {
                if (!$waypoint->isFloating()) {
                    $proposal->removeWaypoint($waypoint);
                }
            }
            foreach ($newWaypoints as $waypoint) {
                $proposal->addWaypoint($waypoint);
            }

            // for now, we cancel the other asks
            foreach ($proposal->getMatchingRequests() as $matching) {
                if ($matching->getId() != $ask->getMatching()->getId()) {
                    foreach ($matching->getAsks() as $ask) {
                        if ($ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER) {
                            $ask->setStatus(Ask::STATUS_DECLINED_AS_DRIVER);
                            $this->entityManager->persist($ask);
                        }
                    }
                }
            }
            // update the matchings
            $this->proposalMatcher->updateMatchingsForProposal($proposal);

            // persist the updates
            $this->entityManager->persist($proposal);
        } else {
            // dynamic carpooling refused : update the passenger ad to make it active again
            $ask->getMatching()->getProposalRequest()->setActive(true);
            $this->entityManager->persist($ask->getMatching()->getProposalRequest());
        }

        $this->entityManager->flush();

        return $dynamicAskData;
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
                        'id' => $message->getUser()->getId(),
                        'givenName' => $message->getUser()->getGivenName(),
                        'shortFamilyName' => $message->getUser()->getShortFamilyName(),
                    ]
                ];
            }
        }
        return $thread;
    }




    /******************
     *  DYNAMIC PROOF *
     ******************/

    
    /**
     * Create a proof for a dynamic ask.
     *
     * @param DynamicProof    $dynamicProof The proof to create
     * @return DynamicProof                 The created proof.
     */
    public function createDynamicProof(DynamicProof $dynamicProof)
    {
        // search the ask
        if (!$ask = $this->askRespository->find($dynamicProof->getDynamicAskId())) {
            throw new DynamicException("Dynamic ask not found");
        }

        // check that the ask is accepted
        if (!$ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER) {
            throw new DynamicException("Dynamic ask not accepted");
        }

        // check if a proof already exists
        if (!is_null($ask->getCarpoolProof())) {
            // the proof already exists, it's an update
            return $this->updateDynamicProof($ask->getCarpoolProof()->getId(), $dynamicProof);
        }

        $carpoolProof = new CarpoolProof();
        $carpoolProof->setAsk($ask);

        // search the role of the current user
        if ($ask->getUser()->getId() == $dynamicProof->getUser()->getId()) {
            // the user is passenger
            $carpoolProof->setPickUpPassengerDate(new \DateTime('UTC'));
            $carpoolProof->setPickUpPassengerAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProof->getLatitude(),'longitude'=>$dynamicProof->getLongitude()]));
        } else {
            // the user is driver
            $carpoolProof->setPickUpDriverDate(new \DateTime('UTC'));
            $carpoolProof->setPickUpDriverAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProof->getLatitude(),'longitude'=>$dynamicProof->getLongitude()]));
        }

        $this->entityManager->persist($carpoolProof);
        $this->entityManager->flush();

        $dynamicProof->setId($carpoolProof->getId());

        return $dynamicProof;
    }

    /**
     * Update a dynamic proof.
     *
     * @param integer $id                       The id of the dynamic proof to update
     * @param DynamicProof $dynamicProofData    The data to update the dynamic proof
     * @return DynamicProof The dynamic proof updated
     */
    public function updateDynamicProof(int $id, DynamicProof $dynamicProofData)
    {
        // search the proof
        if (!$carpoolProof = $this->carpoolProofRepository->find($id)) {
            throw new DynamicException("Dynamic proof not found");
        }

        // search the role of the current user
        $actor = null;
        if ($carpoolProof->getAsk()->getUser()->getId() == $dynamicProofData->getUser()->getId()) {
            // the user is passenger
            $actor = CarpoolProof::ACTOR_PASSENGER;
        } else {
            // the user is driver
            $actor = CarpoolProof::ACTOR_DRIVER;
        }

        // TODO : set the new origin and destination waypoints for the passenger => pickup and dropoff !!

        // we perform different actions depending on the role and the moment
        switch ($actor) {
            case CarpoolProof::ACTOR_DRIVER:
                if (!is_null($carpoolProof->getPickUpDriverAddress()) && is_null($carpoolProof->getPickUpPassengerAddress())) {
                    // the driver can't set the dropoff while the passenger has not certified its pickup
                    throw new DynamicException("The passenger has not sent its pickup certification yet");
                }
                if (!is_null($carpoolProof->getPickUpDriverAddress())) {
                    // the driver has set its pickup
                    if (!is_null($carpoolProof->getDropOffDriverAddress())) {
                        // the driver has already certified its pickup and dropoff
                        throw new DynamicException("The driver has already sent its dropoff certification");
                    }
                    if (is_null($carpoolProof->getDropOffPassengerAddress())) {
                        // the passenger has not set its dropoff
                        $carpoolProof->setDropOffDriverDate(new \DateTime('UTC'));
                        $carpoolProof->setDropOffDriverAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                    } else {
                        // the passenger has set its dropoff, we have to check the positions
                        if ($this->geoTools->haversineGreatCircleDistance(
                            $dynamicProofData->getLatitude(),
                            $dynamicProofData->getLongitude(),
                            $carpoolProof->getDropOffPassengerAddress()->getLatitude(),
                            $carpoolProof->getDropOffPassengerAddress()->getLongitude()
                        )<=$this->params['dynamicProofDistance']) {
                            // drop off driver
                            $carpoolProof->setDropOffDriverDate(new \DateTime('UTC'));
                            $carpoolProof->setDropOffDriverAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                        // driver direction will be set when the dynamic ad of the driver will be finished
                        } else {
                            throw new DynamicException("Driver dropoff certification failed : the passenger certified address is too far");
                        }
                    }
                } elseif (!is_null($carpoolProof->getPickUpPassengerAddress())) {
                    // the driver has not sent its pickup but the passenger has
                    if ($this->geoTools->haversineGreatCircleDistance(
                        $dynamicProofData->getLatitude(),
                        $dynamicProofData->getLongitude(),
                        $carpoolProof->getPickUpPassengerAddress()->getLatitude(),
                        $carpoolProof->getPickUpPassengerAddress()->getLongitude()
                    )<=$this->params['dynamicProofDistance']) {
                        $carpoolProof->setPickupDriverDate(new \DateTime('UTC'));
                        $carpoolProof->setPickUpDriverAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                    } else {
                        throw new DynamicException("Driver pickup certification failed : the passenger certified address is too far");
                    }
                } else {
                    // the passenger has not set its pickup
                    $carpoolProof->setPickUpDriverDate(new \DateTime('UTC'));
                    $carpoolProof->setPickUpDriverAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                }
                break;
            case CarpoolProof::ACTOR_PASSENGER:
                if (!is_null($carpoolProof->getPickUpPassengerAddress()) && is_null($carpoolProof->getPickUpDriverAddress())) {
                    // the passenger can't set the dropoff while the driver has not certified its pickup
                    throw new DynamicException("The driver has not sent its pickup certification yet");
                }
                if (!is_null($carpoolProof->getPickUpPassengerAddress())) {
                    // the passenger has set its pickup
                    if (!is_null($carpoolProof->getDropOffPassengerAddress())) {
                        // the passenger has already certified its pickup and dropoff
                        throw new DynamicException("The passenger has already sent its dropoff certification");
                    }
                    if (is_null($carpoolProof->getDropOffDriverAddress())) {
                        // the driver has not set its dropoff
                        $carpoolProof->setDropOffPassengerDate(new \DateTime('UTC'));
                        $carpoolProof->setDropOffPassengerAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                    } else {
                        // the driver has set its dropoff, we have to check the positions
                        if ($this->geoTools->haversineGreatCircleDistance(
                            $dynamicProofData->getLatitude(),
                            $dynamicProofData->getLongitude(),
                            $carpoolProof->getDropOffDriverAddress()->getLatitude(),
                            $carpoolProof->getDropOffDriverAddress()->getLongitude()
                        )<=$this->params['dynamicProofDistance']) {
                            // drop off passenger
                            $carpoolProof->setDropOffPassengerDate(new \DateTime('UTC'));
                            $carpoolProof->setDropOffPassengerAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                        // set passenger direction
                        } else {
                            throw new DynamicException("Passenger dropoff certification failed : the driver certified address is too far");
                        }
                    }
                } elseif (!is_null($carpoolProof->getPickUpDriverAddress())) {
                    // the passenger has not sent its pickup but the driver has
                    if ($this->geoTools->haversineGreatCircleDistance(
                        $dynamicProofData->getLatitude(),
                        $dynamicProofData->getLongitude(),
                        $carpoolProof->getPickUpDriverAddress()->getLatitude(),
                        $carpoolProof->getPickUpDriverAddress()->getLongitude()
                    )<=$this->params['dynamicProofDistance']) {
                        $carpoolProof->setPickupPassengerDate(new \DateTime('UTC'));
                        $carpoolProof->setPickUpPassengerAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                    } else {
                        throw new DynamicException("Passenger pickup certification failed : the driver certified address is too far");
                    }
                } else {
                    // the driver has not set its pickup
                    $carpoolProof->setPickupPassengerDate(new \DateTime('UTC'));
                    $carpoolProof->setPickUpPassengerAddress($this->getAddressByPartialAddressArray(['latitude'=>$dynamicProofData->getLatitude(),'longitude'=>$dynamicProofData->getLongitude()]));
                }
                break;
        }

        $this->entityManager->persist($carpoolProof);
        $this->entityManager->flush();

        $dynamicProofData->setId($carpoolProof->getId());
        return $dynamicProofData;
    }




    /***********
     *  COMMON *
     ***********/


    /**
     * Get an address using an array. The array may contain only some informations like latitude or longitude.
     *
     * @param array $point  The point
     * @return Address
     */
    private function getAddressByPartialAddressArray(array $point)
    {
        $address = new Address();

        // first we set the lat/lon
        if (isset($point['latitude'])) {
            $address->setLatitude($point['latitude']);
        }
        if (isset($point['longitude'])) {
            $address->setLongitude($point['longitude']);
        }

        // then we reverse geocode, to get a full address if the other properties are not sent
        if ($addresses = $this->geoSearcher->reverseGeoCode($address->getLatitude(), $address->getLongitude())) {
            if (count($addresses)>0) {
                $address = $addresses[0];
            }
        }

        // if other properties are sent we use them
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
        
        if (isset($point['elevation'])) {
            $address->setElevation($point['elevation']);
        }
        if (isset($point['name'])) {
            $address->setName($point['name']);
        }
        if (isset($point['home'])) {
            $address->setHome($point['home']);
        }
        return $address;
    }
}
