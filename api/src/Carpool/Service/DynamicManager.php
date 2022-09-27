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

namespace App\Carpool\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Position;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Exception\DynamicException;
use App\Carpool\Exception\ProofException;
use App\Carpool\Repository\AskHistoryRepository;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Ressource\Dynamic;
use App\Carpool\Ressource\DynamicAsk;
use App\Carpool\Ressource\DynamicProof;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Communication\Service\InternalMessageManager;
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Geography\Service\AddressCompleter;
use App\Geography\Service\Geocoder\MobicoopGeocoder;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\GeoTools;
use App\Geography\Service\Point\AddressAdapter;
use App\Geography\Service\Point\MobicoopGeocoderPointProvider;
use App\User\Entity\User;
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
    private $proposalMatcher;
    private $askManager;
    private $resultManager;
    private $geoTools;
    private $geoRouter;
    private $reversePointProvider;
    private $addressCompleter;
    private $params;
    private $logger;
    private $matchingRepository;
    private $askRespository;
    private $askHistoryRepository;
    private $internalMessageManager;
    private $proofManager;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProposalManager $proposalManager,
        ProposalMatcher $proposalMatcher,
        AskManager $askManager,
        ResultManager $resultManager,
        GeoTools $geoTools,
        GeoRouter $geoRouter,
        MobicoopGeocoder $mobicoopGeocoder,
        array $params,
        LoggerInterface $logger,
        MatchingRepository $matchingRepository,
        AskRepository $askRespository,
        AskHistoryRepository $askHistoryRepository,
        InternalMessageManager $internalMessageManager,
        ProofManager $proofManager
    ) {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->askManager = $askManager;
        $this->resultManager = $resultManager;
        $this->geoTools = $geoTools;
        $this->geoRouter = $geoRouter;
        $this->params = $params;
        $this->logger = $logger;
        $this->matchingRepository = $matchingRepository;
        $this->askRespository = $askRespository;
        $this->askHistoryRepository = $askHistoryRepository;
        $this->internalMessageManager = $internalMessageManager;
        $this->proofManager = $proofManager;

        $this->reversePointProvider = new MobicoopGeocoderPointProvider($mobicoopGeocoder);
        $this->addressCompleter = new AddressCompleter($this->reversePointProvider);
    }

    // DYNAMIC AD

    /**
     * Get a dynamic ad.
     *
     * @param int $id the dynamic ad id
     *
     * @return Dynamic the dynamic ad
     */
    public function getDynamic(int $id)
    {
        if (!$proposal = $this->proposalManager->get($id)) {
            throw new DynamicException('Dynamic ad not found');
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
     * @param Dynamic $dynamic The dynamic ad to create
     *
     * @return Dynamic the created Dynamic ad
     */
    public function createDynamic(Dynamic $dynamic)
    {
        // first we check if the user has already a dynamic ad pending
        if ($this->proposalManager->hasPendingDynamic($dynamic->getUser())) {
            throw new DynamicException('This user has already a pending dynamic ad');
        }

        // set Seats
        if (is_null($dynamic->getSeats())) {
            if (Dynamic::ROLE_DRIVER == $dynamic->getRole()) {
                $dynamic->setSeats($this->params['defaultSeatsDriver']);
            } else {
                $dynamic->setSeats($this->params['defaultSeatsPassenger']);
            }
        }
        // set Date
        if (is_null($dynamic->getDate())) {
            $dynamic->setDate(new \DateTime('UTC'));
        }

        // creation of the proposal
        $this->logger->info('DynamicManager : start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

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
        $criteria->setDriver(Dynamic::ROLE_DRIVER == $dynamic->getRole());
        $criteria->setPassenger(Dynamic::ROLE_PASSENGER == $dynamic->getRole());
        $criteria->setSeatsDriver(Dynamic::ROLE_DRIVER == $dynamic->getRole() ? $dynamic->getSeats() : 0);
        $criteria->setSeatsPassenger(Dynamic::ROLE_PASSENGER == $dynamic->getRole() ? $dynamic->getSeats() : 0);

        // prices
        $criteria->setPriceKm($dynamic->getPriceKm());
        if (Dynamic::ROLE_DRIVER == $dynamic->getRole()) {
            $criteria->setDriverPrice($dynamic->getPrice());
        }
        if (Dynamic::ROLE_PASSENGER == $dynamic->getRole()) {
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
            $address = $this->addressCompleter->getAddressByPartialAddressArray($point);
            $waypoint->setAddress($address);
            $waypoint->setPosition($waypointPosition);
            $waypoint->setDestination($waypointPosition == count($dynamic->getWaypoints()) - 1);
            $proposal->addWaypoint($waypoint);

            if (0 == $waypointPosition) {
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
                $direction->setDetail('');
                $direction->setSnapped('');
                $direction->setFormat('Dynamic');
                $position->setDirection($direction);
            }
        }

        $proposal->setCriteria($criteria);

        $proposal = $this->proposalManager->prepareProposal($proposal);
        $this->logger->info('DynamicManager : end creating ad '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $this->entityManager->persist($proposal);
        $this->logger->info('DynamicManager : end persisting ad '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $position->setProposal($proposal);
        $this->entityManager->persist($position);
        $this->entityManager->flush();

        if (Dynamic::ROLE_DRIVER == $dynamic->getRole()) {
            $dynamic->setPrice($proposal->getCriteria()->getDriverComputedRoundedPrice());
        } else {
            $dynamic->setPrice($proposal->getCriteria()->getPassengerComputedRoundedPrice());
        }

        // we compute the results

        // default order
        $dynamic->setFilters([
            'order' => [
                'criteria' => 'date',
                'value' => 'ASC',
            ],
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
     * Update a dynamic ad => update the current position.
     *
     * @param int     $id          The id of the dynamic ad to update
     * @param Dynamic $dynamicData The dynamic ad data to make the update
     *
     * @return Dynamic the updated Dynamic ad
     */
    public function updateDynamic(int $id, Dynamic $dynamicData)
    {
        // we get the original dynamic ad
        $dynamic = $this->getDynamic($id);

        // dynamic ad ?
        if (!$dynamic->getProposal()->isDynamic()) {
            throw new DynamicException('This ad is not dynamic !');
        }

        // not already finished ?
        if ($dynamic->getProposal()->isFinished()) {
            throw new DynamicException('This ad is finished !');
        }

        // the user indicates that the ad is finished
        if ($dynamicData->isFinished()) {
            $dynamic->getProposal()->setFinished(true);
            $dynamic->getProposal()->setActive(false);
            $dynamic->setFinished(true);
        }

        // last point check
        if ($this->params['dynamicEnableMaxSpeed']) {
            // we compute the direction between the 2 last points to get the average speed
            // => we exclude the point if the speed is too high (can happen with bad GPS coordinates, eg. bad lane guessing on motorways)
            $now = new \DateTime('UTC');
            $newAddress = new Address();
            $newAddress->setLongitude($dynamicData->getLongitude());
            $newAddress->setLatitude($dynamicData->getLatitude());
            $addresses = [
                $dynamic->getProposal()->getPosition()->getWaypoint()->getAddress(),
                $newAddress,
            ];
            if ($routes = $this->geoRouter->getRoutes($addresses)) {
                // we have a direction
                $distance = $routes[0]->getDistance();
                $interval = $now->diff($dynamic->getProposal()->getPosition()->getUpdatedDate());
                $seconds = ((($interval->format('%a') * 24) + $interval->format('%H')) * 60 + $interval->format('%i')) * 60 + $interval->format('%s');
                if (($distance / $seconds) > $this->params['dynamicMaxSpeed']) {
                    throw new DynamicException('Speed too high since the last point ('.round($distance / $seconds * 3.6).' kmh) ignoring last point');
                }
            }
        }

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
                if ($this->geoTools->haversineGreatCircleDistance($dynamic->getLatitude(), $dynamic->getLongitude(), $waypoint->getAddress()->getLatitude(), $waypoint->getAddress()->getLongitude()) < $this->params['dynamicReachedDistance']) {
                    $waypoint->setReached(true);
                    $this->entityManager->persist($waypoint);
                    $this->entityManager->flush();
                }
            }
            if ($waypoint->isDestination() && $waypoint->isReached() && $this->geoTools->haversineGreatCircleDistance($dynamic->getLatitude(), $dynamic->getLongitude(), $waypoint->getAddress()->getLatitude(), $waypoint->getAddress()->getLongitude()) < $this->params['dynamicDestinationDistance']) {
                $dynamic->setDestination(true);
            }
            if ($waypoint->isFloating()) {
                // update the floating waypoint address
                // we reverse geocode, to get a full address
                if ($points = $this->reversePointProvider->reverse((float) $dynamic->getLongitude(), (float) $dynamic->getLatitude())) {
                    if (count($points) > 0) {
                        $reversedGeocodeAddress = AddressAdapter::pointToAddress($points[0]);
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
            // $dynamic->getProposal()->getPosition()->getDirection()->setDetail($newDirection->getDetail());
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
            // we send an exception...
            throw new DynamicException('Bad geographic position... Point ignored !');
        }

        // update the matchings
        // (and update the proposal direction (= direction from the current point to the destination))
        $dynamic->setProposal($this->proposalManager->updateMatchingsForProposal($dynamic->getProposal(), $address));

        // update the proof if there's one pending
        $this->updateProofsDirectionForDynamic($dynamic);

        // persist the updates
        $this->entityManager->persist($dynamic->getProposal());
        $this->entityManager->flush();

        // default order
        $dynamic->setFilters([
            'order' => [
                'criteria' => 'date',
                'value' => 'ASC',
            ],
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
        if (Dynamic::ROLE_DRIVER == $dynamic->getRole()) {
            // the user is driver, we search the matching requests
            foreach ($dynamic->getProposal()->getMatchingRequests() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    /**
                     * @var Ask $ask
                     */
                    // there's an ask, the initiator of the ask is the passenger => the user of the ask
                    // if the pickup hasn't been made yet, we compute the direction between the driver and the passenger
                    $pickUpDuration = null;
                    $pickUpDistance = null;
                    $pickUpUnlock = false;
                    if (0 == count($ask->getCarpoolProofs())) {
                        $addresses = [];
                        $addressDriver = $matching->getProposalOffer()->getPosition()->getWaypoint()->getAddress();
                        $addressPassenger = $matching->getProposalRequest()->getPosition()->getWaypoint()->getAddress();
                        $addresses[] = $addressDriver;
                        $addresses[] = $addressPassenger;
                        $pickUpUnlock = $this->geoTools->haversineGreatCircleDistance(
                            $addressDriver->getLatitude(),
                            $addressDriver->getLongitude(),
                            $addressPassenger->getLatitude(),
                            $addressPassenger->getLongitude()
                        ) <= $this->params['dynamicProofDistance'];
                        if ($routes = $this->geoRouter->getRoutes($addresses)) {
                            $pickUpDuration = $routes[0]->getDuration();
                            $pickUpDistance = $routes[0]->getDistance();
                        }
                    }
                    // check if there's a proof pending
                    $proof = null;
                    if (1 == count($ask->getCarpoolProofs())) {
                        $proof['id'] = $ask->getCarpoolProofs()[0]->getId();
                        if (is_null($ask->getCarpoolProofs()[0]->getPickUpDriverAddress()) && !is_null($ask->getCarpoolProofs()[0]->getPickUpPassengerAddress())) {
                            $proof['needed'] = 'pickUp';
                        } elseif (is_null($ask->getCarpoolProofs()[0]->getDropOffDriverAddress()) && !is_null($ask->getCarpoolProofs()[0]->getDropOffPassengerAddress())) {
                            $proof['needed'] = 'dropOff';
                        }
                    }
                    $status = DynamicAsk::STATUS_PENDING;
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus()) {
                        $status = DynamicAsk::STATUS_ACCEPTED;
                    } elseif (Ask::STATUS_DECLINED_AS_DRIVER == $ask->getStatus()) {
                        $status = DynamicAsk::STATUS_DECLINED;
                    } elseif (Ask::STATUS_DECLINED_AS_PASSENGER == $ask->getStatus()) {
                        $status = DynamicAsk::STATUS_CANCELLED;
                    }
                    $asks[] = [
                        'id' => $ask->getId(),
                        'status' => $status,
                        'user' => [
                            'id' => $ask->getUser()->getId(),
                            'givenName' => $ask->getUser()->getGivenName(),
                            'shortFamilyName' => $ask->getUser()->getShortFamilyName(),
                            'telephone' => DynamicAsk::STATUS_ACCEPTED == $status ? $ask->getUser()->getTelephone() : null,
                            'position' => $matching->getProposalRequest()->getPosition()->getWaypoint()->getAddress(),
                        ],
                        'result' => $this->getResult($matching, $dynamic->getResults()),
                        'messages' => $this->getThread($ask),
                        'priceKm' => $ask->getCriteria()->getPriceKm(),
                        'price' => $ask->getCriteria()->getPassengerComputedRoundedPrice(),
                        'duration' => $matching->getDropOffDuration() - $matching->getPickUpDuration(),
                        'pickUpDuration' => $pickUpDuration,
                        'pickUpDistance' => $pickUpDistance,
                        'pickUpUnlock' => $pickUpUnlock,
                        'detourDistance' => $matching->getDetourDistance(),
                        'detourDuration' => $matching->getDetourDuration(),
                        'proof' => $proof,
                    ];
                }
            }
        } else {
            // the user is passenger, we search the matching offers
            foreach ($dynamic->getProposal()->getMatchingOffers() as $matching) {
                foreach ($matching->getAsks() as $ask) {
                    /**
                     * @var Ask $ask
                     */
                    // there's an ask, the recipient of the ask is the driver => the userRelated of the ask
                    // if the pickup hasn't been made yet, we compute the direction between the driver and the passenger
                    $pickUpDuration = null;
                    $pickUpDistance = null;
                    $pickUpUnlock = false;
                    if (0 == count($ask->getCarpoolProofs())) {
                        $addresses = [];
                        $addressDriver = $matching->getProposalOffer()->getPosition()->getWaypoint()->getAddress();
                        $addressPassenger = $matching->getProposalRequest()->getPosition()->getWaypoint()->getAddress();
                        $addresses[] = $addressDriver;
                        $addresses[] = $addressPassenger;
                        $pickUpUnlock = $this->geoTools->haversineGreatCircleDistance(
                            $addressDriver->getLatitude(),
                            $addressDriver->getLongitude(),
                            $addressPassenger->getLatitude(),
                            $addressPassenger->getLongitude()
                        ) <= $this->params['dynamicProofDistance'];
                        if ($routes = $this->geoRouter->getRoutes($addresses)) {
                            $pickUpDuration = $routes[0]->getDuration();
                            $pickUpDistance = $routes[0]->getDistance();
                        }
                    }
                    // check if there's a proof pending
                    $proof = null;
                    if (1 == count($ask->getCarpoolProofs())) {
                        $proof['id'] = $ask->getCarpoolProofs()[0]->getId();
                        if (!is_null($ask->getCarpoolProofs()[0]->getPickUpDriverAddress()) && is_null($ask->getCarpoolProofs()[0]->getPickUpPassengerAddress())) {
                            $proof['needed'] = 'pickUp';
                        } elseif (!is_null($ask->getCarpoolProofs()[0]->getDropOffDriverAddress()) && is_null($ask->getCarpoolProofs()[0]->getDropOffPassengerAddress())) {
                            $proof['needed'] = 'dropOff';
                        }
                    }
                    $status = DynamicAsk::STATUS_PENDING;
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus()) {
                        $status = DynamicAsk::STATUS_ACCEPTED;
                    } elseif (Ask::STATUS_DECLINED_AS_DRIVER == $ask->getStatus()) {
                        $status = DynamicAsk::STATUS_DECLINED;
                    } elseif (Ask::STATUS_DECLINED_AS_PASSENGER == $ask->getStatus()) {
                        $status = DynamicAsk::STATUS_CANCELLED;
                    }
                    $asks[] = [
                        'id' => $ask->getId(),
                        'status' => $status,
                        'user' => [
                            'id' => $ask->getUserRelated()->getId(),
                            'givenName' => $ask->getUserRelated()->getGivenName(),
                            'shortFamilyName' => $ask->getUserRelated()->getShortFamilyName(),
                            'telephone' => DynamicAsk::STATUS_ACCEPTED == $status ? $ask->getUserRelated()->getTelephone() : null,
                            'position' => $matching->getProposalOffer()->getPosition()->getWaypoint()->getAddress(),
                        ],
                        'result' => $this->getResult($matching, $dynamic->getResults()),
                        'messages' => $this->getThread($ask),
                        'priceKm' => $ask->getCriteria()->getPriceKm(),
                        'price' => $ask->getCriteria()->getPassengerComputedRoundedPrice(),
                        'duration' => $matching->getDropOffDuration() - $matching->getPickUpDuration(),
                        'pickUpDuration' => $pickUpDuration,
                        'pickUpDistance' => $pickUpDistance,
                        'pickUpUnlock' => $pickUpUnlock,
                        'detourDistance' => $matching->getDetourDistance(),
                        'detourDuration' => $matching->getDetourDuration(),
                        'proof' => $proof,
                    ];
                }
            }
        }
        $dynamic->setAsks($asks);

        return $dynamic;
    }

    /**
     * Get the last unfinished dynamic ad.
     *
     * @param User $user The user for which we want the ad
     *
     * @return null|Dynamic the dynamic ad found or null if not found
     */
    public function getLastDynamicUnfinished(User $user)
    {
        if ($proposal = $this->proposalManager->getLastDynamicUnfinished($user)) {
            $dynamic = new Dynamic();
            $dynamic->setProposal($proposal);
            $dynamic->setUser($proposal->getUser());
            $dynamic->setRole($proposal->getCriteria()->isDriver() ? Dynamic::ROLE_DRIVER : Dynamic::ROLE_PASSENGER);
            $dynamic->setId($proposal->getId());

            return $dynamic;
        }

        return null;
    }

    // DYNAMIC ASK

    /**
     * Get a dynamic ask.
     *
     * @param int $id the dynamic ask id
     *
     * @return DynamicAsk the dynamic ask
     */
    public function getDynamicAsk(int $id)
    {
        if (!$ask = $this->askRespository->find($id)) {
            throw new DynamicException('Dynamic ask not found');
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
     * @param DynamicAsk $dynamicAsk The ask to create
     *
     * @return DynamicAsk the created ask
     */
    public function createDynamicAsk(DynamicAsk $dynamicAsk)
    {
        // only the passenger can create an ask
        $matching = $this->matchingRepository->find($dynamicAsk->getMatchingId());
        if ($dynamicAsk->getUser()->getId() != $matching->getProposalRequest()->getUser()->getId()) {
            throw new DynamicException('Only the passenger can create the dynamic ask');
        }

        // check that another ask is not already made
        if ($this->askManager->hasPendingDynamicAsk($dynamicAsk->getUser())) {
            throw new DynamicException('This user has already a pending dynamic ask');
        }

        // check that another ask has not been made on this particular ad
        if ($this->askManager->hasRefusedDynamicAsk($dynamicAsk->getUser(), $matching)) {
            throw new DynamicException('This user has already a refused dynamic ask on this matching');
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
        if (!is_null($dynamicAsk->getMessage()) && '' != $dynamicAsk->getMessage()) {
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
     * Update an ask for a dynamic ad :
     * - by the driver to accept / refuse an ask
     * - by the passenger to cancel an ask (before the driver has accepted only !).
     *
     * @param int        $id             The id of the ask to update
     * @param DynamicAsk $dynamicAskData The ask data to make the update
     *
     * @return DynamicAsk the updated ask
     */
    public function updateDynamicAsk(int $id, DynamicAsk $dynamicAskData)
    {
        // get the ask
        $ask = $this->askRespository->find($id);

        // the driver should only accept or decline the ask
        if ($ask->getUserRelated()->getId() == $dynamicAskData->getUser()->getId() && DynamicAsk::STATUS_ACCEPTED != $dynamicAskData->getStatus() && DynamicAsk::STATUS_DECLINED != $dynamicAskData->getStatus()) {
            throw new DynamicException('Only accept or decline are permitted.');
        }

        // the driver should only accept or decline a pending ask
        if ($ask->getUserRelated()->getId() == $dynamicAskData->getUser()->getId() && Ask::STATUS_DECLINED_AS_PASSENGER == $ask->getStatus()) {
            throw new DynamicException('The ask has been cancelled.');
        }

        // the passenger can only cancel an ask
        if ($ask->getUser()->getId() == $dynamicAskData->getUser()->getId()) {
            if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus()) {
                throw new DynamicException('Update forbidden : the driver has already accepted the carpooling.');
            }
            if (DynamicAsk::STATUS_CANCELLED != $dynamicAskData->getStatus()) {
                throw new DynamicException('Only cancel is permitted.');
            }
        }

        $ask->setStatus(DynamicAsk::STATUS_ACCEPTED == $dynamicAskData->getStatus() ? Ask::STATUS_ACCEPTED_AS_DRIVER : (DynamicAsk::STATUS_DECLINED == $dynamicAskData->getStatus() ? Ask::STATUS_DECLINED_AS_DRIVER : Ask::STATUS_DECLINED_AS_PASSENGER));
        $dynamicAskData->setId($id);

        // Ask History
        $askHistory = new AskHistory();
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $ask->addAskHistory($askHistory);

        // message => the driver is the userRelated, the passenger is the user
        if (!is_null($dynamicAskData->getMessage()) && '' != $dynamicAskData->getMessage()) {
            $message = new Message();
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
            if ($ask->getUser()->getId() == $dynamicAskData->getUser()->getId()) {
                // the passenger sends a message
                $message->setUser($ask->getUser());
                $recipient->setUser($ask->getUserRelated());
            } else {
                // the driver sends a message
                $message->setUser($ask->getUserRelated());
                $recipient->setUser($ask->getUser());
            }
            $recipient->setStatus(Recipient::STATUS_PENDING);
            $message->addRecipient($recipient);
            $this->entityManager->persist($message);
            $askHistory->setMessage($message);
        }

        $this->entityManager->persist($ask);

        if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus()) {
            // dynamic carpooling accepted : update the ad to include the passenger path

            $proposal = $ask->getMatching()->getProposalOffer();

            // waypoints :
            // - we remove all the previous waypoints
            // - we use the waypoints of the ask
            $newWaypoints = [];
            foreach ($ask->getWaypoints() as $point) {
                $waypoint = clone $point;
                if (0 == $waypoint->getPosition()) {
                    // the first waypoint was the driver floating waypoint when the passenger made the ask, it wasn't reached, but we set it as reached anyway
                    $waypoint->setReached(true);
                }
                // we search in the original waypoints if the current waypoint has been reached by the driver
                foreach ($proposal->getWaypoints() as $curWaypoint) {
                    if (
                        $curWaypoint->getAddress()->getLongitude() == $point->getAddress()->getLongitude()
                        && $curWaypoint->getAddress()->getLatitude() == $point->getAddress()->getLatitude()
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

            // uncomment to cancel the other asks arbitrary as the path has changed
            // foreach ($proposal->getMatchingRequests() as $matching) {
            //     if ($matching->getId() != $ask->getMatching()->getId()) {
            //         foreach ($matching->getAsks() as $ask) {
            //             if ($ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER) {
            //                 $ask->setStatus(Ask::STATUS_DECLINED_AS_DRIVER);
            //                 $this->entityManager->persist($ask);
            //             }
            //         }
            //     }
            // }

            // update the matchings
            $this->proposalMatcher->updateMatchingsForProposal($proposal);

            // persist the updates
            $this->entityManager->persist($proposal);
        } else {
            // dynamic carpooling refused or cancelled : update the passenger ad to make it active again
            $ask->getMatching()->getProposalRequest()->setActive(true);
            $this->entityManager->persist($ask->getMatching()->getProposalRequest());
        }

        $this->entityManager->flush();

        return $dynamicAskData;
    }

    // DYNAMIC PROOF

    /**
     * Create a proof for a dynamic ask.
     *
     * @param DynamicProof $dynamicProof The proof to create (or update if it already exists)
     *
     * @return DynamicProof the created or updated proof
     */
    public function createDynamicProof(DynamicProof $dynamicProof)
    {
        // search the ask
        if (!$ask = $this->askRespository->find($dynamicProof->getDynamicAskId())) {
            throw new DynamicException('Dynamic ask not found');
        }

        // check that the ask is accepted
        if (Ask::STATUS_ACCEPTED_AS_DRIVER == !$ask->getStatus()) {
            throw new DynamicException('Dynamic ask not accepted');
        }

        // check if a proof already exists => the array of carpool proofs for the ask has only one item as it's dynamic => punctual
        if (1 == count($ask->getCarpoolProofs())) {
            // the proof already exists, it's an update
            return $this->updateDynamicProof($ask->getCarpoolProofs()[0]->getId(), $dynamicProof);
        }

        $carpoolProof = $this->proofManager->createProof($ask, $dynamicProof->getLongitude(), $dynamicProof->getLatitude(), CarpoolProof::TYPE_UNDETERMINED_DYNAMIC, $dynamicProof->getUser(), $ask->getUserRelated(), $ask->getUser());

        $dynamicProof->setId($carpoolProof->getId());
        $dynamicProof->setStatus(
            ($carpoolProof->getPickUpPassengerDate() ? '1' : '0').
            ($carpoolProof->getPickUpDriverDate() ? '1' : '0').
            ($carpoolProof->getDropOffPassengerDate() ? '1' : '0').
            ($carpoolProof->getDropOffDriverDate() ? '1' : '0')
        );

        return $dynamicProof;
    }

    /**
     * Update a dynamic proof.
     *
     * @param int          $id               The id of the dynamic proof to update
     * @param DynamicProof $dynamicProofData The data to update the dynamic proof
     *
     * @return DynamicProof The dynamic proof updated
     */
    public function updateDynamicProof(int $id, DynamicProof $dynamicProofData)
    {
        // search the proof
        if (!$carpoolProof = $this->proofManager->getProof($id)) {
            throw new DynamicException('Dynamic proof not found');
        }

        // Check if the proof has been canceled
        if (CarpoolProof::STATUS_CANCELED === $carpoolProof->getStatus()) {
            throw new DynamicException('Dynamic proof already canceled');
        }

        try {
            $carpoolProof = $this->proofManager->updateProof($id, $dynamicProofData->getLongitude(), $dynamicProofData->getLatitude(), $dynamicProofData->getUser(), $carpoolProof->getAsk()->getMatching()->getProposalRequest()->getUser(), $this->params['dynamicProofDistance']);
            $dynamicProofData->setId($carpoolProof->getId());
            $dynamicProofData->setStatus(
                ($carpoolProof->getPickUpPassengerDate() ? '1' : '0').
                ($carpoolProof->getPickUpDriverDate() ? '1' : '0').
                ($carpoolProof->getDropOffPassengerDate() ? '1' : '0').
                ($carpoolProof->getDropOffDriverDate() ? '1' : '0')
            );
        } catch (ProofException $proofException) {
            throw new DynamicException($proofException->getMessage());
        }

        return $dynamicProofData;
    }

    /**
     * Get a result from a Matching.
     *
     * @param Matching $matching The matching
     * @param array    $results  The array of results
     *
     * @return null|Result The result found
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

    /**
     * Get all the messages related to an ask.
     *
     * @param Ask $ask The ask
     *
     * @return array The messages
     */
    private function getThread(Ask $ask)
    {
        $thread = [];
        if (!is_null($ask->getAskHistories()[0]->getMessage())) {
            $messages = $this->internalMessageManager->getCompleteThread($ask->getAskHistories()[0]->getMessage()->getId());
            foreach ($messages as $message) {
                // @var Message $message
                $thread[] = [
                    'text' => $message->getText(),
                    'user' => [
                        'id' => $message->getUser()->getId(),
                        'givenName' => $message->getUser()->getGivenName(),
                        'shortFamilyName' => $message->getUser()->getShortFamilyName(),
                    ],
                ];
            }
        }

        return $thread;
    }

    /**
     * Update the direction of the related proofs of a dynamic ad (if it exists).
     * For now we only update the direction using the driver position updates to avoid mismatches.
     *
     * @param Dynamic $dynamic The dynamic ad
     */
    private function updateProofsDirectionForDynamic(Dynamic $dynamic)
    {
        // first we search if there are asks related to the dynamic ad
        if (Dynamic::ROLE_DRIVER == $dynamic->getRole()) {
            // the user is driver
            foreach ($dynamic->getProposal()->getMatchingRequests() as $matching) {
                /**
                 * @var Matching $matching
                 */
                foreach ($matching->getAsks() as $ask) {
                    /**
                     * @var Ask $ask
                     */
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() && 1 == count($ask->getCarpoolProofs()) && !is_null($ask->getCarpoolProofs()[0]->getPickUpDriverAddress())) {
                        // we update the direction if the driver has made its pickup certification
                        $this->updateProofDirection($ask->getCarpoolProofs()[0], $dynamic->getLongitude(), $dynamic->getLatitude());
                    }
                }
            }
        }
        // uncomment the following to use also the passenger position
        // } else {
        //     // the user is passenger
        //     foreach ($dynamic->getProposal()->getMatchingOffers() as $matching) {
        //         /**
        //          * @var Matching $matching
        //          */
        //         foreach ($matching->getAsks() as $ask) {
        //             /**
        //              * @var Ask $ask
        //              */
        //             if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER && !is_null($ask->getCarpoolProof()) && !is_null($ask->getCarpoolProof()->getPickUpPassengerAddress())) {
        //                 $this->updateProofDirection($ask->getCarpoolProof(),$dynamic->getLongitude(), $dynamic->getLatitude());
        //             }
        //         }
        //     }
        // }
    }

    /**
     * Update a carpool proof direction.
     *
     * @param CarpoolProof $carpoolProof The carpool proof
     * @param float        $longitude    The longitude of the new point
     * @param float        $latitude     The latitude of the new point
     */
    private function updateProofDirection(CarpoolProof $carpoolProof, float $longitude, float $latitude)
    {
        // first we get all the past points that are stored as a linestring in the geoJsonPoints property
        $points = $carpoolProof->getGeoJsonPoints()->getPoints();
        // then we add the last point (must be an object that have longitude and latitude properties, like an Address)
        $address = new Address();
        $address->setLatitude($latitude);
        $address->setLongitude($longitude);
        $points[] = $address;
        $carpoolProof->setPoints($points);
        // here we force the update because maybe none of the properties from the entity could be updated, but we need to compute GeoJson
        $carpoolProof->setAutoUpdatedDate();

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
            $carpoolProof->getDirection()->setDistance($newDirection->getDistance());
            $carpoolProof->getDirection()->setDuration($newDirection->getDuration());
            $carpoolProof->getDirection()->setAscend($newDirection->getAscend());
            $carpoolProof->getDirection()->setDescend($newDirection->getDescend());
            $carpoolProof->getDirection()->setBboxMinLon($newDirection->getBboxMinLon());
            $carpoolProof->getDirection()->setBboxMinLat($newDirection->getBboxMinLat());
            $carpoolProof->getDirection()->setBboxMaxLon($newDirection->getBboxMaxLon());
            $carpoolProof->getDirection()->setBboxMaxLat($newDirection->getBboxMaxLat());
            // $carpoolProof->getDirection()->setDetail($newDirection->getDetail());
            $carpoolProof->getDirection()->setFormat($newDirection->getFormat());
            $carpoolProof->getDirection()->setSnapped($newDirection->getSnapped());
            $carpoolProof->getDirection()->setBearing($newDirection->getBearing());
            // the following is needed to compute the geoJson in the direction automatic update trigger
            $carpoolProof->getDirection()->setPoints($routes[0]->getPoints());
            $carpoolProof->getDirection()->setSaveGeoJson(true);
            $carpoolProof->getDirection()->setDetailUpdatable(true);
            // here we force the update because maybe none of the properties from the entity could be updated, but we need to compute GeoJson
            $carpoolProof->getDirection()->setAutoUpdatedDate();
        } else {
            // the last point introduced an error as we couldn't compute the direction !
            // we send an exeption...
            throw new DynamicException('Bad geographic position... Point ignored !');
        }

        $this->entityManager->persist($carpoolProof);
        $this->entityManager->flush();
    }
}
