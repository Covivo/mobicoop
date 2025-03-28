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

use App\App\Service\AppManager;
use App\Auth\Service\AuthManager;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\MapsAd\MapsAd;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Event\AdMinorUpdatedEvent;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Exception\AdException;
use App\Carpool\Exception\AntiFraudException;
use App\Carpool\Exception\ProofException;
use App\Carpool\Repository\CriteriaRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Carpool\Ressource\ClassicProof;
use App\Community\Exception\CommunityNotFoundException;
use App\Community\Repository\CommunityRepository;
use App\Event\Exception\EventNotFoundException;
use App\Event\Service\EventManager;
use App\Geography\Entity\Address;
use App\Geography\Service\AddressManager;
use App\Geography\Service\Geocoder\GeocoderFactory;
use App\Geography\Service\GeoTools;
use App\Geography\Service\Point\AddressAdapter;
use App\Geography\Service\Point\GeocoderPointProvider;
use App\Incentive\Event\FirstLongDistanceJourneyPublishedEvent;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Rdex\Entity\RdexError;
use App\Solidary\Repository\SubjectRepository;
use App\User\Entity\User;
use App\User\Exception\UserAlreadyExistsException;
use App\User\Exception\UserNotFoundException;
use App\User\Repository\UserRepository;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Ad manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class AdManager
{
    public const AUTHORIZED_MATCHING_ALGORITHMS = [Ad::MATCHING_ALGORITHM_V2, Ad::MATCHING_ALGORITHM_V3];

    private $entityManager;
    private $proposalManager;
    private $userManager;
    private $communityRepository;
    private $eventManager;
    private $resultManager;
    private $params;
    private $logger;
    private $proposalRepository;
    private $matchingRepository;
    private $criteriaRepository;
    private $proposalMatcher;
    private $askManager;
    private $eventDispatcher;
    private $security;
    private $authManager;
    private $proofManager;
    private $subjectRepository;
    private $addressManager;
    private $appManager;
    private $antiFraudManager;
    private $userRepository;

    private $currentMargin;

    private $reversePointProvider;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    private $_matcherCustomization;
    private $_defaultCarpoolTimezone;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProposalManager $proposalManager,
        UserManager $userManager,
        MatchingRepository $matchingRepository,
        CommunityRepository $communityRepository,
        EventManager $eventManager,
        ResultManager $resultManager,
        LoggerInterface $logger,
        array $params,
        ProposalRepository $proposalRepository,
        CriteriaRepository $criteriaRepository,
        ProposalMatcher $proposalMatcher,
        AskManager $askManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security,
        AuthManager $authManager,
        ProofManager $proofManager,
        SubjectRepository $subjectRepository,
        AddressManager $addressManager,
        AppManager $appManager,
        AntiFraudManager $antiFraudManager,
        UserRepository $userRepository,
        GeocoderFactory $geocoderFactory,
        JourneyValidation $journeyValidation
    ) {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->communityRepository = $communityRepository;
        $this->eventManager = $eventManager;
        $this->resultManager = $resultManager;
        $this->logger = $logger;
        $this->params = $params;
        $this->proposalRepository = $proposalRepository;
        $this->matchingRepository = $matchingRepository;
        $this->criteriaRepository = $criteriaRepository;
        $this->proposalMatcher = $proposalMatcher;
        $this->askManager = $askManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->authManager = $authManager;
        $this->proofManager = $proofManager;
        $this->subjectRepository = $subjectRepository;
        $this->addressManager = $addressManager;
        $this->appManager = $appManager;
        $this->antiFraudManager = $antiFraudManager;
        $this->userRepository = $userRepository;
        $this->reversePointProvider = new GeocoderPointProvider($geocoderFactory->getGeocoder());
        if ($this->params['paymentActiveDate'] = \DateTime::createFromFormat('Y-m-d', $this->params['paymentActive'])) {
            $this->params['paymentActiveDate']->setTime(0, 0);
            $this->params['paymentActive'] = true;
        }
        $this->_journeyValidation = $journeyValidation;
        $this->_matcherCustomization = $params['matcherCustomization'];
        $this->_defaultCarpoolTimezone = $params['defaultCarpoolTimezone'];
    }

    /**
     * Create an ad.
     * This method creates a proposal, and its linked proposal for a return trip.
     * It returns the ad created, with its outward and return results.
     *
     * @param Ad     $ad                The ad to create
     * @param bool   $doPrepare         When we prepare the Proposal
     * @param bool   $withSolidaries    Return also the matching solidary asks
     * @param bool   $forceNotUseTime   For to set useTime at false
     * @param string $matchingAlgorithm Version of the matching algorithm
     *
     * @return Ad
     *
     * @throws \Exception
     */
    public function createAd(Ad $ad, bool $doPrepare = true, bool $withSolidaries = true, bool $withResults = true, $forceNotUseTime = false, string $matchingAlgorithm = Ad::MATCHING_ALGORITHM_DEFAULT)
    {
        /** Anti-Fraud check */
        $antiFraudResponse = $this->antiFraudManager->validAd($ad);
        if (!$antiFraudResponse->isValid()) {
            throw new AntiFraudException($antiFraudResponse->getMessage());
        }

        if (!$this->_matcherCustomization || !in_array($matchingAlgorithm, self::AUTHORIZED_MATCHING_ALGORITHMS)) {
            $matchingAlgorithm = Ad::MATCHING_ALGORITHM_DEFAULT;
        }

        // $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->logger->info('AdManager : start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $timezone = GeoTools::determineTimeZoneOfAd($ad, $this->_defaultCarpoolTimezone);
        $outwardProposal = new Proposal();
        $outwardCriteria = new Criteria();

        // validation

        // try for an anonymous post ?
        if (!$ad->isSearch() && !$ad->getUserId() && !$ad->isSolidaryExclusive()) {
            throw new AdException('Anonymous users can\'t post an ad');
        }

        // we set the user of the proposal
        if ($ad->getUserId()) {
            if ($user = $this->userManager->getUser($ad->getUserId())) {
                $outwardProposal->setUser($user);
            } else {
                throw new UserNotFoundException('User '.$ad->getUserId().' not found');
            }
        } elseif ($ad->getUser()) {
            // we check if the user past exist if not we create it
            if ($this->userRepository->findOneBy(['email' => $ad->getUser()->getEmail()])) {
                throw new UserAlreadyExistsException(UserAlreadyExistsException::USER_ALREADY_EXISTS);
            }
            $user = new User();
            $user->setEmail($ad->getUser()->getEmail());
            $user->setPassword($ad->getUser()->getPassword());
            $user->setGivenName($ad->getUser()->getGivenName());
            $user->setFamilyName($ad->getUser()->getFamilyName());
            $user->setBirthDate($ad->getUser()->getBirthDate());
            $user->setTelephone($ad->getUser()->getTelephone());
            $user->setGender($ad->getUser()->getGender());
            $user->setNewsSubscription(true);

            // we set the home address
            $homeAddress = new Address();
            $homeAddress->setHouseNumber($ad->getUser()->getHomeAddress()->getHouseNumber());
            $homeAddress->setStreet($ad->getUser()->getHomeAddress()->getStreet());
            $homeAddress->setStreetAddress($ad->getUser()->getHomeAddress()->getStreetAddress());
            $homeAddress->setPostalCode($ad->getUser()->getHomeAddress()->getPostalCode());
            $homeAddress->setSubLocality($ad->getUser()->getHomeAddress()->getSubLocality());
            $homeAddress->setAddressLocality($ad->getUser()->getHomeAddress()->getAddressLocality());
            $homeAddress->setLocalAdmin($ad->getUser()->getHomeAddress()->getLocalAdmin());
            $homeAddress->setCounty($ad->getUser()->getHomeAddress()->getCounty());
            $homeAddress->setMacroCounty($ad->getUser()->getHomeAddress()->getMacroCounty());
            $homeAddress->setRegion($ad->getUser()->getHomeAddress()->getRegion());
            $homeAddress->setMacroRegion($ad->getUser()->getHomeAddress()->getMacroRegion());
            $homeAddress->setAddressCountry($ad->getUser()->getHomeAddress()->getAddressCountry());
            $homeAddress->setCountryCode($ad->getUser()->getHomeAddress()->getCountryCode());
            $homeAddress->setLatitude($ad->getUser()->getHomeAddress()->getLatitude());
            $homeAddress->setLongitude($ad->getUser()->getHomeAddress()->getLongitude());

            $homeAddress->setHome(true);
            $user->addAddress($homeAddress);

            $user = $this->userManager->registerUser($user);
            $outwardProposal->setUser($user);
        }

        // we check if the ad is posted for another user (delegation)
        if ($ad->getPosterId()) {
            if ($poster = $this->userManager->getUser($ad->getPosterId())) {
                $outwardProposal->setUserDelegate($poster);
            } else {
                throw new UserNotFoundException('Poster '.$ad->getPosterId().' not found');
            }
        }

        // we check if the ad is posted from an Interoperability app
        if ($ad->getAppPosterId()) {
            if ($poster = $this->appManager->getApp($ad->getAppPosterId())) {
                $outwardProposal->setAppDelegate($poster);
            } else {
                throw new UserNotFoundException('Poster App '.$ad->getAppPosterId().' not found');
            }
        }

        // Init solidary exclusive
        if (!$ad->isSearch() && is_null($ad->isSolidaryExclusive())) {
            $ad->setSolidaryExclusive(false);
        }

        // the proposal is private if it's a search only ad
        $outwardProposal->setPrivate($ad->isSearch() ? true : false);

        // If the proposal is external (i.e Rdex request...) we set it
        $outwardProposal->setExternal($ad->getExternal());

        // if the proposal is exposed, we also generate an external id
        if ($ad->isExposed()) {
            $outwardProposal->setExposed(true);
            $outwardProposal->setExternalId();
        }

        // we check if it's a round trip
        if ($ad->isOneWay()) {
            // the ad has explicitly been set to one way
            $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
        } elseif (is_null($ad->isOneWay())) {
            // the ad type has not been set, we assume it's a round trip for a regular trip and a one way for a punctual trip
            if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
                $ad->setOneWay(false);
                $outwardProposal->setType(Proposal::TYPE_OUTWARD);
            } else {
                $ad->setOneWay(true);
                $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
            }
        } else {
            $outwardProposal->setType(Proposal::TYPE_OUTWARD);
        }

        // comment
        $outwardProposal->setComment($ad->getComment());

        // communities
        if ($ad->getCommunities()) {
            // todo : check if the user can post/search in each community
            foreach ($ad->getCommunities() as $communityId) {
                if ($community = $this->communityRepository->findOneBy(['id' => $communityId])) {
                    $outwardProposal->addCommunity($community);
                } else {
                    throw new CommunityNotFoundException('Community '.$communityId.' not found');
                }
            }
        }

        // event
        if ($ad->getEventId()) {
            if ($event = $this->eventManager->getEvent($ad->getEventId())) {
                $outwardProposal->setEvent($event);
            } else {
                throw new EventNotFoundException('Event '.$ad->getEventId().' not found');
            }
        }

        // subject
        if ($ad->getSubjectId()) {
            if ($subject = $this->subjectRepository->find($ad->getSubjectId())) {
                $outwardProposal->setSubject($subject);
            } else {
                throw new EventNotFoundException('Subject '.$ad->getSubjectId().' not found');
            }
        }

        // criteria

        // driver / passenger / seats
        $outwardCriteria->setDriver(Ad::ROLE_DRIVER == $ad->getRole() || Ad::ROLE_DRIVER_OR_PASSENGER == $ad->getRole());
        $outwardCriteria->setPassenger(Ad::ROLE_PASSENGER == $ad->getRole() || Ad::ROLE_DRIVER_OR_PASSENGER == $ad->getRole());
        $outwardCriteria->setSeatsDriver($ad->getSeatsDriver() ? $ad->getSeatsDriver() : $this->params['defaultSeatsDriver']);
        $outwardCriteria->setSeatsPassenger($ad->getSeatsPassenger() ? $ad->getSeatsPassenger() : $this->params['defaultSeatsPassenger']);

        // solidary
        $outwardCriteria->setSolidary($ad->isSolidary());
        if ($ad->getSolidaryRecord()) {
            $outwardProposal->addSolidary($ad->getSolidaryRecord());
        }
        $outwardCriteria->setSolidaryExclusive($ad->isSolidaryExclusive());

        // no destination ?
        $outwardProposal->setNoDestination($ad->hasNoDestination());

        // prices
        $outwardCriteria->setPriceKm($ad->getPriceKm());
        $outwardCriteria->setDriverPrice($ad->getOutwardDriverPrice());
        $outwardCriteria->setPassengerPrice($ad->getOutwardPassengerPrice());

        // strict
        $outwardCriteria->setStrictDate($ad->isStrictDate());
        $outwardCriteria->setStrictPunctual($ad->isStrictPunctual());
        $outwardCriteria->setStrictRegular($ad->isStrictRegular());

        // misc
        $outwardCriteria->setLuggage($ad->hasLuggage());
        $outwardCriteria->setBike($ad->hasBike());
        $outwardCriteria->setBackSeats($ad->hasBackSeats());

        // dates and times
        $marginDuration = $ad->getMarginDuration() ? $ad->getMarginDuration() : $this->params['defaultMarginDuration'];
        // if the date is not set we use the current date
        $outwardCriteria->setFromDate($ad->getOutwardDate() ? $ad->getOutwardDate() : new \DateTime());
        if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
            if (
                is_null($ad->getSchedule())
                || (is_array($ad->getSchedule()) && 0 == count($ad->getSchedule()))
            ) {
                $outwardProposal->setUseTime(false);
            } else {
                ($forceNotUseTime) ? $outwardProposal->setUseTime(false) : $outwardProposal->setUseTime(true);
            }

            $outwardCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            $outwardCriteria->setToDate($ad->getOutwardLimitDate() ? $ad->getOutwardLimitDate() : null);
            $outwardCriteria = $this->createTimesFromSchedule($ad->getSchedule(), $outwardCriteria, 'outwardTime', $marginDuration);
            $hasSchedule = $outwardCriteria->isMonCheck() || $outwardCriteria->isTueCheck()
                || $outwardCriteria->isWedCheck() || $outwardCriteria->isFriCheck() || $outwardCriteria->isThuCheck()
                || $outwardCriteria->isSatCheck() || $outwardCriteria->isSunCheck();
            if (!$hasSchedule && !$ad->isSearch()) {
                // for a post, we need a schedule !
                throw new AdException('At least one day should be selected for a regular trip');
            }
            if (!$hasSchedule) {
                // for a search we set the schedule to every day
                $outwardCriteria->setMonCheck(true);
                $outwardCriteria->setMonMarginDuration($marginDuration);
                $outwardCriteria->setTueCheck(true);
                $outwardCriteria->setTueMarginDuration($marginDuration);
                $outwardCriteria->setWedCheck(true);
                $outwardCriteria->setWedMarginDuration($marginDuration);
                $outwardCriteria->setThuCheck(true);
                $outwardCriteria->setThuMarginDuration($marginDuration);
                $outwardCriteria->setFriCheck(true);
                $outwardCriteria->setFriMarginDuration($marginDuration);
                $outwardCriteria->setSatCheck(true);
                $outwardCriteria->setSatMarginDuration($marginDuration);
                $outwardCriteria->setSunCheck(true);
                $outwardCriteria->setSunMarginDuration($marginDuration);
            }
        } else {
            // punctual
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            // if the time is not set we use the current time for an ad post, and null for a search
            // $outwardCriteria->setFromTime($ad->getOutwardTime() ? \DateTime::createFromFormat('H:i', $ad->getOutwardTime()) : (!$ad->isSearch() ? new \DateTime() : null));
            if ($ad->getOutwardTime()) {
                $outwardCriteria->setFromTime(\DateTime::createFromFormat('H:i', $ad->getOutwardTime()));
                ($forceNotUseTime) ? $outwardProposal->setUseTime(false) : $outwardProposal->setUseTime(true);
            } else {
                $outwardCriteria->setFromTime(new \DateTime('now', new \DateTimeZone($timezone)));
                $outwardProposal->setUseTime(false);
            }

            $outwardCriteria->setMarginDuration($marginDuration);
        }

        // waypoints
        foreach ($ad->getOutwardWaypoints() as $position => $point) {
            $waypoint = new Waypoint();

            $address = ($point instanceof Address) ? $point : GeoTools::createAddressFromPoint($point);

            if (is_null($address->getAddressLocality())) {
                // No address locality given. We need to reverse geocode this address

                if ($points = $this->reversePointProvider->reverse((float) $address->getLongitude(), (float) $address->getLatitude())) {
                    if (count($points) > 0) {
                        $address = AddressAdapter::pointToAddress($points[0]);
                    }
                }
            }

            $waypoint->setAddress($address);
            $waypoint->setPosition($position);
            $waypoint->setDestination($position == count($ad->getOutwardWaypoints()) - 1);
            $outwardProposal->addWaypoint($waypoint);
        }

        $outwardProposal->setCriteria($outwardCriteria);
        if ($doPrepare) {
            $outwardProposal = $this->proposalManager->prepareProposal($outwardProposal, $matchingAlgorithm);
        }

        // $this->logger->info("AdManager : end creating outward " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // $this->entityManager->persist($outwardProposal);

        // $this->logger->info("AdManager : end persisting outward " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // return trip ?
        if (!$ad->isOneWay()) {
            // we clone the outward proposal
            $returnProposal = clone $outwardProposal;
            $returnProposal->setType(Proposal::TYPE_RETURN);

            // we link the outward and the return
            $outwardProposal->setProposalLinked($returnProposal);

            // criteria
            $returnCriteria = new Criteria();

            // driver / passenger / seats
            $returnCriteria->setDriver($outwardCriteria->isDriver());
            $returnCriteria->setPassenger($outwardCriteria->isPassenger());
            $returnCriteria->setSeatsDriver($outwardCriteria->getSeatsDriver());
            $returnCriteria->setSeatsPassenger($outwardCriteria->getSeatsPassenger());

            // solidary
            $returnCriteria->setSolidary($outwardCriteria->isSolidary());
            $returnCriteria->setSolidaryExclusive($outwardCriteria->isSolidaryExclusive());

            // no destination ?
            $returnProposal->setNoDestination($outwardProposal->hasNoDestination());

            // prices
            $returnCriteria->setPriceKm($outwardCriteria->getPriceKm());
            $returnCriteria->setDriverPrice($ad->getReturnDriverPrice());
            $returnCriteria->setPassengerPrice($ad->getReturnPassengerPrice());

            // strict
            $returnCriteria->setStrictDate($outwardCriteria->isStrictDate());
            $returnCriteria->setStrictPunctual($outwardCriteria->isStrictPunctual());
            $returnCriteria->setStrictRegular($outwardCriteria->isStrictRegular());

            // misc
            $returnCriteria->setLuggage($outwardCriteria->hasLuggage());
            $returnCriteria->setBike($outwardCriteria->hasBike());
            $returnCriteria->setBackSeats($outwardCriteria->hasBackSeats());

            // dates and times
            $marginDuration = $ad->getReturnMarginDuration() ? $ad->getReturnMarginDuration() : ($ad->getMarginDuration() ? $ad->getMarginDuration() : $this->params['defaultMarginDuration']);
            // if no return date is specified, we use the outward date to be sure the return date is not before the outward date
            $returnCriteria->setFromDate($ad->getReturnDate() ? $ad->getReturnDate() : $outwardCriteria->getFromDate());
            if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
                if (
                    is_null($ad->getSchedule())
                    || (is_array($ad->getSchedule()) && 0 == count($ad->getSchedule()))
                ) {
                    $returnProposal->setUseTime(false);
                } else {
                    ($forceNotUseTime) ? $returnProposal->setUseTime(false) : $returnProposal->setUseTime(true);
                }

                $returnCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
                $returnCriteria->setToDate($ad->getReturnLimitDate() ? $ad->getReturnLimitDate() : null);
                $returnCriteria = $this->createTimesFromSchedule($ad->getSchedule(), $returnCriteria, 'returnTime', $marginDuration);
                $hasSchedule = $returnCriteria->isMonCheck() || $returnCriteria->isTueCheck()
                    || $returnCriteria->isWedCheck() || $returnCriteria->isFriCheck() || $returnCriteria->isThuCheck()
                    || $returnCriteria->isSatCheck() || $returnCriteria->isSunCheck();
                if (!$hasSchedule && !$ad->isSearch()) {
                    // for a post, we need a schedule !
                    throw new AdException('At least one day should be selected for a regular trip');
                }
                if (!$hasSchedule) {
                    // for a search we set the schedule to every day
                    $returnCriteria->setMonCheck(true);
                    $returnCriteria->setMonMarginDuration($marginDuration);
                    $returnCriteria->setTueCheck(true);
                    $returnCriteria->setTueMarginDuration($marginDuration);
                    $returnCriteria->setWedCheck(true);
                    $returnCriteria->setWedMarginDuration($marginDuration);
                    $returnCriteria->setThuCheck(true);
                    $returnCriteria->setThuMarginDuration($marginDuration);
                    $returnCriteria->setFriCheck(true);
                    $returnCriteria->setFriMarginDuration($marginDuration);
                    $returnCriteria->setSatCheck(true);
                    $returnCriteria->setSatMarginDuration($marginDuration);
                    $returnCriteria->setSunCheck(true);
                    $returnCriteria->setSunMarginDuration($marginDuration);
                }
            } else {
                // punctual
                $returnCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                // if no return time is specified, we use the outward time to be sure the return date is not before the outward date, and null for a search
                // $returnCriteria->setFromTime($ad->getReturnTime() ? \DateTime::createFromFormat('H:i', $ad->getReturnTime()) : new \DateTime("now",new \DateTimeZone($this->_defaultCarpoolTimezone)));

                if ($ad->getReturnTime()) {
                    $returnCriteria->setFromTime(\DateTime::createFromFormat('H:i', $ad->getReturnTime()));
                    ($forceNotUseTime) ? $returnProposal->setUseTime(false) : $returnProposal->setUseTime(true);
                } else {
                    $returnCriteria->setFromTime(new \DateTime('now', new \DateTimeZone($timezone)));
                    $returnProposal->setUseTime(false);
                }

                $returnCriteria->setMarginDuration($marginDuration);
            }

            // waypoints
            if (0 == count($ad->getReturnWaypoints())) {
                // return waypoints are not set : we use the outward waypoints in reverse order
                $ad->setReturnWaypoints(array_reverse($ad->getOutwardWaypoints()));
            }
            foreach ($ad->getReturnWaypoints() as $position => $point) {
                $waypoint = new Waypoint();

                $address = ($point instanceof Address) ? $point : GeoTools::createAddressFromPoint($point);

                if (is_null($address->getAddressLocality())) {
                    // No address locality given. We need to reverse geocode this address

                    if ($points = $this->reversePointProvider->reverse((float) $address->getLongitude(), (float) $address->getLatitude())) {
                        if (count($points) > 0) {
                            $address = AddressAdapter::pointToAddress($points[0]);
                        }
                    }
                }

                $waypoint->setAddress($address);
                $waypoint->setPosition($position);
                $waypoint->setDestination($position == count($ad->getReturnWaypoints()) - 1);
                $returnProposal->addWaypoint($waypoint);
            }

            $returnProposal->setCriteria($returnCriteria);
            if ($doPrepare) {
                $returnProposal = $this->proposalManager->prepareProposal($returnProposal, $matchingAlgorithm);
            }
            $this->entityManager->persist($returnProposal);
        }
        // we persist the proposals
        $this->logger->info('AdManager : start flush proposal '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $this->entityManager->flush();
        $this->logger->info('AdManager : end flush proposal '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // in the following, raw updates can be performed, we use a flag to check if proposal refresh is neede
        $proposalRefresh = false;

        // if the ad is a round trip, we want to link the potential matching results
        if (!$ad->isOneWay()) {
            $this->logger->info('AdManager : start related link matchings '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $this->matchingRepository->linkRelatedMatchings($outwardProposal->getId());
            $this->matchingRepository->linkRelatedMatchings($returnProposal->getId());
            $proposalRefresh = true;
        }
        // if the requester can be driver and passenger, we want to link the potential opposite matching results
        if (Ad::ROLE_DRIVER_OR_PASSENGER == $ad->getRole()) {
            // linking for the outward
            $this->logger->info('AdManager : start opposite link matchings '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $this->matchingRepository->linkOppositeMatchings($outwardProposal->getId());
            if (!$ad->isOneWay()) {
                // linking for the return
                $this->matchingRepository->linkOppositeMatchings($returnProposal->getId());
            }
            $this->logger->info('AdManager : end opposite link matchings '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $proposalRefresh = true;
        }
        // we load the proposal again to get the last updates
        if ($proposalRefresh) {
            $outwardProposal = $this->proposalRepository->find($outwardProposal->getId());
        }

        if (!$outwardProposal->isPrivate() && !$outwardProposal->isPaused() && !$outwardProposal->isDynamic()) {
            $matchings = array_merge($outwardProposal->getMatchingOffers(), $outwardProposal->getMatchingRequests());
            foreach ($matchings as $matching) {
                $this->entityManager->refresh($matching);
                if (is_null($matching->getMatchingOpposite())) {
                    $event = new MatchingNewEvent($matching, $outwardProposal->getUser(), $outwardProposal->getType());
                    $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
                }
            }
        }

        if (!$ad->isOneWay() && !$returnProposal->isPrivate() && !$returnProposal->isPaused() && !$returnProposal->isDynamic()) {
            $matchings = array_merge($returnProposal->getMatchingOffers(), $returnProposal->getMatchingRequests());
            foreach ($matchings as $matching) {
                $this->entityManager->refresh($matching);
                if (is_null($matching->getMatchingOpposite())) {
                    $event = new MatchingNewEvent($matching, $returnProposal->getUser(), $returnProposal->getType());
                    $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
                }
            }
        }

        // we compute the results
        if ($withResults) {
            // default order
            $ad->setFilters([
                'order' => [
                    'criteria' => 'date',
                    'value' => 'ASC',
                ],
            ]);

            $this->logger->info('AdManager : start set results '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            $results = $this->resultManager->filterResults(
                $this->resultManager->createAdResults($outwardProposal, $withSolidaries),
                $ad->getFilters()
            );
            $ad->setNbResults(count($results));
            $ad->setResults(
                $this->resultManager->paginateResults(
                    $this->resultManager->orderResults(
                        $results,
                        $ad->getFilters()
                    )
                )
            );
            // $results = $this->resultManager->orderResults(
            //     $this->resultManager->filterResults(
            //         $this->resultManager->createAdResults($outwardProposal, $withSolidaries),
            //         $ad->getFilters()
            //     ),
            //     $ad->getFilters()
            // );
            // $ad->setResults(array_slice($results,0,10));
            $this->logger->info('AdManager : end set results '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        // we set the ad id to the outward proposal id
        $ad->setId($outwardProposal->getId());
        $ad->setExternalId($outwardProposal->getExternalId());

        if (!$outwardProposal->isPrivate() && $this->_journeyValidation->isPublishedJourneyValidLongECCJourney($outwardProposal)) {
            $event = new FirstLongDistanceJourneyPublishedEvent($outwardProposal);
            $this->eventDispatcher->dispatch(FirstLongDistanceJourneyPublishedEvent::NAME, $event);
        }

        return $ad;
    }

    /**
     * Get an ad.
     * Returns the ad, eventually with its outward and return results.
     *
     * @param int        $id            The ad id to get
     * @param null|array $filters       The filters to apply to the results
     * @param null|array $order         The order to apply to the results
     * @param null|int   $page          The result page
     * @param bool       $createResults Create the formatted results
     *
     * @return Ad
     */
    public function getAd(int $id, ?array $filters = null, ?array $order = null, ?int $page = 1, ?bool $createResults = true)
    {
        if (is_null($page)) {
            $page = 1;
        }
        $ad = new Ad();
        $proposal = $this->proposalManager->get($id);
        if (is_null($proposal)) {
            return null;
        }

        $ad->setId($id);
        $ad->setExternalId($proposal->getExternalId());
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ? ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setPaused($proposal->isPaused());

        // outward waypoints
        $outwardWaypoints = [];
        foreach ($proposal->getWaypoints() as $outwardWaypointP) {
            $outwardWaypoints[] = $outwardWaypointP->getAddress();
        }
        $ad->setOutwardWaypoints($outwardWaypoints);

        // return waypoints
        if (!is_null($proposal->getProposalLinked())) {
            $returnWaypoints = [];
            foreach ($proposal->getProposalLinked()->getWaypoints() as $returnWaypointP) {
                $returnWaypoints[] = $returnWaypointP->getAddress();
            }
            $ad->setReturnWaypoints($returnWaypoints);
        }

        if (!is_null($proposal->getUser())) {
            $ad->setUserId($proposal->getUser()->getId());
        }
        $ad->setCreatedDate($proposal->getCreatedDate());

        if ($createResults) {
            // default order
            $aFilters = [
                'order' => [
                    'criteria' => 'date',
                    'value' => 'ASC',
                ],
            ];
            if (!is_null($filters)) {
                $aFilters['filters'] = $filters;
            }
            if (!is_null($order)) {
                $aFilters['order'] = $order;
            }
            $ad->setFilters($aFilters);
            $this->logger->info('AdManager : start set results '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $results = $this->resultManager->filterResults(
                $this->resultManager->createAdResults($proposal),
                $ad->getFilters()
            );
            $ad->setNbResults(count($results));
            $ad->setResults(
                $this->resultManager->paginateResults(
                    $this->resultManager->orderResults(
                        $results,
                        $ad->getFilters()
                    ),
                    $page
                )
            );
            $this->logger->info('AdManager : end set results '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        return $ad;
    }

    /**
     * Get an ad from an external Id.
     * Returns the ad, with its outward and return results.
     *
     * @param int $id The external ad id to get
     * @param null|array    The filters to apply to the results
     * @param null|array    The order to apply to the results
     *
     * @return Ad
     */
    public function getAdFromExternalId(string $id, ?array $filters = null, ?array $order = null)
    {
        $ad = new Ad();
        $proposal = $this->proposalManager->getFromExternalId($id);
        if (is_null($proposal)) {
            return null;
        }

        $ad->setId($proposal->getId());
        $ad->setExternalId($proposal->getExternalId());
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ? ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setPaused($proposal->isPaused());
        $ad->setOutwardWaypoints($proposal->getWaypoints());

        if (!is_null($proposal->getUser())) {
            $ad->setUserId($proposal->getUser()->getId());
        } else {
            // If the User is connected, we claim the search
            if ($this->security->getUser() instanceof User) {
                $this->claimAd($proposal->getId());
                $ad->setUserId($this->security->getUser()->getId());
            }
        }

        $ad->setCreatedDate($proposal->getCreatedDate());
        $aFilters = [];
        if (!is_null($filters)) {
            $aFilters['filters'] = $filters;
        }
        if (!is_null($order)) {
            $aFilters['order'] = $order;
        }
        $ad->setFilters($aFilters);
        $this->logger->info('AdManager : start set results '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $ad->setResults(
            $this->resultManager->orderResults(
                $this->resultManager->filterResults(
                    $this->resultManager->createAdResults($proposal),
                    $ad->getFilters()
                ),
                $ad->getFilters()
            )
        );
        $this->logger->info('AdManager : end set results '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $ad;
    }

    /**
     * Get an ad with full data.
     * Returns the ad, with its outward and return results.
     *
     * @param int $id The ad id to get
     *
     * @return Ad
     */
    public function getFullAd(int $id)
    {
        $proposal = $this->proposalManager->get($id);

        // get asks if proposal has asks
        $asks = $this->getAssociatedAsks($proposal);

        return $this->makeAd($proposal, $proposal->getUser()->getId(), count($asks) > 0, null, null, $asks);
    }

    /**
     * Claim a anonymous private ad.
     *
     * @param int $id The ad id to claim
     */
    public function claimAd(int $id)
    {
        if (!$proposal = $this->proposalManager->get($id)) {
            throw new AdException('Unknown source ad #'.$id);
        }
        if (!$proposal->isPrivate() || (!is_null($proposal->getUser()) && $proposal->getUser()->getId() != $this->security->getUser()->getId())) {
            throw new AdException('Acces denied');
        }

        $ad = new Ad();
        $ad->setId($id);

        // we claim the proposal
        $proposal->setUser($this->security->getUser());
        // check if there's a linked proposal
        if ($proposal->getProposalLinked()) {
            $proposal->getProposalLinked()->setUser($this->security->getUser());
        }

        $this->entityManager->persist($proposal);
        $this->entityManager->flush();

        return $ad;
    }

    /**
     * Get an ad for permission check.
     * Returns the ad based on the proposal without results.
     *
     * @param int $id The ad id to get
     *
     * @return null|Ad
     */
    public function getAdForPermission(int $id)
    {
        $ad = new Ad();
        if ($proposal = $this->proposalManager->get($id)) {
            $ad->setId($id);
            $ad->setExternalId($proposal->getExternalId());
            $ad->setFrequency($proposal->getCriteria()->getFrequency());
            $ad->setRole($proposal->getCriteria()->isDriver() ? ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
            $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
            $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
            if (!is_null($proposal->getUser())) {
                $ad->setUserId($proposal->getUser()->getId());
            }

            return $ad;
        }

        return null;
    }

    /**
     * Get all ads of a user.
     *
     * @return array
     */
    public function getAds(int $userId)
    {
        $ads = [];
        $user = $this->userManager->getUser($userId);
        $proposals = $this->proposalRepository->findBy(['user' => $user, 'private' => false]);

        $refIdProposals = [];
        foreach ($proposals as $proposal) {
            // TO DO : This if is ugly... we could use a better method in ProposalRepository
            if (Proposal::TYPE_RETURN == $proposal->getType()) {
                continue;
            }
            if (!in_array($proposal->getId(), $refIdProposals)) {
                $ads[] = $this->makeAd($proposal, $userId);
                if (!is_null($proposal->getProposalLinked())) {
                    $refIdProposals[$proposal->getId()] = $proposal->getProposalLinked()->getId();
                }
            }
        }

        return $ads;
    }

    /**
     * Get all ads of an Event.
     *
     * @param int $eventId Id of the Event
     */
    public function getAdsOfEvent(int $eventId)
    {
        $ads = [];
        $event = $this->eventManager->getEvent($eventId);

        $refIdProposals = [];
        foreach ($event->getProposals() as $proposal) {
            if (!in_array($proposal->getId(), $refIdProposals) && !$proposal->isPrivate() && !$proposal->hasExpired()) {
                $ads[] = $this->makeAdForCommunityOrEvent($proposal);
                if (!is_null($proposal->getProposalLinked())) {
                    $refIdProposals[$proposal->getId()] = $proposal->getProposalLinked()->getId();
                }
            }
        }

        return $ads;
    }

    /**
     * Make an ad from a proposal.
     *
     * @param Proposal $proposal  The base proposal of the ad
     * @param int      $userId    The userId who made the proposal
     * @param bool     $hasAsks   - if the ad has ask we do not return results since we return the ask with the ad
     * @param Ad       $askLinked - the linked ask if proposal is private and get the correct data for Ad (like time and day checks)
     * @param Matching $matching  - the corresponding Matching
     * @param mixed    $asks
     *
     * @return Ad
     */
    public function makeAd($proposal, $userId, $hasAsks = false, ?Ad $askLinked = null, ?Matching $matching = null, $asks = [])
    {
        $ad = new Ad();
        $ad->setId($proposal->getId());
        $ad->setExternalId($proposal->getExternalId());
        $ad->setProposalId($proposal->getId());
        $ad->setProposalLinkedId(!is_null($proposal->getProposalLinked()) ? $proposal->getProposalLinked()->getId() : null);
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ? ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setUserId($userId);
        $ad->setOutwardWaypoints($proposal->getWaypoints());
        $ad->setPaused($proposal->isPaused());
        $ad->setOutwardDriverPrice($proposal->getCriteria()->getDriverComputedRoundedPrice());
        $ad->setBike($proposal->getCriteria()->hasBike());
        $ad->setLuggage($proposal->getCriteria()->hasLuggage());
        $ad->setBackSeats($proposal->getCriteria()->hasBackSeats());
        $ad->setComment($proposal->getComment());
        $ad->setPriceKm(strval(floatval($proposal->getCriteria()->getPriceKm())));
        $ad->setCommunities($proposal->getCommunities());
        $ad->setAsks($asks);

        if ($matching && $matching->getProposalOffer()->getCriteria()->getFromTime()) {
            $date = $matching->getProposalOffer()->getCriteria()->getFromDate();
            $ad->setOutwardDate($date);
            $ad->setOutwardTime($date->format('Y-m-d').' '.$matching->getProposalOffer()->getCriteria()->getFromTime()->format('H:i:s'));
        } elseif ($matching && $matching->getProposalRequest()->getCriteria()->getFromTime()) {
            $date = $matching->getProposalRequest()->getCriteria()->getFromDate();
            $ad->setOutwardDate($date);
            $ad->setOutwardTime($date->format('Y-m-d').' '.$matching->getProposalRequest()->getCriteria()->getFromTime()->format('H:i:s'));
        } elseif ($proposal->getCriteria()->getFromTime()) {
            $date = $proposal->getCriteria()->getFromDate();
            $ad->setOutwardDate($date);
            $ad->setOutwardTime($date->format('Y-m-d').' '.$proposal->getCriteria()->getFromTime()->format('H:i:s'));
        } else {
            $ad->setOutwardDate($proposal->getCriteria()->getFromDate());
            $ad->setOutwardTime(null);
        }

        $ad->setOutwardLimitDate($askLinked ? $askLinked->getOutwardLimitDate() : $proposal->getCriteria()->getToDate());
        $ad->setOneWay(true);
        $ad->setSolidary($proposal->getCriteria()->isSolidary());
        $ad->setSolidaryExclusive($proposal->getCriteria()->isSolidaryExclusive());

        // set return if twoWays ad
        if ($proposal->getProposalLinked()) {
            $ad->setReturnWaypoints($proposal->getProposalLinked()->getWaypoints());
            $returnDate = $proposal->getProposalLinked()->getCriteria()->getFromDate();
            $ad->setReturnDate($returnDate);

            if ($proposal->getProposalLinked()->getCriteria()->getFromTime()) {
                $ad->setReturnTime($returnDate->format('Y-m-d').' '.$proposal->getProposalLinked()->getCriteria()->getFromTime()->format('H:i:s'));
            } else {
                $ad->setReturnTime(null);
            }

            $ad->setReturnLimitDate($proposal->getProposalLinked()->getCriteria()->getToDate());
            $ad->setOneWay(false);
        }

        // set schedule if regular
        $schedule = [];
        if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
            // schedule needs data in asks results when the user that display the Ad is not the owner
            $schedule = (!is_null($askLinked))
                ? $this->getScheduleFromResults($askLinked->getResults()[0], $proposal, $matching, $userId)
                : $this->getScheduleFromCriteria($proposal->getCriteria(), $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria() : null);
            // if schedule is based on results, we do not need to update pickup times because it's already done in results
            if (Ad::ROLE_PASSENGER === $ad->getRole() && !is_null($matching) && $matching->getPickUpDuration() && !$askLinked) {
                $schedule = $this->updateScheduleTimesWithPickUpDurations($schedule, $matching->getPickUpDuration(), $matching->getMatchingLinked() ? $matching->getMatchingLinked()->getPickUpDuration() : null);
            }
        }
        $ad->setSchedule([$schedule]);
        $results = $this->resultManager->createAdResults($proposal);
        $ad->setPotentialCarpoolers(count($results));

        if (!$hasAsks) {
            $ad->setResults($results);
        }

        return $ad;
    }

    public function getScheduleFromCriteria(Criteria $criteria, ?Criteria $returnCriteria = null)
    {
        // we clean up every days based on isDayCheck
        $schedule['mon'] = $criteria->isMonCheck() || ($returnCriteria ? $returnCriteria->isMonCheck() : false);
        $schedule['monOutwardTime'] = $criteria->isMonCheck() ? $criteria->getMonTime() : null;
        $schedule['monReturnTime'] = $returnCriteria && $returnCriteria->isMonCheck() ? $returnCriteria->getMonTime() : null;

        $schedule['tue'] = $criteria->isTueCheck() || ($returnCriteria ? $returnCriteria->isTueCheck() : false);
        $schedule['tueOutwardTime'] = $criteria->isTueCheck() ? $criteria->getTueTime() : null;
        $schedule['tueReturnTime'] = $returnCriteria && $returnCriteria->isTueCheck() ? $returnCriteria->getTueTime() : null;

        $schedule['wed'] = $criteria->isWedCheck() || ($returnCriteria ? $returnCriteria->isWedCheck() : false);
        $schedule['wedOutwardTime'] = $criteria->isWedCheck() ? $criteria->getWedTime() : null;
        $schedule['wedReturnTime'] = $returnCriteria && $returnCriteria->isWedCheck() ? $returnCriteria->getWedTime() : null;

        $schedule['thu'] = $criteria->isThuCheck() || ($returnCriteria ? $returnCriteria->isThuCheck() : false);
        $schedule['thuOutwardTime'] = $criteria->isThuCheck() ? $criteria->getThuTime() : null;
        $schedule['thuReturnTime'] = $returnCriteria && $returnCriteria->isThuCheck() ? $returnCriteria->getThuTime() : null;

        $schedule['fri'] = $criteria->isFriCheck() || ($returnCriteria ? $returnCriteria->isFriCheck() : false);
        $schedule['friOutwardTime'] = $criteria->isFriCheck() ? $criteria->getFriTime() : null;
        $schedule['friReturnTime'] = $returnCriteria && $returnCriteria->isFriCheck() ? $returnCriteria->getFriTime() : null;

        $schedule['sat'] = $criteria->isSatCheck() || ($returnCriteria ? $returnCriteria->isSatCheck() : false);
        $schedule['satOutwardTime'] = $criteria->isSatCheck() ? $criteria->getSatTime() : null;
        $schedule['satReturnTime'] = $returnCriteria && $returnCriteria->isSatCheck() ? $returnCriteria->getSatTime() : null;

        $schedule['sun'] = $criteria->isSunCheck() || ($returnCriteria ? $returnCriteria->isSunCheck() : false);
        $schedule['sunOutwardTime'] = $criteria->isSunCheck() ? $criteria->getSunTime() : null;
        $schedule['sunReturnTime'] = $returnCriteria && $returnCriteria->isSunCheck() ? $returnCriteria->getSunTime() : null;

        return $schedule;
    }

    public function getScheduleFromResults(Result $results, Proposal $proposal, Matching $matching, int $userId)
    {
        if (!$proposal->getCriteria()->isDriver() && $results->getResultDriver()) {
            $outward = $results->getResultDriver()->getOutward();
            $return = $results->getResultDriver()->getReturn();
        } elseif (!$proposal->getCriteria()->isPassenger() && $results->getResultPassenger()) {
            $outward = $results->getResultPassenger()->getOutward();
            $return = $results->getResultPassenger()->getReturn();
        } else {
            // The user registered his proposal as driver and passenger.
            // We need to know the role that he's playing in the matching
            if ($matching->getProposalOffer()->getUser()->getId() == $userId) {
                $outward = $results->getResultPassenger()->getOutward();
                $return = $results->getResultPassenger()->getReturn();
            } elseif ($matching->getProposalRequest()->getUser()->getId() == $userId) {
                $outward = $results->getResultDriver()->getOutward();
                $return = $results->getResultDriver()->getReturn();
            }
        }

        // we clean up every days based on isDayCheck
        $schedule['mon'] = $outward->isMonCheck() || ($return ? $return->isMonCheck() : null);
        $schedule['monOutwardTime'] = $outward->isMonCheck() ? $outward->getMonTime() : null;
        $schedule['monReturnTime'] = $return && $return->isMonCheck() ? $return->getMonTime() : null;

        $schedule['tue'] = $outward->isTueCheck() || ($return ? $return->isTueCheck() : null);
        $schedule['tueOutwardTime'] = $outward->isTueCheck() ? $outward->getTueTime() : null;
        $schedule['tueReturnTime'] = $return && $return->isTueCheck() ? $return->getTueTime() : null;

        $schedule['wed'] = $outward->isWedCheck() || ($return ? $return->isWedCheck() : null);
        $schedule['wedOutwardTime'] = $outward->isWedCheck() ? $outward->getWedTime() : null;
        $schedule['wedReturnTime'] = $return && $return->isWedCheck() ? $return->getWedTime() : null;

        $schedule['thu'] = $outward->isThuCheck() || ($return ? $return->isThuCheck() : null);
        $schedule['thuOutwardTime'] = $outward->isThuCheck() ? $outward->getThuTime() : null;
        $schedule['thuReturnTime'] = $return && $return->isThuCheck() ? $return->getThuTime() : null;

        $schedule['fri'] = $outward->isFriCheck() || ($return ? $return->isFriCheck() : null);
        $schedule['friOutwardTime'] = $outward->isFriCheck() ? $outward->getFriTime() : null;
        $schedule['friReturnTime'] = $return && $return->isFriCheck() ? $return->getFriTime() : null;

        $schedule['sat'] = $outward->isSatCheck() || ($return ? $return->isSatCheck() : null);
        $schedule['satOutwardTime'] = $outward->isSatCheck() ? $outward->getSatTime() : null;
        $schedule['satReturnTime'] = $return && $return->isSatCheck() ? $return->getSatTime() : null;

        $schedule['sun'] = $outward->isSunCheck() || ($return ? $return->isSunCheck() : null);
        $schedule['sunOutwardTime'] = $outward->isSunCheck() ? $outward->getSunTime() : null;
        $schedule['sunReturnTime'] = $return && $return->isSunCheck() ? $return->getSunTime() : null;

        return $schedule;
    }

    /**
     * Update a Schedule with pick up durations from a Matching
     * Used when the Ad role is passenger.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function updateScheduleTimesWithPickUpDurations(array $schedule, string $outwardPickUpDuration, ?string $returnPickUpDuration = null)
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        foreach ($days as $day) {
            if ($schedule[$day.'OutwardTime']) {
                $schedule[$day.'OutwardTime'] = $schedule[$day.'OutwardTime']->add(new \DateInterval('PT'.$outwardPickUpDuration.'S'));
            }
            if ($schedule[$day.'ReturnTime'] && $returnPickUpDuration) {
                $schedule[$day.'ReturnTime'] = $schedule[$day.'ReturnTime']->add(new \DateInterval('PT'.$returnPickUpDuration.'S'));
            }
        }

        return $schedule;
    }

    /**
     * make an ad from a proposal.
     *
     * @param Proposal $proposal Base Proposal of the Ad
     *
     * @return Ad
     */
    public function makeAdForCommunityOrEvent(Proposal $proposal)
    {
        $ad = new Ad();
        $ad->setId($proposal->getId());
        $ad->setExternalId($proposal->getExternalId());
        $ad->setUser($proposal->getUser());
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ? ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setOutwardWaypoints($proposal->getWaypoints());
        $ad->setOutwardDate($proposal->getCriteria()->getFromDate());
        $ad->setPaused($proposal->isPaused());
        if ($proposal->getCriteria()->getFromTime()) {
            $ad->setOutwardTime($ad->getOutwardDate()->format('Y-m-d').' '.$proposal->getCriteria()->getFromTime()->format('H:i:s'));
        } else {
            $ad->setOutwardTime(null);
        }
        $ad->setOutwardLimitDate($proposal->getCriteria()->getToDate());
        $ad->setOneWay(true);
        $ad->setSolidary($proposal->getCriteria()->isSolidary());
        $ad->setSolidaryExclusive($proposal->getCriteria()->isSolidaryExclusive());
        // set return if twoWays ad
        if ($proposal->getProposalLinked()) {
            $ad->setReturnWaypoints($proposal->getProposalLinked()->getWaypoints());
            $ad->setReturnDate($proposal->getProposalLinked()->getCriteria()->getFromDate());
            if ($proposal->getProposalLinked()->getCriteria()->getFromTime()) {
                $ad->setReturnTime($ad->getReturnDate()->format('Y-m-d').' '.$proposal->getProposalLinked()->getCriteria()->getFromTime()->format('H:i:s'));
            } else {
                $ad->setReturnTime(null);
            }
            $ad->setReturnLimitDate($proposal->getProposalLinked()->getCriteria()->getToDate());
            $ad->setOneWay(false);
        }
        // set schedule if regular
        $schedule = [];
        if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
            $schedule = $this->getScheduleFromCriteria($proposal->getCriteria(), $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria() : null);
        }
        $ad->setSchedule([$schedule]);

        return $ad;
    }

    /**
     * Update an ad.
     *  /!\ Only minor data can be updated
     * Otherwise we delete and create new Ad.
     *
     * @param Ad   $ad             The ad to update
     * @param bool $withSolidaries Return also the solidary asks
     *
     * @return Ad
     *
     * @throws \Exception
     */
    public function updateAd(Ad $ad, bool $withSolidaries = true)
    {
        $proposal = $this->proposalRepository->find($ad->getProposalId());
        $oldAd = $this->makeAd($proposal, $ad->getUserId());
        $proposalAsks = $this->askManager->getAsksFromProposal($proposal);
        // Pause is apart and do not needs notifications by now
        if ($ad->isPaused() !== $oldAd->isPaused()) {
            if (false == $ad->isPaused()) {
                /** Anti-Fraud check */
                $antiFraudResponse = $this->antiFraudManager->validAd($ad, true);
                if (!$antiFraudResponse->isValid()) {
                    throw new AntiFraudException($antiFraudResponse->getMessage());
                }
            }
            $proposal->setPaused($ad->isPaused());
            if ($proposal->getProposalLinked()) {
                $proposal->getProposalLinked()->setPaused($ad->isPaused());
            }
            $this->entityManager->persist($proposal);
            $this->entityManager->flush();
        } // major update
        elseif ($this->checkForMajorUpdate($oldAd, $ad)) {
            if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
                $ad->setOutwardLimitDate((new \DateTime())->modify('+ 1 year'));
                $ad->setReturnLimitDate((new \DateTime())->modify('+ 1 year'));
            }
            $ad = $this->createAd($ad, true, $withSolidaries, true, false, Ad::MATCHING_ALGORITHM_V3);
            $this->proposalManager->deleteProposal($proposal);
        // minor update
        } elseif (
            $oldAd->hasBike() !== $ad->hasBike()
            || $oldAd->hasBackSeats() !== $ad->hasBackSeats()
            || $oldAd->hasLuggage() !== $ad->hasLuggage()
            || $oldAd->getSeatsDriver() !== $ad->getSeatsDriver()
            || $oldAd->getComment() !== $ad->getComment()
            || $oldAd->getCommunities() !== $ad->getCommunities()
        ) {
            $proposal->getCriteria()->setBike($ad->hasBike());
            $proposal->getCriteria()->setBackSeats($ad->hasBackSeats());
            $proposal->getCriteria()->setLuggage($ad->hasLuggage());
            $proposal->getCriteria()->setSeatsDriver($ad->getSeatsDriver());
            $proposal->setComment($ad->getComment());

            // communities
            if ($ad->getCommunities()) {
                // todo : check if the user can post/search in each community
                foreach ($ad->getCommunities() as $communityId) {
                    if ($community = $this->communityRepository->findOneBy(['id' => $communityId])) {
                        $proposal->addCommunity($community);
                    } else {
                        throw new CommunityNotFoundException('Community '.$communityId.' not found');
                    }
                }
            }

            if ($proposal->getProposalLinked()) {
                // same if there is linked proposal
                $linkedProposal = $proposal->getProposalLinked();

                $linkedProposal->setPaused($ad->isPaused());
                $linkedProposal->getCriteria()->setBike($ad->hasBike());
                $linkedProposal->getCriteria()->setBackSeats($ad->hasBackSeats());
                $linkedProposal->getCriteria()->setLuggage($ad->hasLuggage());
                $linkedProposal->getCriteria()->setSeatsDriver($ad->getSeatsDriver());
                $linkedProposal->setComment($ad->getComment());

                if ($ad->getCommunities() && count($ad->getCommunities()) > 0) {
                    foreach ($ad->getCommunities() as $communityId) {
                        if ($community = $this->communityRepository->findOneBy(['id' => $communityId])) {
                            $linkedProposal->addCommunity($community);
                        } else {
                            throw new CommunityNotFoundException('Community '.$communityId.' not found');
                        }
                    }
                }

                $this->entityManager->persist($linkedProposal);
                $this->entityManager->flush();
            }

            if (count($proposalAsks) > 0) {
                $event = new AdMinorUpdatedEvent($oldAd, $ad, $proposalAsks, $this->security->getUser());
                $this->eventDispatcher->dispatch(AdMinorUpdatedEvent::NAME, $event);
            }
            $this->entityManager->persist($proposal);
            $this->entityManager->flush();
            $ad = $this->makeAd($proposal, $proposal->getUser()->getId());
        }

        return $ad;
    }

    /**
     * Check if Ad update needs a major update and so, deleting then creating a new one.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function checkForMajorUpdate(Ad $oldAd, Ad $newAd)
    {
        // checks for regular and punctual
        if (
            $oldAd->getPriceKm() !== number_format($newAd->getPriceKm(), 6)
            || $oldAd->getFrequency() !== $newAd->getFrequency()
            || $oldAd->getRole() !== $newAd->getRole()
            || !$this->compareWaypoints($oldAd->getOutwardWaypoints(), $newAd->getOutwardWaypoints())
        ) {
            return true;
        }

        // checks for regular only
        if (
            Criteria::FREQUENCY_REGULAR === $newAd->getFrequency()
            && !$this->compareSchedules(!empty($oldAd->getSchedule()) ? $oldAd->getSchedule()[0] : $oldAd->getSchedule(), $newAd->getSchedule())
        ) {
            return true;
        }

        // checks for punctual only
        if (
            Criteria::FREQUENCY_PUNCTUAL === $newAd->getFrequency()
            && !$this->compareDateTimes($oldAd, $newAd)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Compare Schedules
     * array_diff, array_udiff etc provide strange behavior probably due to datetime, even with callback function.
     *
     * @param mixed $old
     * @param mixed $new
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function compareSchedules($old, $new)
    {
        $adSchedule = [];
        // we create temporary schedule cause we need to keep new Ad clean to be able to create a new proposal easily if needed
        foreach ($new as $schedule) {
            if (isset($schedule['mon']) && $schedule['mon']) {
                $adSchedule['mon'] = true;
                $adSchedule['monOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['monReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['mon'])) {
                $adSchedule['mon'] = false;
                $adSchedule['monOutwardTime'] = null;
                $adSchedule['monReturnTime'] = null;
            }
            if (isset($schedule['tue']) && $schedule['tue']) {
                $adSchedule['tue'] = true;
                $adSchedule['tueOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['tueReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['tue'])) {
                $adSchedule['tue'] = false;
                $adSchedule['tueOutwardTime'] = null;
                $adSchedule['tueReturnTime'] = null;
            }
            if (isset($schedule['wed']) && $schedule['wed']) {
                $adSchedule['wed'] = true;
                $adSchedule['wedOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['wedReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['wed'])) {
                $adSchedule['wed'] = false;
                $adSchedule['wedOutwardTime'] = null;
                $adSchedule['wedReturnTime'] = null;
            }
            if (isset($schedule['thu']) && $schedule['thu']) {
                $adSchedule['thu'] = true;
                $adSchedule['thuOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['thuReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['thu'])) {
                $adSchedule['thu'] = false;
                $adSchedule['thuOutwardTime'] = null;
                $adSchedule['thuReturnTime'] = null;
            }
            if (isset($schedule['fri']) && $schedule['fri']) {
                $adSchedule['fri'] = true;
                $adSchedule['friOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['friReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['fri'])) {
                $adSchedule['fri'] = false;
                $adSchedule['friOutwardTime'] = null;
                $adSchedule['friReturnTime'] = null;
            }
            if (isset($schedule['sat']) && $schedule['sat']) {
                $adSchedule['sat'] = true;
                $adSchedule['satOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['satReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['sat'])) {
                $adSchedule['sat'] = false;
                $adSchedule['satOutwardTime'] = null;
                $adSchedule['satReturnTime'] = null;
            }
            if (isset($schedule['sun']) && $schedule['sun']) {
                $adSchedule['sun'] = true;
                $adSchedule['sunOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['sunReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['sun'])) {
                $adSchedule['sun'] = false;
                $adSchedule['sunOutwardTime'] = null;
                $adSchedule['sunReturnTime'] = null;
            }
        }

        if (!is_array($old[0]) || !is_array($adSchedule) || count($old[0]) !== count($adSchedule)) {
            return false;
        }

        $old = array_values($old[0]);
        $new = array_values($adSchedule);

        for ($i = 0; $i < count($old); ++$i) {
            if (is_bool($old[$i]) && is_bool($new[$i]) && $old[$i] !== $new[$i]) {
                return false;
            }
            if (
                is_a($old[$i], \DateTime::class) && is_null($new[$i])
                || is_a($new[$i], \DateTime::class) && is_null($old[$i])
            ) {
                return false;
            }
            if (is_a($old[$i], \DateTime::class) && is_a($new[$i], \DateTime::class)) {
                if ($old[$i]->format('Y-m-d H:i:s') !== $new[$i]->format('Y-m-d H:i:s')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Compare if new Waypoints are different from the old ones.
     *
     * @param $old - Waypoints object from a Proposal
     * @param $new - waypoint|address object from front
     *
     * @return bool
     */
    public function compareWaypoints($old, $new)
    {
        if (!is_array($old) || !is_array($new) || count($old) !== count($new)) {
            return false;
        }

        for ($i = 0; $i < count($old); ++$i) {
            if (!$old[$i] && !$new[$i]) {
                continue;
            }
            if (!isset($old[$i]) || !isset($new[$i]) || $old[$i]->getAddress()->getId() !== $new[$i]['id']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compare Date and time for Outward and Returns.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function compareDateTimes(Ad $old, Ad $new)
    {
        $oldOutwardTime = new \DateTime($old->getOutwardTime());

        // formats are different when Ad is returned by api makeAd or from front serialization
        if ($oldOutwardTime->format('H:i') !== $new->getOutwardTime()) {
            return false;
        }

        if ($old->getOutwardDate()->format('Y-m-d') !== $new->getOutwardDate()->format('Y-m-d')) {
            return false;
        }

        if (
            !is_null($old->getReturnTime()) && is_null($new->getReturnTime())
            || !is_null($new->getReturnTime()) && is_null($old->getReturnTime())
        ) {
            return false;
        }
        if ($old->getReturnTime()) {
            $oldReturnTime = new \DateTime($old->getReturnTime());
            if ($oldReturnTime->format('H:i') !== $new->getReturnTime()) {
                return false;
            }
        }

        if (
            !is_null($old->getReturnDate()) && is_null($new->getReturnDate())
            || !is_null($new->getReturnDate()) && is_null($old->getReturnDate())
        ) {
            return false;
        }
        if ($old->getReturnDate()) {
            if ($old->getReturnDate()->format('Y-m-d') !== $new->getReturnDate()->format('Y-m-d')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update all carpools limits (i.e max_detour_duration, max_detour_distance...).
     */
    public function updateCarpoolsLimits()
    {
        set_time_limit(7200);
        $criteria = $this->criteriaRepository->findDrivers();

        /**
         * @var Criteria $criterion
         */
        foreach ($criteria as $criterion) {
            $criterion->setMaxDetourDistance($criterion->getDirectionDriver()->getDistance() * $this->proposalMatcher::getMaxDetourDistancePercent() / 100);
            $criterion->setMaxDetourDuration($criterion->getDirectionDriver()->getDuration() * $this->proposalMatcher::getMaxDetourDurationPercent() / 100);
            $this->entityManager->persist($criterion);
        }
        $this->entityManager->flush();

        return ['yay!'];
    }

    /**
     * Returns an ad and its results matching the parameters.
     * Used for RDEX export.
     *
     * @param string $external The external client
     *
     * @return Ad|RdexError
     */
    public function getAdForRdex(
        ?string $external,
        bool $offer,
        bool $request,
        float $from_longitude,
        float $from_latitude,
        float $to_longitude,
        float $to_latitude,
        ?string $frequency = null,
        ?array $days = null,
        ?array $outward = null
    ) {
        $ad = new Ad();
        $ad->setExternal($external);
        $ad->setSearch(true); // Only a search. This Ad won't be publish.
        $ad->setExposed(true); // But we need to access it publicly

        // Role
        if ($offer && $request) {
            $ad->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        } elseif ($request) {
            $ad->setRole(Ad::ROLE_DRIVER);
        } else {
            $ad->setRole(Ad::ROLE_PASSENGER);
        }

        // Origin/Destination
        $ad->setOutwardWaypoints([
            [
                'latitude' => $from_latitude,
                'longitude' => $from_longitude,
            ],
            [
                'latitude' => $to_latitude,
                'longitude' => $to_longitude,
            ],
        ]);

        // Create a schedule and set frequency
        // RDEX has always a explicit day (monday...) even for puntual
        // So we always create a schedule then we make a deduction if it's punctual or regular based on it.
        // If the frequency parameter is given it overides the deduction

        // if outward is null, we make an array using now with 1 hour margin on $day bases
        if (is_null($outward)) {
            $time = new \DateTime('now', new \DateTimeZone($this->_defaultCarpoolTimezone));
            $mintime = $time->format('H:i:s');
            $maxtime = $time->add(new \DateInterval('PT1H'))->format('H:i:s');

            // if days is null, we are using today

            if (is_null($days)) {
                $today = new \DateTime('now', new \DateTimeZone($this->_defaultCarpoolTimezone));
                $days = [strtolower($today->format('l')) => 1];
                $outward = ['mindate' => $time->format('Y-m-d')];
            } else {
                // We don't have any date so i'm looking for the first date corresponding to the first day
                $dateFound = '';
                $currentTestDate = new \DateTime('now', new \DateTimeZone($this->_defaultCarpoolTimezone));
                $cpt = 0; // it's a failsafe to avoid infinit loop
                while ('' === $dateFound && $cpt < 7) {
                    if (isset($days[strtolower($currentTestDate->format('l'))])) {
                        $dateFound = $currentTestDate;
                    } else {
                        $currentTestDate = $currentTestDate->add(new \DateInterval('P1D'));
                    }
                    ++$cpt;
                }
                $outward = ['mindate' => $dateFound->format('Y-m-d')];
            }
        }
        // var_dump($outward);die;

        // if days is null, we make an array using outward
        $daysList = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        if (is_null($days)) {
            $day = [];
            $today = new \DateTime('now', new \DateTimeZone($this->_defaultCarpoolTimezone));
            foreach ($outward as $day => $times) {
                if (in_array($day, $daysList)) {
                    $days[$day] = 1;
                } elseif ('mindate' == $day) {
                    // It's the mindate field. We use it to create a day
                    $dayMinDate = new \DateTime($times);
                    $textDayMinDate = strtolower($dayMinDate->format('l'));
                    $days[$textDayMinDate] = 1;
                }
            }
        }

        // var_dump($outward);
        // var_dump($days);
        // die;

        $schedules = $this->buildSchedule($days, $outward);
        // var_dump($schedules);die;
        if (count($schedules) > 0) {
            if ('punctual' == $frequency) {
                // Punctual journey
                $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $outward['mindate']));
                (isset($outward['maxdate'])) ? $ad->setOutwardLimitDate(\DateTime::createFromFormat('Y-m-d', $outward['maxdate'])) : '';

                if (isset($schedules[0]['outwardTime'])) {
                    $ad->setOutwardTime($schedules[0]['outwardTime']);
                }
            } elseif ('regular' == $frequency) {
                // Regular journey
                $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
                $ad->setSchedule($schedules);
            } else {
                // If only one schedule with one day punctual. Else it's regular.
                if (count($schedules) > 1 || count($schedules[0]) > 2) {
                    $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
                    $ad->setSchedule($schedules);
                } else {
                    $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                    $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $outward['mindate']));
                    (isset($outward['maxdate'])) ? $ad->setOutwardLimitDate(\DateTime::createFromFormat('Y-m-d', $outward['maxdate'])) : '';

                    if (isset($schedules[0]['outwardTime'])) {
                        $ad->setOutwardTime($schedules[0]['outwardTime']);
                    }
                }
            }
        } else {
            // No schedule
            $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            if ('regular' == $frequency) {
                $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
            }
            $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $outward['mindate']));
        }

        if (!is_null($this->currentMargin)) {
            $ad->setMarginDuration($this->currentMargin);
        } else {
            $ad->setMarginDuration($this->params['defaultMarginDuration']);
        }

        return $this->createAd($ad, true, true, true, true, Ad::MATCHING_ALGORITHM_V3);
    }

    /**
     * Get ads with accepted asks of a user.
     */
    public function getUserAcceptedCarpools(int $userId): array
    {
        // array of ads
        $ads = [];
        // temporary array
        $temp = [];
        // array of ads from asks
        $askAds = [];
        $user = $this->userManager->getUser($userId);
        // We retrieve all the proposals of the user
        $proposals = $this->proposalRepository->findBy(['user' => $user]);

        // We check for each proposal if he have matching
        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            $askAdLinked = null;
            $matchingLinked = null;

            /** @var Matching $matching */
            foreach ($proposal->getMatchingRequests() as $matching) {
                // We check if the matching have an ask
                /** @var Ask $ask */
                foreach ($matching->getAsks() as $ask) {
                    // We check if the ask is accepted if yes we put the ask in the tab
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER === $ask->getStatus()) {
                        // this ask is the ask with data we want to fill the Ad
                        if ($ask->getUser()->getId() && $ask->getUser()->getId() === $userId) {
                            $askAd = $askAdLinked = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId, $matching->getProposalOffer());
                            $matchingLinked = $matching;
                        } else {
                            $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId);
                        }
                        $askAds[] = $askAd;
                    }
                }
            }
            // We check for each proposal if he have matching
            foreach ($proposal->getMatchingOffers() as $matching) {
                // We check if the matching have an ask
                foreach ($matching->getAsks() as $ask) {
                    // We check if the ask is accepted if yes we put the ask in the tab
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER === $ask->getStatus()) {
                        // this ask is the ask with data we want to fill the Ad
                        if ($ask->getUser()->getId() && $ask->getUser()->getId() === $userId) {
                            $askAd = $askAdLinked = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId, $matching->getProposalRequest());
                            $matchingLinked = $matching;
                        } else {
                            $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId);
                        }
                        $askAds[] = $askAd;
                    }
                }
            }
            // we check if the proposal have accepted asks
            if (count($askAds) > 0) {
                // if yes we create an ad with the associated asks
                // we pass the askLinked (Ad) to fill Ad with data from the ask if not null
                $ad = $this->makeAd($proposal, $userId, true, $askAdLinked, $matchingLinked);
                $ad->setAsks($askAds);
                // we put the id of the proposals linked in the temporary array
                $temp[] = $ad->getProposalLinkedId();
                // We reset the asks array for the next proposal
                $askAds = [];
                // We check if the proposal is not a proposal linked of an other proposal and regular
                if (in_array($ad->getProposalId(), $temp) && Criteria::FREQUENCY_REGULAR === $ad->getFrequency()) {
                    //  If yes we continue
                    continue;
                }                // If not we add it to the ads array

                // If payement is active we determine the general payments status of the Ad taking account of all the sub ads
                // If one sub ad is UNPAID, the general Ad is UNPAID
                // If one sub ad is PENDING, the general Ad is PENDING
                $ad->setPaymentStatus(Ask::PAYMENT_STATUS_PAID); // Default value : PAID
                if ($this->params['paymentActive']) {
                    foreach ($ad->getAsks() as $askAd) {
                        if (!is_null($askAd->getUnpaidDate())) {
                            $ad->setPaymentStatus(Ask::PAYMENT_STATUS_UNPAID);
                            $ad->setUnpaidDate($askAd->getUnpaidDate());

                            break;
                        }
                        if (Ask::PAYMENT_STATUS_PENDING == $askAd->getPaymentStatus()) {
                            $ad->setPaymentStatus(Ask::PAYMENT_STATUS_PENDING);

                            break;
                        }
                    }
                }
                $ad->setPaymentItemId($askAd->getPaymentItemId());
                $ad->setPaymentItemWeek($askAd->getPaymentItemWeek());

                $ads[] = $ad;
            }
        }

        // We return the ads array with only the ads with accepted asks associated
        return $ads;
    }

    // PROOF

    /**
     * Create a proof for an ask.
     *
     * @param ClassicProof $classicProof The proof to create
     *
     * @return ClassicProof the created proof
     */
    public function createCarpoolProof(ClassicProof $classicProof)
    {
        // search the ask
        if (!$ask = $this->askManager->getAsk($classicProof->getAskId())) {
            throw new AdException('Ask not found for classic proof');
        }

        // check that the ask is accepted
        if (!(Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus())) {
            throw new AdException('Ask not accepted');
        }

        // check if a proof already exists for this day
        if ($carpoolProof = $this->proofManager->getProofForDate($ask, new \DateTime())) {
            // the proof already exists, it's an update
            return $this->updateCarpoolProof($carpoolProof->getId(), $classicProof);
        }
        $carpoolProof = $this->proofManager->createProof($ask, $classicProof->getLongitude(), $classicProof->getLatitude(), CarpoolProof::TYPE_UNDETERMINED_CLASSIC, $classicProof->getUser(), $ask->getMatching()->getProposalOffer()->getUser(), $ask->getMatching()->getProposalRequest()->getUser(), $classicProof->getDriverPhoneUniqueId(), $classicProof->getPassengerPhoneUniqueId());

        $classicProof->setId($carpoolProof->getId());

        return $classicProof;
    }

    /**
     * Update a proof.
     *
     * @param int          $id               The id of the proof to update
     * @param ClassicProof $classicProofData The data to update the proof
     *
     * @return ClassicProof The classic proof updated
     */
    public function updateCarpoolProof(int $id, ClassicProof $classicProofData): ClassicProof
    {
        // search the proof
        if (!$carpoolProof = $this->proofManager->getProof($id)) {
            throw new AdException('Classic proof not found');
        }

        // Check if the proof has been canceled
        if (CarpoolProof::STATUS_CANCELED === $carpoolProof->getStatus()) {
            throw new AdException('Classic proof already canceled');
        }

        try {
            $carpoolProof = $this->proofManager->updateProof($id, $classicProofData->getLongitude(), $classicProofData->getLatitude(), $classicProofData->getUser(), $carpoolProof->getAsk()->getMatching()->getProposalRequest()->getUser(), $this->params['carpoolProofDistance'], $classicProofData->getDriverPhoneUniqueId(), $classicProofData->getPassengerPhoneUniqueId());
        } catch (ProofException $proofException) {
            throw new AdException($proofException->getMessage());
        }
        $classicProofData->setId($id);

        $classicProofData->setId($id);

        return $classicProofData;
    }

    /**
     * Cancel an already existing proof.
     *
     * @param int $id Proof's id to cancel
     */
    public function cancelCarpoolProof(int $id): ClassicProof
    {
        // Get the proof
        if (!$carpoolProof = $this->proofManager->getProof($id)) {
            throw new AdException('Classic proof not found');
        }

        // Cancel the proof
        $carpoolProof->setStatus(CarpoolProof::STATUS_CANCELED);
        $this->entityManager->persist($carpoolProof);
        $this->entityManager->flush();

        $classicProof = new ClassicProof();
        $classicProof->setId($carpoolProof->getId());
        $classicProof->setRegisteredStatus($carpoolProof->getStatus());

        return $classicProof;
    }

    // REFACTOR

    /**
     * Create a proposal from an Ad.
     *
     * @param Ad   $ad      The source Ad
     * @param bool $persist Persist the Proposal and related entities
     *
     * @return Ad The ad created
     */
    public function createProposalFromAd(Ad $ad, bool $persist = true)
    {
        $this->logger->info('AdManager : start createProposalFromAd '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $outwardProposal = new Proposal();
        $outwardCriteria = new Criteria();

        // validation

        // try for an anonymous post ?
        if (!$ad->isSearch() && !$ad->getUserId()) {
            throw new AdException('Anonymous users can\'t post an ad');
        }

        // we set the user of the proposal
        if ($ad->getUserId()) {
            if ($user = $this->userManager->getUser($ad->getUserId())) {
                $outwardProposal->setUser($user);
            } else {
                throw new UserNotFoundException('User '.$ad->getUserId().' not found');
            }
        }

        // we check if the ad is posted for another user (delegation)
        if ($ad->getPosterId()) {
            if ($poster = $this->userManager->getUser($ad->getPosterId())) {
                $outwardProposal->setUserDelegate($poster);
            } else {
                throw new UserNotFoundException('Poster '.$ad->getPosterId().' not found');
            }
        }

        $timezone = GeoTools::determineTimeZoneOfAd($ad, $this->_defaultCarpoolTimezone);

        // SOLIDARY TEMPORARY FIX
        // if the poster is solidary manager, we assume the Ad is solidary
        // if (isset($user)) {
        //     if ($this->authManager->isAuthorized('ROLE_SOLIDARY_MANAGER')) {
        //         $ad->setSolidary(true);
        //     }
        // }

        // the proposal is private if it's a search only ad
        $outwardProposal->setPrivate($ad->isSearch() ? true : false);

        // If the proposal is external (i.e Rdex request...) we set it
        $outwardProposal->setExternal($ad->getExternal());

        // if the proposal is exposed, we also generate an external id
        if ($ad->isExposed()) {
            $outwardProposal->setExposed(true);
            $outwardProposal->setExternalId();
        }

        // we check if it's a round trip
        if ($ad->isOneWay()) {
            // the ad has explicitly been set to one way
            $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
        } elseif (is_null($ad->isOneWay())) {
            // the ad type has not been set, we assume it's a round trip for a regular trip and a one way for a punctual trip
            if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
                $ad->setOneWay(false);
                $outwardProposal->setType(Proposal::TYPE_OUTWARD);
            } else {
                $ad->setOneWay(true);
                $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
            }
        } else {
            $outwardProposal->setType(Proposal::TYPE_OUTWARD);
        }

        // comment
        $outwardProposal->setComment($ad->getComment());

        // communities
        if ($ad->getCommunities()) {
            // todo : check if the user can post/search in each community
            foreach ($ad->getCommunities() as $communityId) {
                if ($community = $this->communityRepository->findOneBy(['id' => $communityId])) {
                    $outwardProposal->addCommunity($community);
                } else {
                    throw new CommunityNotFoundException('Community '.$communityId.' not found');
                }
            }
        }

        // event
        if ($ad->getEventId()) {
            if ($event = $this->eventManager->getEvent($ad->getEventId())) {
                $outwardProposal->setEvent($event);
            } else {
                throw new EventNotFoundException('Event '.$ad->getEventId().' not found');
            }
        }

        // subject
        if ($ad->getSubjectId()) {
            if ($subject = $this->subjectRepository->find($ad->getSubjectId())) {
                $outwardProposal->setSubject($subject);
            } else {
                throw new EventNotFoundException('Subject '.$ad->getSubjectId().' not found');
            }
        }

        // criteria

        // driver / passenger / seats
        $outwardCriteria->setDriver(Ad::ROLE_DRIVER == $ad->getRole() || Ad::ROLE_DRIVER_OR_PASSENGER == $ad->getRole());
        $outwardCriteria->setPassenger(Ad::ROLE_PASSENGER == $ad->getRole() || Ad::ROLE_DRIVER_OR_PASSENGER == $ad->getRole());
        $outwardCriteria->setSeatsDriver($ad->getSeatsDriver() ? $ad->getSeatsDriver() : $this->params['defaultSeatsDriver']);
        $outwardCriteria->setSeatsPassenger($ad->getSeatsPassenger() ? $ad->getSeatsPassenger() : $this->params['defaultSeatsPassenger']);

        // solidary
        $outwardCriteria->setSolidary($ad->isSolidary());
        $outwardCriteria->setSolidaryExclusive($ad->isSolidaryExclusive());

        // prices
        $outwardCriteria->setPriceKm($ad->getPriceKm());
        $outwardCriteria->setDriverPrice($ad->getOutwardDriverPrice());
        $outwardCriteria->setPassengerPrice($ad->getOutwardPassengerPrice());

        // strict
        $outwardCriteria->setStrictDate($ad->isStrictDate());
        $outwardCriteria->setStrictPunctual($ad->isStrictPunctual());
        $outwardCriteria->setStrictRegular($ad->isStrictRegular());

        // misc
        $outwardCriteria->setLuggage($ad->hasLuggage());
        $outwardCriteria->setBike($ad->hasBike());
        $outwardCriteria->setBackSeats($ad->hasBackSeats());

        // dates and times
        $marginDuration = $ad->getMarginDuration() ? $ad->getMarginDuration() : $this->params['defaultMarginDuration'];
        // if the date is not set we use the current date
        $outwardCriteria->setFromDate($ad->getOutwardDate() ? $ad->getOutwardDate() : new \DateTime());
        if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            $outwardCriteria->setToDate($ad->getOutwardLimitDate() ? $ad->getOutwardLimitDate() : null);
            $outwardCriteria = $this->createTimesFromSchedule($ad->getSchedule(), $outwardCriteria, 'outwardTime', $marginDuration);
            $hasSchedule = $outwardCriteria->isMonCheck() || $outwardCriteria->isTueCheck()
                || $outwardCriteria->isWedCheck() || $outwardCriteria->isFriCheck() || $outwardCriteria->isThuCheck()
                || $outwardCriteria->isSatCheck() || $outwardCriteria->isSunCheck();
            if (!$hasSchedule && !$ad->isSearch()) {
                // for a post, we need aschedule !
                throw new AdException('At least one day should be selected for a regular trip');
            }
            if (!$hasSchedule) {
                // for a search we set the schedule to every day
                $outwardCriteria->setMonCheck(true);
                $outwardCriteria->setMonMarginDuration($marginDuration);
                $outwardCriteria->setTueCheck(true);
                $outwardCriteria->setTueMarginDuration($marginDuration);
                $outwardCriteria->setWedCheck(true);
                $outwardCriteria->setWedMarginDuration($marginDuration);
                $outwardCriteria->setThuCheck(true);
                $outwardCriteria->setThuMarginDuration($marginDuration);
                $outwardCriteria->setFriCheck(true);
                $outwardCriteria->setFriMarginDuration($marginDuration);
                $outwardCriteria->setSatCheck(true);
                $outwardCriteria->setSatMarginDuration($marginDuration);
                $outwardCriteria->setSunCheck(true);
                $outwardCriteria->setSunMarginDuration($marginDuration);
            }
        } else {
            // punctual
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            if ($ad->getOutwardTime()) {
                $outwardCriteria->setFromTime(\DateTime::createFromFormat('H:i', $ad->getOutwardTime()));
                $outwardProposal->setUseTime(true);
            } else {
                $outwardCriteria->setFromTime(new \DateTime('now', new \DateTimeZone($timezone)));
                $outwardProposal->setUseTime(false);
            }
            $outwardCriteria->setMarginDuration($marginDuration);
        }

        // waypoints
        foreach ($ad->getOutwardWaypoints() as $position => $point) {
            $waypoint = new Waypoint();
            $waypoint->setAddress(($point instanceof Address) ? $point : GeoTools::createAddressFromPoint($point));
            $waypoint->setPosition($position);
            $waypoint->setDestination($position == count($ad->getOutwardWaypoints()) - 1);
            $outwardProposal->addWaypoint($waypoint);
        }

        $outwardProposal->setCriteria($outwardCriteria);
        $this->logger->info('AdManager : end creating outward '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // return trip ?
        if (!$ad->isOneWay()) {
            // we clone the outward proposal
            $returnProposal = clone $outwardProposal;
            $returnProposal->setType(Proposal::TYPE_RETURN);

            // we link the outward and the return
            $outwardProposal->setProposalLinked($returnProposal);

            // criteria
            $returnCriteria = new Criteria();

            // driver / passenger / seats
            $returnCriteria->setDriver($outwardCriteria->isDriver());
            $returnCriteria->setPassenger($outwardCriteria->isPassenger());
            $returnCriteria->setSeatsDriver($outwardCriteria->getSeatsDriver());
            $returnCriteria->setSeatsPassenger($outwardCriteria->getSeatsPassenger());

            // solidary
            $returnCriteria->setSolidary($outwardCriteria->isSolidary());
            $returnCriteria->setSolidaryExclusive($outwardCriteria->isSolidaryExclusive());

            // prices
            $returnCriteria->setPriceKm($outwardCriteria->getPriceKm());
            $returnCriteria->setDriverPrice($ad->getReturnDriverPrice());
            $returnCriteria->setPassengerPrice($ad->getReturnPassengerPrice());

            // strict
            $returnCriteria->setStrictDate($outwardCriteria->isStrictDate());
            $returnCriteria->setStrictPunctual($outwardCriteria->isStrictPunctual());
            $returnCriteria->setStrictRegular($outwardCriteria->isStrictRegular());

            // misc
            $returnCriteria->setLuggage($outwardCriteria->hasLuggage());
            $returnCriteria->setBike($outwardCriteria->hasBike());
            $returnCriteria->setBackSeats($outwardCriteria->hasBackSeats());

            // dates and times
            // if no return date is specified, we use the outward date to be sure the return date is not before the outward date
            $returnCriteria->setFromDate($ad->getReturnDate() ? $ad->getReturnDate() : $outwardCriteria->getFromDate());
            if (Criteria::FREQUENCY_REGULAR == $ad->getFrequency()) {
                $returnCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
                $returnCriteria->setToDate($ad->getReturnLimitDate() ? $ad->getReturnLimitDate() : null);
                $returnCriteria = $this->createTimesFromSchedule($ad->getSchedule(), $returnCriteria, 'returnTime', $marginDuration);
                $hasSchedule = $returnCriteria->isMonCheck() || $returnCriteria->isTueCheck()
                    || $returnCriteria->isWedCheck() || $returnCriteria->isFriCheck() || $returnCriteria->isThuCheck()
                    || $returnCriteria->isSatCheck() || $returnCriteria->isSunCheck();
                if (!$hasSchedule && !$ad->isSearch()) {
                    // for a post, we need a schedule !
                    throw new AdException('At least one day should be selected for a regular trip');
                }
                if (!$hasSchedule) {
                    // for a search we set the schedule to every day
                    $returnCriteria->setMonCheck(true);
                    $returnCriteria->setMonMarginDuration($marginDuration);
                    $returnCriteria->setTueCheck(true);
                    $returnCriteria->setTueMarginDuration($marginDuration);
                    $returnCriteria->setWedCheck(true);
                    $returnCriteria->setWedMarginDuration($marginDuration);
                    $returnCriteria->setThuCheck(true);
                    $returnCriteria->setThuMarginDuration($marginDuration);
                    $returnCriteria->setFriCheck(true);
                    $returnCriteria->setFriMarginDuration($marginDuration);
                    $returnCriteria->setSatCheck(true);
                    $returnCriteria->setSatMarginDuration($marginDuration);
                    $returnCriteria->setSunCheck(true);
                    $returnCriteria->setSunMarginDuration($marginDuration);
                }
            } else {
                // punctual
                $returnCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                if ($ad->getReturnTime()) {
                    $returnCriteria->setFromTime(\DateTime::createFromFormat('H:i', $ad->getReturnTime()));
                    $returnProposal->setUseTime(true);
                } else {
                    $returnCriteria->setFromTime(new \DateTime('now', new \DateTimeZone($timezone)));
                    $returnProposal->setUseTime(false);
                }
                $returnCriteria->setMarginDuration($marginDuration);
            }

            // waypoints
            if (0 == count($ad->getReturnWaypoints())) {
                // return waypoints are not set : we use the outward waypoints in reverse order
                $ad->setReturnWaypoints(array_reverse($ad->getOutwardWaypoints()));
            }
            foreach ($ad->getReturnWaypoints() as $position => $point) {
                $waypoint = new Waypoint();
                $waypoint->setAddress(($point instanceof Address) ? $point : GeoTools::createAddressFromPoint($point));
                $waypoint->setPosition($position);
                $waypoint->setDestination($position == count($ad->getReturnWaypoints()) - 1);
                $returnProposal->addWaypoint($waypoint);
            }

            $returnProposal->setCriteria($returnCriteria);
        }

        if ($persist) {
            $this->entityManager->persist($outwardProposal);
            $this->entityManager->flush();
            // we set the ad id to the outward proposal id
            $ad->setId($outwardProposal->getId());
            $ad->setExternalId($outwardProposal->getExternalId());
        }

        return $ad;
    }

    public function makeMapsAdFromProposal(Proposal $proposal): MapsAd
    {
        $mapsAd = new MapsAd();

        $mapsAd->setOrigin($proposal->getWaypoints()[0]->getAddress());

        $mapsAd->setDestination($proposal->getWaypoints()[count($proposal->getWaypoints()) - 1]->getAddress());

        $mapsAd->setProposalId($proposal->getId());

        $mapsAd->setOneWay(true);
        if ($proposal->getProposalLinked()) {
            $mapsAd->setOneWay(false);
        }

        $mapsAd->setRegular(false);
        if (Criteria::FREQUENCY_REGULAR == $proposal->getCriteria()->getFrequency()) {
            $mapsAd->setRegular(true);
            $mapsAd->setOutwardDate(null);
        } else {
            $mapsAd->setOutwardDate($proposal->getCriteria()->getFromDate());
        }

        $mapsAd->setCarpoolerFirstName($proposal->getUser()->getGivenName());
        $mapsAd->setCarpoolerLastName($proposal->getUser()->getShortFamilyName());

        $mapsAd->setDriver($proposal->getCriteria()->isDriver());
        $mapsAd->setPassenger($proposal->getCriteria()->isPassenger());

        return $mapsAd;
    }

    public function getAssociatedAsks(Proposal $proposal)
    {
        $userId = $proposal->getUser()->getId();
        $askAds = [];

        /** @var Matching $matching */
        foreach ($proposal->getMatchingRequests() as $matching) {
            // We check if the matching have an ask
            /** @var Ask $ask */
            foreach ($matching->getAsks() as $ask) {
                // We check if the ask is accepted if yes we put the ask in the tab
                if (Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER === $ask->getStatus()) {
                    // this ask is the ask with data we want to fill the Ad
                    if ($ask->getUser()->getId() && $ask->getUser()->getId() === $userId) {
                        $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId, $matching->getProposalOffer());
                    } else {
                        $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId);
                    }
                    $askAds[] = $askAd;
                }
            }
        }
        // We check for each proposal if he have matching
        foreach ($proposal->getMatchingOffers() as $matching) {
            // We check if the matching have an ask
            foreach ($matching->getAsks() as $ask) {
                // We check if the ask is accepted if yes we put the ask in the tab
                if (Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus() || Ask::STATUS_ACCEPTED_AS_PASSENGER === $ask->getStatus()) {
                    // this ask is the ask with data we want to fill the Ad
                    if ($ask->getUser()->getId() && $ask->getUser()->getId() === $userId) {
                        $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId, $matching->getProposalRequest());
                    } else {
                        $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId);
                    }
                    $askAds[] = $askAd;
                }
            }
        }

        return $askAds;
    }

    /**
     * Add times with margins duration to the criteria's schedules.
     *
     * @param array $schedules
     * @param int   $marginDuration
     *
     * @return Criteria
     */
    private function createTimesFromSchedule($schedules, Criteria $criteria, string $key, $marginDuration)
    {
        foreach ($schedules as $schedule) {
            if (isset($schedule[$key]) && '' != $schedule[$key]) {
                if (is_string($schedule[$key])) {
                    if (isset($schedule['mon']) && $schedule['mon']) {
                        $criteria->setMonCheck(true);
                        $criteria->setMonTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setMonMarginDuration($marginDuration);
                    }
                    if (isset($schedule['tue']) && $schedule['tue']) {
                        $criteria->setTueCheck(true);
                        $criteria->setTueTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setTueMarginDuration($marginDuration);
                    }
                    if (isset($schedule['wed']) && $schedule['wed']) {
                        $criteria->setWedCheck(true);
                        $criteria->setWedTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setWedMarginDuration($marginDuration);
                    }
                    if (isset($schedule['thu']) && $schedule['thu']) {
                        $criteria->setThuCheck(true);
                        $criteria->setThuTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setThuMarginDuration($marginDuration);
                    }
                    if (isset($schedule['fri']) && $schedule['fri']) {
                        $criteria->setFriCheck(true);
                        $criteria->setFriTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setFriMarginDuration($marginDuration);
                    }
                    if (isset($schedule['sat']) && $schedule['sat']) {
                        $criteria->setSatCheck(true);
                        $criteria->setsatTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setSatMarginDuration($marginDuration);
                    }
                    if (isset($schedule['sun']) && $schedule['sun']) {
                        $criteria->setSunCheck(true);
                        $criteria->setSunTime(\DateTime::createFromFormat('H:i', $schedule[$key]));
                        $criteria->setSunMarginDuration($marginDuration);
                    }
                } elseif ('DateTime' == get_class($schedule[$key])) {
                    if (isset($schedule['mon']) && $schedule['mon']) {
                        $criteria->setMonCheck(true);
                        $criteria->setMonTime($schedule[$key]);
                        $criteria->setMonMarginDuration($marginDuration);
                    }
                    if (isset($schedule['tue']) && $schedule['tue']) {
                        $criteria->setTueCheck(true);
                        $criteria->setTueTime($schedule[$key]);
                        $criteria->setTueMarginDuration($marginDuration);
                    }
                    if (isset($schedule['wed']) && $schedule['wed']) {
                        $criteria->setWedCheck(true);
                        $criteria->setWedTime($schedule[$key]);
                        $criteria->setWedMarginDuration($marginDuration);
                    }
                    if (isset($schedule['thu']) && $schedule['thu']) {
                        $criteria->setThuCheck(true);
                        $criteria->setThuTime($schedule[$key]);
                        $criteria->setThuMarginDuration($marginDuration);
                    }
                    if (isset($schedule['fri']) && $schedule['fri']) {
                        $criteria->setFriCheck(true);
                        $criteria->setFriTime($schedule[$key]);
                        $criteria->setFriMarginDuration($marginDuration);
                    }
                    if (isset($schedule['sat']) && $schedule['sat']) {
                        $criteria->setSatCheck(true);
                        $criteria->setsatTime($schedule[$key]);
                        $criteria->setSatMarginDuration($marginDuration);
                    }
                    if (isset($schedule['sun']) && $schedule['sun']) {
                        $criteria->setSunCheck(true);
                        $criteria->setSunTime($schedule[$key]);
                        $criteria->setSunMarginDuration($marginDuration);
                    }
                }
            }
        }

        return $criteria;
    }

    /**
     * Compute the average hour between two hours.
     *
     * @param string      $heureMin Minimum hour
     * @param string      $heureMax Maximum hour
     * @param string      $dateMin  Minimum date
     * @param null|string $dateMax  Maximum date
     *
     * @return \Datetime
     */
    private function middleHour(string $heureMin, string $heureMax, string $dateMin, ?string $dateMax = null)
    {
        (is_null($dateMax)) ? $dateMax = $dateMin : '';

        $min = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMin.' '.$heureMin, new \DateTimeZone('UTC'));
        $mintime = $min->getTimestamp();
        $max = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMax.' '.$heureMax, new \DateTimeZone('UTC'));
        $maxtime = $max->getTimestamp();
        $marge = ($maxtime - $mintime) / 2;
        $middleHour = $mintime + $marge;
        $returnHour = new \DateTime();
        $returnHour->setTimestamp($middleHour);

        return $returnHour;
    }

    /**
     * Get the difference in seconds between two times and dates.
     *
     * @return int
     */
    private function dateDiff(string $heureMin, string $heureMax, string $dateMin, ?string $dateMax = null)
    {
        $min = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMin.' '.$heureMin, new \DateTimeZone('UTC'));
        $mintime = $min->getTimestamp();
        $max = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMax.' '.$heureMax, new \DateTimeZone('UTC'));
        $maxtime = $max->getTimestamp();

        return $maxtime - $mintime;
    }

    /**
     * Build an Ad Schedule.
     *
     * @var array Array of the selected days
     * @var array Array of the time for each days
     *
     * @return array
     */
    private function buildSchedule(?array $days, ?array $outward)
    {
        $schedules = []; // We set a sub schedule because a real Ad can have multiple schedule. Only one in RDEX though.
        $refTimes = [];
        foreach ($days as $day => $value) {
            $shortDay = substr($day, 0, 3);
            if (isset($outward[$day]['mintime'])) {
                // Determine outwardTime
                if (isset($outward[$day]['mintime'], $outward[$day]['maxtime'])) {
                    // If there is a minTime and a maxTime we take the middle and compute the corresponding margin
                    // ex : minTime : 6h, maxTime : 10h => outwardTime = 8h, margin 2h
                    $outwardMaxTime = \DateTime::createFromFormat('H:i:s', $outward[$day]['maxtime'], new \DateTimeZone('UTC'));
                    $outwardMinTime = \DateTime::createFromFormat('H:i:s', $outward[$day]['mintime'], new \DateTimeZone('UTC'));
                    $diff = $outwardMaxTime->format('U') - $outwardMinTime->format('U');
                    $this->currentMargin = ($diff / 2);
                    $outwardMiddleTime = clone $outwardMinTime;
                    $outwardMiddleTime = clone $outwardMiddleTime->modify('+'.($diff / 2).' second');
                    $outwardTime = $outwardMiddleTime->format('H:i');
                } elseif (isset($outward[$day]['mintime'])) {
                    $outwardTime = \DateTime::createFromFormat('H:i:s', $outward[$day]['mintime'], new \DateTimeZone('UTC'))->format('H:i');
                } elseif (isset($outward[$day]['maxtime'])) {
                    $outwardTime = \DateTime::createFromFormat('H:i:s', $outward[$day]['maxtime'], new \DateTimeZone('UTC'))->format('H:i');
                } else {
                    return new RdexError('schedule', RdexError::ERROR_NO_MIN_MAX_TIME, 'No min or max time');
                }

                $previousKey = array_search($outwardTime, $refTimes);

                if (is_null($previousKey) || !is_numeric($previousKey)) {
                    $refTimes[] = $outwardTime;
                    $previousKey = array_search($outwardTime, $refTimes);
                    $schedules[$previousKey] = [
                        'outwardTime' => $outwardTime,
                    ];
                }

                $schedules[$previousKey][$shortDay] = 1;
            }
        }

        return $schedules;
    }
}
