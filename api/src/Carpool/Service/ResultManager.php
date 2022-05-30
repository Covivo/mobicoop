<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\ResultItem;
use App\Carpool\Entity\ResultRole;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Service\FormatDataManager;
use App\User\Entity\User;
use App\User\Repository\ReviewRepository;
use App\User\Service\BlockManager;
use App\User\Service\ReviewManager;
use DateTime;
use Symfony\Component\Security\Core\Security;

/**
 * Result manager service.
 * Used to create user-friendly results from the matching system.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ResultManager
{
    private $formatDataManager;
    private $proposalMatcher;
    private $matchingRepository;
    private $askRepository;
    private $params;
    private $security;
    private $blockManager;
    private $reviewRepository;
    private $reviewManager;
    private $userReview;
    private $carpoolNoticeableDetourDurationPercent;
    private $carpoolNoticeableDetourDistancePercent;
    private $proposalRepository;

    /**
     * Constructor.
     */
    public function __construct(
        FormatDataManager $formatDataManager,
        ProposalMatcher $proposalMatcher,
        MatchingRepository $matchingRepository,
        AskRepository $askRepository,
        Security $security,
        BlockManager $blockManager,
        ReviewRepository $reviewRepository,
        ReviewManager $reviewManager,
        ProposalRepository $proposalRepository,
        bool $userReview,
        int $carpoolNoticeableDetourDurationPercent,
        int $carpoolNoticeableDetourDistancePercent
    ) {
        $this->formatDataManager = $formatDataManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->matchingRepository = $matchingRepository;
        $this->askRepository = $askRepository;
        $this->security = $security;
        $this->blockManager = $blockManager;
        $this->reviewRepository = $reviewRepository;
        $this->reviewManager = $reviewManager;
        $this->userReview = $userReview;
        $this->carpoolNoticeableDetourDurationPercent = $carpoolNoticeableDetourDurationPercent;
        $this->carpoolNoticeableDetourDistancePercent = $carpoolNoticeableDetourDistancePercent;
        $this->proposalRepository = $proposalRepository;
    }

    // set the params
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Create "user-friendly" results from the matchings of an ad proposal.
     *
     * @param Proposal $proposal       The proposal with its matchings
     * @param bool     $withSolidaries Create also the results for solidary asks
     *
     * @return array The array of results
     */
    public function createAdResults(Proposal $proposal, bool $withSolidaries = true): array
    {
        // the outward results are the base results
        $results = $this->createProposalResults($proposal, false, $withSolidaries);
        $returnResults = [];
        if ($proposal->getProposalLinked()) {
            $returnResults = $this->createProposalResults($proposal->getProposalLinked(), true, $withSolidaries);
        }

        // the outward results are the base
        // we will have to check for each return result if it's a return of an outward result

        // we loop through the return results
        foreach ($returnResults as $result) {
            if (!is_null($result->getResultDriver())) {
                // there's a return as a driver
                if ($linkedMatching = $this->matchingRepository->findOneBy(['matchingLinked' => $result->getResultDriver()->getReturn()->getMatchingId()])) {
                    // there's a linked matching, we check if there's an outwardResult with this matching
                    if (isset($results[$linkedMatching->getProposalRequest()->getId()])) {
                        // the linked matching is in the outward results => we set the return of the outward
                        if (!is_null($results[$linkedMatching->getProposalRequest()->getId()]->getResultDriver())) {
                            // there's an outward as a driver
                            $results[$linkedMatching->getProposalRequest()->getId()]->getResultDriver()->setReturn($result->getResultDriver()->getReturn());
                        }
                        // there's no outward as a driver, but a return as a driver => for now we skip
                    }
                }
            }
            if (!is_null($result->getResultPassenger())) {
                // there's a return as a passenger
                if ($linkedMatching = $this->matchingRepository->findOneBy(['matchingLinked' => $result->getResultPassenger()->getReturn()->getMatchingId()])) {
                    // there's a linked matching, we check if there's an outwardResult with this matching
                    if (isset($results[$linkedMatching->getProposalOffer()->getId()])) {
                        // the linked matching is in the outward results => we set the return of the outward
                        if (!is_null($results[$linkedMatching->getProposalOffer()->getId()]->getResultPassenger())) {
                            // there's an outward as a driver
                            $results[$linkedMatching->getProposalOffer()->getId()]->getResultPassenger()->setReturn($result->getResultPassenger()->getReturn());
                        }
                        // there's no outward as a driver, but a return as a driver => for now we skip
                    }
                }
            }
        }

        // global origin / destination / date / time / seats / price / return
        $finalResults = [];
        foreach ($results as $originalResult) {
            $result = clone $originalResult;
            $finalResults[] = $this->createGlobalResult($result, $proposal->getWaypoints());
        }

        return $finalResults;
    }

    /**
     * Order the results.
     *
     * @param array      $results The array of results to order
     * @param null|array $order   The order criteria
     *
     * @return array The results ordered
     */
    public function orderResults(array $results, ?array $order = null): array
    {
        $criteria = null;
        $value = null;
        if (is_array($order) && isset($order['order']) && is_array($order['order']) && isset($order['order']['criteria'])) {
            $criteria = $order['order']['criteria'];
        }
        if (is_array($order) && isset($order['order']) && is_array($order['order']) && isset($order['order']['value'])) {
            $value = $order['order']['value'];
        }
        usort($results, function ($a, $b) use ($criteria, $value) {
            $return = -1;

            switch ($criteria) {
                case 'date':
                    $dateA = $timeA = null;
                    $dateB = $timeB = null;
                    if (!is_null($a->getDate())) {
                        $dateA = $a->getDate();
                        if (!is_null($a->getTime())) {
                            $timeA = $a->getTime();
                        }
                    } elseif (!is_null($a->getStartDate())) {
                        $dateA = $a->getStartDate();
                        if (!is_null($a->getOutwardTime())) {
                            $timeA = $a->getOutwardTime();
                        }
                    } else {
                        break;
                    }
                    if (!is_null($b->getDate())) {
                        $dateB = $b->getDate();
                        if (!is_null($b->getTime())) {
                            $timeB = $b->getTime();
                        }
                    } elseif (!is_null($b->getStartDate())) {
                        $dateB = $b->getStartDate();
                        if (!is_null($b->getOutwardTime())) {
                            $timeB = $b->getOutwardTime();
                        }
                    } else {
                        break;
                    }
                    if (!is_null($timeA)) {
                        $dateTimeA = \DateTime::createFromFormat('Y-m-d H:i', $dateA->format('Y-m-d').' '.$timeA->format('H:i'));
                    } else {
                        $dateTimeA = $dateA;
                    }
                    if (!is_null($timeB)) {
                        $dateTimeB = \DateTime::createFromFormat('Y-m-d H:i', $dateB->format('Y-m-d').' '.$timeB->format('H:i'));
                    } else {
                        $dateTimeB = $dateB;
                    }
                    ('ASC' == $value) ? $return = $dateTimeA <=> $dateTimeB : $return = $dateTimeB <=> $dateTimeA;

                break;
            }

            return $return;
        });

        return $results;
    }

    /**
     * Filter the results.
     *
     * @param array      $results The array of results to filter
     * @param null|array $filters The array of filters to apply (applied successively in the order of the array)
     *
     * @return array The results filtered
     */
    public function filterResults(array $results, ?array $filters = null): array
    {
        if (null !== $filters && isset($filters['filters']) && null !== $filters['filters']) {
            foreach ($filters['filters'] as $field => $value) {
                if (is_null($value)) {
                    continue;
                }
                $results = array_filter($results, function ($a) use ($field, $value) {
                    $return = true;

                    switch ($field) {
                        // Filter on Time (the hour)
                        case 'time':
                            $value = new \DateTime(str_replace('h', ':', $value));
                            if (is_null($a->getTime()) || is_null($value)) {
                                $return = false;

                                break;
                            }
                            $return = $a->getTime()->format('H') >= $value->format('H');

                            break;
                        // Filter on Role (driver, passenger, both)
                        case 'role':
                            $return = self::filterByRole($a, $value);

                            break;
                        // Filter on Gender
                        case 'gender':
                            $return = $a->getCarpooler()->getGender() == $value;

                            break;
                        // Filter on a Community
                        case 'community':
                            $return = array_key_exists($value, $a->getCommunities());

                            break;
                    }

                    return $return;
                });
            }
        }

        // We exclude the results where the current user (if he is logged) and the carpooler are involved in a block
        // Useless ?
        // $user1 = $this->security->getUser();
        // if ($user1 instanceof User) {
        //     $resultsWithoutBlock = [];
        //     foreach ($results as $result) {
        //         $user2 = $result->getCarpooler();
        //         $blocks = $this->blockManager->getInvolvedInABlock($user1, $user2);
        //         if (is_null($blocks) || (is_array($blocks) && count($blocks)==0)) {
        //             $resultsWithoutBlock[] = $result;
        //         }
        //     }
        //     return $resultsWithoutBlock;
        // }

        return $results;
    }

    /**
     * Paginate the results.
     *
     * @param array      $results The array of results to paginate
     * @param null|array $filters The array of filters to apply (applied successively in the order of the array)
     *
     * @return array The results filtered
     */
    public function paginateResults(array $results, int $page = 1, int $perPage = 10): array
    {
        return array_slice($results, (($page - 1) * $perPage), $perPage);
    }

    /**
     * Create "user-friendly" results for the asks of an ad
     * An Ad can have multiple asks, all linked (as a driver, as a passenger, each for outward and return)
     * The results are different if they are computed for the driver or the passenger.
     *
     * @param Ask $ask    The master ask
     * @param int $userId The id of the user that makes the request
     *
     * @return array The array of results
     */
    public function createAskResults(Ask $ask, int $userId): array
    {
        $result = new Result();
        $result->setId($ask->getId());

        $resultDriver = null;
        $resultPassenger = null;

        $role = Ad::ROLE_DRIVER;

        // This instead of the switch case below
        if ($ask->getMatching()->getProposalOffer()->getUser()->getId() == $userId) {
            // the requester is the driver
            $role = Ad::ROLE_DRIVER;
        } else {
            // the requester is the passenger
            $role = Ad::ROLE_PASSENGER;
        }

        // get the requester role, it depends on the status
        // switch ($ask->getStatus()) {
        //     case Ask::STATUS_INITIATED:
        //         if ($ask->getMatching()->getProposalOffer()->getUser()->getId() == $userId) {
        //             // the requester is the driver
        //             $role = Ad::ROLE_DRIVER;
        //         } else {
        //             // the requester is the passenger
        //             $role = Ad::ROLE_PASSENGER;
        //         }
        //         break;
        //     case Ask::STATUS_PENDING_AS_DRIVER:
        //     case Ask::STATUS_ACCEPTED_AS_DRIVER:
        //     case Ask::STATUS_DECLINED_AS_DRIVER:
        //         // the requester is the driver
        //         $role = Ad::ROLE_DRIVER;
        //         break;
        //     case Ask::STATUS_PENDING_AS_PASSENGER:
        //     case Ask::STATUS_ACCEPTED_AS_PASSENGER:
        //     case Ask::STATUS_DECLINED_AS_PASSENGER:
        //         // the requester is the passenger
        //         $role = Ad::ROLE_PASSENGER;
        //         break;
        // }

        // we create the ResultRole for the ask
        if (Ad::ROLE_DRIVER == $role) {
            $resultDriver = $this->createAskResultRole($ask, $role);
        } else {
            $resultPassenger = $this->createAskResultRole($ask, $role);
        }

        // we check if there's an opposite
        if ($ask->getAskOpposite()) {
            // we create the opposite ResultRole for the ask
            if (Ad::ROLE_DRIVER == $role) {
                $resultPassenger = $this->createAskResultRole($ask->getAskOpposite(), Ad::ROLE_PASSENGER);
            } else {
                $resultDriver = $this->createAskResultRole($ask->getAskOpposite(), Ad::ROLE_DRIVER);
            }
        }

        $result->setResultDriver($resultDriver);
        $result->setResultPassenger($resultPassenger);

        // create the global result
        if ($ask->getUser()->getId() == $userId) {
            $carpooler = $ask->getUserRelated();
            $currentUser = $ask->getUser();
        } else {
            $carpooler = $ask->getUser();
            $currentUser = $ask->getUserRelated();
        }

        $carpooler = $this->canReceiveReview($currentUser, $carpooler);

        $result->setCarpooler($carpooler);
        $result->setFrequency($ask->getCriteria()->getFrequency());
        $result->setFrequencyResult($ask->getCriteria()->getFrequency());

        return $this->createGlobalResult($result, $ask->getWaypoints());
        // return the result
    }

    /**
     * Create "user-friendly" results for the asks of an ad
     * An Ad can have multiple asks, all linked (as a driver, as a passenger, each for outward and return)
     * The results are different if they are computed for the driver or the passenger
     * In that case, since the carpool is accepted we know the role so we only need the result of that role.
     *
     * @param Ask $ask    The master ask
     * @param int $userId The id of the user that makes the request
     *
     * @return Result The array of results
     */
    public function createSimpleAskResults(Ask $ask, int $userId, int $role): Result
    {
        $result = new Result();
        $result->setId($ask->getId());

        $resultDriver = null;
        $resultPassenger = null;

        if (Ad::ROLE_DRIVER == $role) {
            $resultDriver = $this->createAskResultRole($ask, $role);
        } else {
            $resultPassenger = $this->createAskResultRole($ask, $role);
        }

        $result->setResultDriver($resultDriver);
        $result->setResultPassenger($resultPassenger);

        // create the global result
        if ($ask->getUser()->getId() == $userId) {
            $carpooler = $ask->getUserRelated();
            $currentUser = $ask->getUser();
        } else {
            $carpooler = $ask->getUser();
            $currentUser = $ask->getUserRelated();
        }

        $carpooler = $this->canReceiveReview($currentUser, $carpooler);
        $result->setCarpooler($carpooler);
        $result->setFrequency($ask->getCriteria()->getFrequency());
        $result->setFrequencyResult($ask->getCriteria()->getFrequency());

        return $this->createGlobalResult($result, $ask->getWaypoints());
        // return the result
    }

    /**
     * Complete the global result.
     */
    private function createGlobalResult(Result $result, array $waypoints): Result
    {
        // origin / destination
        // We always display the origin and destination of the driver
        // We set the pickup address which is the passenger's origin
        // if the carpooler can be driver and passenger, we choose to consider him as driver as he's the first to publish
        // we also set the originFirst and destinationLast to indicate if the driver origin / destination are different than the passenger ones

        // we first get the origin and destination of the requester
        $requesterOrigin = null;
        $requesterDestination = null;
        foreach ($waypoints as $waypoint) {
            if (0 == $waypoint->getPosition()) {
                $requesterOrigin = $waypoint->getAddress();
            }
            if ($waypoint->isDestination()) {
                $requesterDestination = $waypoint->getAddress();
            }
        }
        if ($result->getResultDriver() && !$result->getResultPassenger()) {
            // the carpooler is passenger only
            $result->setPickUpOutward($result->getResultDriver()->getOutward()->getOrigin());
            $result->setOrigin($result->getResultDriver()->getOutward()->getOriginDriver());
            $result->setDestination($result->getResultDriver()->getOutward()->getDestinationDriver());
            // we check if his origin and destination are first and last of the whole journey
            // we use the gps coordinates
            $result->setOriginFirst(false);
            if ($result->getOrigin()->getLatitude() == $requesterOrigin->getLatitude() && $result->getOrigin()->getLongitude() == $requesterOrigin->getLongitude()) {
                $result->setOriginFirst(true);
            }
            $result->setDestinationLast(false);
            if ($result->getDestination()->getLatitude() == $requesterDestination->getLatitude() && $result->getDestination()->getLongitude() == $requesterDestination->getLongitude()) {
                $result->setDestinationLast(true);
            }
        } else {
            // the carpooler can be driver
            $result->setPickUpOutward($requesterOrigin);
            $result->setOrigin($result->getResultPassenger()->getOutward()->getOrigin());
            $result->setDestination($result->getResultPassenger()->getOutward()->getDestination());

            // we check if his origin and destination are first and last of the whole journey
            // we use the gps coordinates
            $result->setOriginFirst(false);
            if ($result->getOrigin()->getLatitude() == $result->getResultPassenger()->getOutward()->getOrigin()->getLatitude() && $result->getOrigin()->getLongitude() == $result->getResultPassenger()->getOutward()->getOrigin()->getLongitude()) {
                $result->setOriginFirst(true);
            }
            $result->setDestinationLast(false);
            if ($result->getDestination()->getLatitude() == $result->getResultPassenger()->getOutward()->getDestination()->getLatitude() && $result->getDestination()->getLongitude() == $result->getResultPassenger()->getOutward()->getDestination()->getLongitude()) {
                $result->setDestinationLast(true);
            }
        }

        // date / time / seats / price
        // if the request is regular, there is no date, but we keep a start date
        // otherwise we display the date of the matching proposal computed before depending on if the carpooler can be driver and/or passenger
        if ($result->getResultDriver() && !$result->getResultPassenger()) {
            // the carpooler is passenger only
            if (Criteria::FREQUENCY_PUNCTUAL == $result->getFrequency()) {
                $result->setDate($result->getResultDriver()->getOutward()->getDate());
                $result->setTime($result->getResultDriver()->getOutward()->getTime());
            } else {
                $result->setStartDate($result->getResultDriver()->getOutward()->getFromDate());
                $result->setToDate($result->getResultDriver()->getOutward()->getToDate());
            }
            $result->setPrice($result->getResultDriver()->getOutward()->getComputedPrice());
            $result->setRoundedPrice($result->getResultDriver()->getOutward()->getComputedRoundedPrice());
            $result->setSeatsDriver($result->getResultDriver()->getSeatsDriver());
            $result->setSeatsPassenger($result->getResultDriver()->getSeatsPassenger());
            $result->setSeats($result->getResultDriver()->getSeatsPassenger());
        } else {
            // the carpooler is driver or passenger
            if (Criteria::FREQUENCY_PUNCTUAL == $result->getFrequency()) {
                $result->setDate($result->getResultPassenger()->getOutward()->getDate());
                $result->setTime($result->getResultPassenger()->getOutward()->getTime());
            } else {
                $result->setDate($result->getResultPassenger()->getOutward()->getDate());
                $result->setTime($result->getResultPassenger()->getOutward()->getTime());
                $result->setStartDate($result->getResultPassenger()->getOutward()->getFromDate());
                $result->setToDate($result->getResultPassenger()->getOutward()->getToDate());
            }
            $result->setPrice($result->getResultPassenger()->getOutward()->getComputedPrice());
            $result->setRoundedPrice($result->getResultPassenger()->getOutward()->getComputedRoundedPrice());
            $result->setSeatsDriver($result->getResultPassenger()->getSeatsDriver());
            $result->setSeatsPassenger($result->getResultPassenger()->getSeatsPassenger());
            $result->setSeats($result->getResultPassenger()->getSeatsDriver());
        }
        // regular days and times
        if (Criteria::FREQUENCY_REGULAR == $result->getFrequencyResult()) {
            if ($result->getResultDriver() && !$result->getResultPassenger()) {
                // the carpooler is passenger only
                $result->setMonCheck($result->getResultDriver()->getOutward()->isMonCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isMonCheck()));
                $result->setTueCheck($result->getResultDriver()->getOutward()->isTueCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isTueCheck()));
                $result->setWedCheck($result->getResultDriver()->getOutward()->isWedCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isWedCheck()));
                $result->setThuCheck($result->getResultDriver()->getOutward()->isThuCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isThuCheck()));
                $result->setFriCheck($result->getResultDriver()->getOutward()->isFriCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isFriCheck()));
                $result->setSatCheck($result->getResultDriver()->getOutward()->isSatCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isSatCheck()));
                $result->setSunCheck($result->getResultDriver()->getOutward()->isSunCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isSunCheck()));
                if (!$result->getResultDriver()->getOutward()->hasMultipleTimes()) {
                    if ($result->getResultDriver()->getOutward()->getMonTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getMonTime());
                    } elseif ($result->getResultDriver()->getOutward()->getTueTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getTueTime());
                    } elseif ($result->getResultDriver()->getOutward()->getWedTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getWedTime());
                    } elseif ($result->getResultDriver()->getOutward()->getThuTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getThuTime());
                    } elseif ($result->getResultDriver()->getOutward()->getFriTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getFriTime());
                    } elseif ($result->getResultDriver()->getOutward()->getSatTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getSatTime());
                    } elseif ($result->getResultDriver()->getOutward()->getSunTime()) {
                        $result->setOutwardTime($result->getResultDriver()->getOutward()->getSunTime());
                    }
                }
                if ($result->getResultDriver()->getReturn() && !$result->getResultDriver()->getReturn()->hasMultipleTimes()) {
                    if ($result->getResultDriver()->getReturn()->getMonTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getMonTime());
                    } elseif ($result->getResultDriver()->getReturn()->getTueTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getTueTime());
                    } elseif ($result->getResultDriver()->getReturn()->getWedTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getWedTime());
                    } elseif ($result->getResultDriver()->getReturn()->getThuTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getThuTime());
                    } elseif ($result->getResultDriver()->getReturn()->getFriTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getFriTime());
                    } elseif ($result->getResultDriver()->getReturn()->getSatTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getSatTime());
                    } elseif ($result->getResultDriver()->getReturn()->getSunTime()) {
                        $result->setReturnTime($result->getResultDriver()->getReturn()->getSunTime());
                    }
                }
            } else {
                // the carpooler is driver or passenger
                $result->setMonCheck($result->getResultPassenger()->getOutward()->isMonCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isMonCheck()));
                $result->setTueCheck($result->getResultPassenger()->getOutward()->isTueCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isTueCheck()));
                $result->setWedCheck($result->getResultPassenger()->getOutward()->isWedCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isWedCheck()));
                $result->setThuCheck($result->getResultPassenger()->getOutward()->isThuCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isThuCheck()));
                $result->setFriCheck($result->getResultPassenger()->getOutward()->isFriCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isFriCheck()));
                $result->setSatCheck($result->getResultPassenger()->getOutward()->isSatCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isSatCheck()));
                $result->setSunCheck($result->getResultPassenger()->getOutward()->isSunCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isSunCheck()));
                if (!$result->getResultPassenger()->getOutward()->hasMultipleTimes()) {
                    if ($result->getResultPassenger()->getOutward()->getMonTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getMonTime());
                    } elseif ($result->getResultPassenger()->getOutward()->getTueTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getTueTime());
                    } elseif ($result->getResultPassenger()->getOutward()->getWedTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getWedTime());
                    } elseif ($result->getResultPassenger()->getOutward()->getThuTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getThuTime());
                    } elseif ($result->getResultPassenger()->getOutward()->getFriTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getFriTime());
                    } elseif ($result->getResultPassenger()->getOutward()->getSatTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getSatTime());
                    } elseif ($result->getResultPassenger()->getOutward()->getSunTime()) {
                        $result->setOutwardTime($result->getResultPassenger()->getOutward()->getSunTime());
                    }
                }
                if ($result->getResultPassenger()->getReturn() && !$result->getResultPassenger()->getReturn()->hasMultipleTimes()) {
                    if ($result->getResultPassenger()->getReturn()->getMonTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getMonTime());
                    } elseif ($result->getResultPassenger()->getReturn()->getTueTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getTueTime());
                    } elseif ($result->getResultPassenger()->getReturn()->getWedTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getWedTime());
                    } elseif ($result->getResultPassenger()->getReturn()->getThuTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getThuTime());
                    } elseif ($result->getResultPassenger()->getReturn()->getFriTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getFriTime());
                    } elseif ($result->getResultPassenger()->getReturn()->getSatTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getSatTime());
                    } elseif ($result->getResultPassenger()->getReturn()->getSunTime()) {
                        $result->setReturnTime($result->getResultPassenger()->getReturn()->getSunTime());
                    }
                }
            }
        }

        // return trip ?
        $result->setReturn(false);
        if ($result->getResultDriver() && !$result->getResultPassenger()) {
            // the carpooler is passenger only
            if (!is_null($result->getResultDriver()->getReturn())) {
                $result->setReturn(true);
                $result->setPickUpReturn($result->getResultDriver()->getReturn()->getOrigin());
            }
        } else {
            // the carpooler is driver or passenger
            if (!is_null($result->getResultPassenger()->getReturn())) {
                $result->setReturn(true);
                $result->setPickUpReturn($requesterDestination);
            }
        }

        // pending or accepted ask linked ?
        $result->setPendingAsk(false);
        $result->setAcceptedAsk(false);
        $result->setInitiatedAsk(false);
        if ($result->getResultDriver()) {
            if ($result->getResultDriver()->getOutward()) {
                if ($result->getResultDriver()->getOutward()->hasInitiatedAsk()) {
                    $result->setInitiatedAsk(true);
                }
                if ($result->getResultDriver()->getOutward()->hasPendingAsk()) {
                    $result->setPendingAsk(true);
                }
                if ($result->getResultDriver()->getOutward()->hasAcceptedAsk()) {
                    $result->setAcceptedAsk(true);
                }
            }
            if ($result->getResultDriver()->getReturn()) {
                if ($result->getResultDriver()->getReturn()->hasInitiatedAsk()) {
                    $result->setInitiatedAsk(true);
                }
                if ($result->getResultDriver()->getReturn()->hasPendingAsk()) {
                    $result->setPendingAsk(true);
                }
                if ($result->getResultDriver()->getReturn()->hasAcceptedAsk()) {
                    $result->setAcceptedAsk(true);
                }
            }
        }
        if ($result->getResultPassenger()) {
            if ($result->getResultPassenger()->getOutward()) {
                if ($result->getResultPassenger()->getOutward()->hasInitiatedAsk()) {
                    $result->setInitiatedAsk(true);
                }
                if ($result->getResultPassenger()->getOutward()->hasPendingAsk()) {
                    $result->setPendingAsk(true);
                }
                if ($result->getResultPassenger()->getOutward()->hasAcceptedAsk()) {
                    $result->setAcceptedAsk(true);
                }
            }
            if ($result->getResultPassenger()->getReturn()) {
                if ($result->getResultPassenger()->getReturn()->hasInitiatedAsk()) {
                    $result->setInitiatedAsk(true);
                }
                if ($result->getResultPassenger()->getReturn()->hasPendingAsk()) {
                    $result->setPendingAsk(true);
                }
                if ($result->getResultPassenger()->getReturn()->hasAcceptedAsk()) {
                    $result->setAcceptedAsk(true);
                }
            }
        }

        return $result;
    }

    /**
     * Create results for an outward or a return proposal.
     *
     * @param Proposal $proposal       The proposal
     * @param bool     $return         The result is for the return trip
     * @param bool     $withSolidaries Create also the results for solidary asks
     */
    private function createProposalResults(Proposal $proposal, bool $return = false, bool $withSolidaries = true): array
    {
        $results = [];
        // we group the matchings by matching proposalId to merge potential driver and/or passenger candidates
        $matchings = [];
        // we search the matchings as an offer
        /** @var Matching $request */
        foreach ($proposal->getMatchingRequests() as $request) {
            // we exclude the private proposals
            if ($request->getProposalRequest()->isPrivate() || $request->getProposalRequest()->isPaused()) {
                continue;
            }
            // do we exclude solidary requests ?
            if (!$withSolidaries && $request->getProposalRequest()->getCriteria()->isSolidary()) {
                continue;
            }
            // we check if the route hasn't been computed, or if the matching is not complete (we check one of the properties that must be filled if the matching is complete)
            if (is_null($request->getFilters() && is_null($request->getPickUpDuration()))) {
                $request->setFilters($this->proposalMatcher->getMatchingFilters($request));
            }

            $matchings[$request->getProposalRequest()->getId()]['request'] = $request;
        }
        // we search the matchings as a request
        foreach ($proposal->getMatchingOffers() as $offer) {
            // we exclude the private proposals
            if ($offer->getProposalOffer()->isPrivate() || $offer->getProposalOffer()->isPaused()) {
                continue;
            }
            // we check if the route hasn't been computed, or if the matching is not complete (we check one of the properties that must be filled if the matching is complete)
            if (is_null($offer->getFilters() && is_null($offer->getPickUpDuration()))) {
                $offer->setFilters($this->proposalMatcher->getMatchingFilters($offer));
            }
            $matchings[$offer->getProposalOffer()->getId()]['offer'] = $offer;
        }

        // we iterate through the matchings to create the results
        foreach ($matchings as $matchingProposalId => $matching) {
            // If these matchings is between two Users involved in a block, we skip it
            $blockedRequest = $blockedOffer = false;
            if (isset($matching['request'])) {
                $user1 = $matching['request']->getProposalOffer()->getUser();
                $user2 = $matching['request']->getProposalRequest()->getUser();
                // a user may be null in case of anonymous search
                if ($user1 && $user2) {
                    $blocks = $this->blockManager->getInvolvedInABlock($user1, $user2);
                    if (is_array($blocks) && count($blocks) > 0) {
                        $blockedRequest = true;
                    }
                }
                $matchingProposal = $matching['request']->getProposalRequest();
            }
            if (isset($matching['offer'])) {
                $user1 = $matching['offer']->getProposalOffer()->getUser();
                $user2 = $matching['offer']->getProposalRequest()->getUser();
                // a user may be null in case of anonymous search
                if ($user1 && $user2) {
                    $blocks = $this->blockManager->getInvolvedInABlock($user1, $user2);
                    if (is_array($blocks) && count($blocks) > 0) {
                        $blockedOffer = true;
                    }
                }
                $matchingProposal = $matching['offer']->getProposalOffer();
            }

            if (!$blockedRequest && !$blockedOffer) {
                $result = $this->createMatchingResult($proposal, $matchingProposal, $matching, $return);
                $results[$matchingProposalId] = $result;
            }
        }

        return $results;
    }

    /**
     * Create results for a given matching of a proposal.
     *
     * @param Proposal $proposal         The proposal
     * @param Proposal $matchingProposal The proposal that matches
     * @param array    $matching         The array of the matchings of the proposal (an array with the matching proposal as offer and/or request)
     * @param bool     $return           The matching concerns a return (=false if it's the outward)
     *
     * @return Result The result object
     */
    private function createMatchingResult(Proposal $proposal, Proposal $matchingProposal, array $matching, bool $return): Result
    {
        $result = new Result();
        $result->setId($proposal->getId());
        $result->setUserId($proposal->getId());
        $resultDriver = new ResultRole();
        $resultPassenger = new ResultRole();
        $communities = [];

        // Will be used to determine the role (driver, pasenger, both) of the result
        $driver = false;
        $passenger = false;

        // REQUEST

        if (isset($matching['request'])) {
            // the carpooler can be passenger
            if (is_null($result->getFrequency())) {
                $result->setFrequency($matching['request']->getCriteria()->getFrequency());
            }
            if (is_null($result->getFrequencyResult())) {
                $result->setFrequencyResult($matching['request']->getProposalRequest()->getCriteria()->getFrequency());
            }
            if (is_null($result->getCarpooler())) {
                $carpooler = $matching['request']->getProposalRequest()->getUser();
                // Clone doesn't treat avatars as it's a loadListener
                $resultCarpooler = clone $carpooler;
                $resultCarpooler->setAvatars($carpooler->getAvatars());
                $resultCarpooler->setExperienced($carpooler->isExperienced());
                $result->setCarpooler($resultCarpooler);
                // We check if we have accepted carpool if yes we display the carpooler phone number
                $hasAsk = false;
                $asks = $matching['request']->getAsks();
                foreach ($asks as $ask) {
                    if ($ask->getStatus() == (ASK::STATUS_ACCEPTED_AS_DRIVER || ASK::STATUS_ACCEPTED_AS_PASSENGER)) {
                        $hasAsk = true;

                        break;
                    }
                }
                // if we don't have accepted carpools AND (no user is logged OR the phone display is restricted) we pass the telephone at null
                if (!$hasAsk && (!$matching['request']->getProposalOffer()->getUser() || USER::PHONE_DISPLAY_RESTRICTED == $carpooler->getPhoneDisplay())) {
                    $result->getCarpooler()->setTelephone(null);
                }
            }
            if (is_null($result->getComment()) && !is_null($matching['request']->getProposalRequest()->getComment())) {
                $result->setComment($matching['request']->getProposalRequest()->getComment());
            }
            if (is_null($result->getAskId()) && !empty($matching['request']->getAsks())) {
                $result->setAskId($matching['request']->getAsks()[0]->getId());
            }

            // solidary : the request can be solidary
            $result->setSolidary($matching['request']->getProposalRequest()->getCriteria()->isSolidary());
            $result->setSolidaryExclusive(false);

            // communities
            foreach ($matching['request']->getProposalRequest()->getCommunities() as $community) {
                $communities[$community->getId()] = [
                    'name' => $community->getName(),
                    'image' => $community->getImages(),
                ];
            }

            // outward
            $item = new ResultItem();
            // we set the proposalId
            $item->setProposalId($matchingProposal->getId());
            if (Matching::DEFAULT_ID !== $matching['request']->getId()) {
                $item->setMatchingId($matching['request']->getId());
            }
            if (Criteria::FREQUENCY_PUNCTUAL == $proposal->getCriteria()->getFrequency()) {
                // the search/ad proposal is punctual
                // we have to calculate the date and time of the carpool
                // date :
                // - if the matching proposal is also punctual, it's the date of the matching proposal (as the date of the matching proposal could be the same or after the date of the search/ad)
                // - if the matching proposal is regular, it's the date of the search/ad (as the matching proposal "matches", it means that the date is valid => the date is in the range of the regular matching proposal)
                if (Criteria::FREQUENCY_PUNCTUAL == $matching['request']->getProposalRequest()->getCriteria()->getFrequency()) {
                    $item->setDate($matching['request']->getProposalRequest()->getCriteria()->getFromDate());
                } else {
                    $regularDayInfos = $this->getMatchingRegularDayAsOffer($matching['request'], $item);
                    $item = $regularDayInfos['item'];
                    $fromTime = $regularDayInfos['time'];
                }

                // time
                // the carpooler is passenger, the proposal owner is driver : we use his time if it's set
                // if the proposal is dynamic, we take the updated time of the position linked with the proposal
                if ($matching['request']->getProposalOffer()->isDynamic()) {
                    $item->setTime($matching['request']->getProposalOffer()->getPosition()->getUpdatedDate());
                } else {
                    // the time is not set, it must be the matching results of a search (and not an ad)
                    // we have to calculate the starting time so that the driver will get the carpooler on the carpooler time
                    // we init the time to the one of the carpooler
                    if (Criteria::FREQUENCY_PUNCTUAL == $matching['request']->getProposalRequest()->getCriteria()->getFrequency()) {
                        // the carpooler proposal is punctual, we take the fromTime
                        $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getFromTime();
                        $item->setMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getMarginDuration());
                    }

                    // we search the pickup duration
                    $pickupDuration = null;
                    if (!is_null($matching['request']->getPickUpDuration())) {
                        $pickupDuration = $matching['request']->getPickUpDuration();
                    } else {
                        $filters = $matching['request']->getFilters();
                        foreach ($filters['route'] as $value) {
                            if (2 == $value['candidate'] && 0 == $value['position']) {
                                $pickupDuration = (int) round($value['duration']);

                                break;
                            }
                        }
                    }
                    if ($pickupDuration) {
                        $fromTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                    }
                    $item->setTime($fromTime);
                }
            } else {
                if (Criteria::FREQUENCY_PUNCTUAL == $matching['request']->getCriteria()->getFrequency()) {
                    // Set the date to the carpooler's date
                    $item->setDate($matching['request']->getProposalRequest()->getCriteria()->getFromDate());
                    $item->setMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getMarginDuration());
                    // the carpooler proposal is punctual, we have to take the proposal time matching to the carpooler day
                    $fromTime = new \DateTime();

                    switch ($matching['request']->getProposalRequest()->getCriteria()->getFromDate()->format('w')) {
                        case 0:
                            $fromTime = clone $proposal->getCriteria()->getSunTime();
                            $item->setMonMarginDuration($proposal->getCriteria()->getMonMarginDuration());

                            break;

                        case 1:
                            $fromTime = clone $proposal->getCriteria()->getMonTime();
                            $item->setTueMarginDuration($proposal->getCriteria()->getTueMarginDuration());

                            break;

                        case 2:
                            $fromTime = clone $proposal->getCriteria()->getTueTime();
                            $item->setWedMarginDuration($proposal->getCriteria()->getWedMarginDuration());

                            break;

                        case 3:
                            $fromTime = clone $proposal->getCriteria()->getWedTime();
                            $item->setThuMarginDuration($proposal->getCriteria()->getThuMarginDuration());

                            break;

                        case 4:
                            $fromTime = clone $proposal->getCriteria()->getThuTime();
                            $item->setFriMarginDuration($proposal->getCriteria()->getFriMarginDuration());

                            break;

                        case 5:
                            $fromTime = clone $proposal->getCriteria()->getFriTime();
                            $item->setSatMarginDuration($proposal->getCriteria()->getSatMarginDuration());

                            break;

                        case 6:
                            $fromTime = clone $proposal->getCriteria()->getSatTime();
                            $item->setSunMarginDuration($proposal->getCriteria()->getSunMarginDuration());

                            break;
                    }

                    $item->setTime($fromTime);
                } else {
                    // the search or ad is regular => no date
                    // we have to find common days (if it's a search the common days should be the carpooler days)
                    // we check if pickup times have been calculated already
                    if (isset($matching['request']->getFilters()['pickup'])) {
                        // we have pickup times, it must be the matching results of an ad (and not a search)
                        // the carpooler is passenger, the proposal owner is driver : we use his time as it must be set
                        // we use the times even if we don't use them, maybe we'll need them in the future
                        // we set the global time for each day, we will erase it if we discover that all days have not the same time
                        // this way we are sure that if all days have the same time, the global time will be set and ok
                        if (isset($matching['request']->getFilters()['pickup']['monMinPickupTime'], $matching['request']->getFilters()['pickup']['monMaxPickupTime'])) {
                            $item->setMonCheck(true);
                            $item->setMonTime($proposal->getCriteria()->getMonTime());
                            $item->setTime($proposal->getCriteria()->getMonTime());
                            $item->setMonMarginDuration($proposal->getCriteria()->getMonMarginDuration());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['tueMinPickupTime'], $matching['request']->getFilters()['pickup']['tueMaxPickupTime'])) {
                            $item->setTueCheck(true);
                            $item->setTueTime($proposal->getCriteria()->getTueTime());
                            $item->setTime($proposal->getCriteria()->getTueTime());
                            $item->setTueMarginDuration($proposal->getCriteria()->getTueMarginDuration());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['wedMinPickupTime'], $matching['request']->getFilters()['pickup']['wedMaxPickupTime'])) {
                            $item->setWedCheck(true);
                            $item->setWedTime($proposal->getCriteria()->getWedTime());
                            $item->setTime($proposal->getCriteria()->getWedTime());
                            $item->setWedMarginDuration($proposal->getCriteria()->getWedMarginDuration());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['thuMinPickupTime'], $matching['request']->getFilters()['pickup']['thuMaxPickupTime'])) {
                            $item->setThuCheck(true);
                            $item->setThuTime($proposal->getCriteria()->getThuTime());
                            $item->setTime($proposal->getCriteria()->getThuTime());
                            $item->setThuMarginDuration($proposal->getCriteria()->getThuMarginDuration());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['friMinPickupTime'], $matching['request']->getFilters()['pickup']['friMaxPickupTime'])) {
                            $item->setFriCheck(true);
                            $item->setFriTime($proposal->getCriteria()->getFriTime());
                            $item->setTime($proposal->getCriteria()->getFriTime());
                            $item->setFriMarginDuration($proposal->getCriteria()->getFriMarginDuration());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['satMinPickupTime'], $matching['request']->getFilters()['pickup']['satMaxPickupTime'])) {
                            $item->setSatCheck(true);
                            $item->setSatTime($proposal->getCriteria()->getSatTime());
                            $item->setTime($proposal->getCriteria()->getSatTime());
                            $item->setSatMarginDuration($proposal->getCriteria()->getSatMarginDuration());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['sunMinPickupTime'], $matching['request']->getFilters()['pickup']['sunMaxPickupTime'])) {
                            $item->setSunCheck(true);
                            $item->setSunTime($proposal->getCriteria()->getSunTime());
                            $item->setTime($proposal->getCriteria()->getSunTime());
                            $item->setSunMarginDuration($proposal->getCriteria()->getSunMarginDuration());
                        }
                    } else {
                        // no pick up times, it must be the matching results of a search (and not an ad)
                        // the days are the carpooler days
                        $item->setMonCheck($matching['request']->getProposalRequest()->getCriteria()->isMonCheck());
                        $item->setTueCheck($matching['request']->getProposalRequest()->getCriteria()->isTueCheck());
                        $item->setWedCheck($matching['request']->getProposalRequest()->getCriteria()->isWedCheck());
                        $item->setThuCheck($matching['request']->getProposalRequest()->getCriteria()->isThuCheck());
                        $item->setFriCheck($matching['request']->getProposalRequest()->getCriteria()->isFriCheck());
                        $item->setSatCheck($matching['request']->getProposalRequest()->getCriteria()->isSatCheck());
                        $item->setSunCheck($matching['request']->getProposalRequest()->getCriteria()->isSunCheck());
                        // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // even if we don't use them, maybe we'll need them in the future
                        $pickupDuration = null;
                        if (!is_null($matching['request']->getPickUpDuration())) {
                            $pickupDuration = $matching['request']->getPickUpDuration();
                        } else {
                            $filters = $matching['request']->getFilters();
                            foreach ($filters['route'] as $value) {
                                if (2 == $value['candidate'] && 0 == $value['position']) {
                                    $pickupDuration = (int) round($value['duration']);

                                    break;
                                }
                            }
                        }
                        // we init the time to the one of the carpooler
                        if ($matching['request']->getProposalRequest()->getCriteria()->isMonCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getMonTime())) {
                            $monTime = clone $matching['request']->getProposalRequest()->getCriteria()->getMonTime();
                            if ($pickupDuration) {
                                $monTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setMonTime($monTime);
                            $item->setTime($monTime);
                            $item->setMonMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getMonMarginDuration());
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isTueCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getTueTime())) {
                            $tueTime = $matching['request']->getProposalRequest()->getCriteria()->getTueTime();
                            if ($pickupDuration) {
                                $tueTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setTueTime($tueTime);
                            $item->setTime($tueTime);
                            $item->setTueMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getTueMarginDuration());
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isWedCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getWedTime())) {
                            $wedTime = $matching['request']->getProposalRequest()->getCriteria()->getWedTime();
                            if ($pickupDuration) {
                                $wedTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setWedTime($wedTime);
                            $item->setTime($wedTime);
                            $item->setWedMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getWedMarginDuration());
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isThuCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getThuTime())) {
                            $thuTime = $matching['request']->getProposalRequest()->getCriteria()->getThuTime();
                            if ($pickupDuration) {
                                $thuTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setThuTime($thuTime);
                            $item->setTime($thuTime);
                            $item->setThuMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getThuMarginDuration());
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isFriCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getFriTime())) {
                            $friTime = $matching['request']->getProposalRequest()->getCriteria()->getFriTime();
                            if ($pickupDuration) {
                                $friTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setFriTime($friTime);
                            $item->setTime($friTime);
                            $item->setFriMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getFriMarginDuration());
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isSatCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getSatTime())) {
                            $satTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSatTime();
                            if ($pickupDuration) {
                                $satTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setSatTime($satTime);
                            $item->setTime($satTime);
                            $item->setSatMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getSatMarginDuration());
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isSunCheck()
                            && !is_null($matching['request']->getProposalRequest()->getCriteria()->getSunTime())) {
                            $sunTime = $matching['request']->getProposalRequest()->getCriteria()->getSunTime();
                            if ($pickupDuration) {
                                $sunTime->sub(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setSunTime($sunTime);
                            $item->setTime($sunTime);
                            $item->setSunMarginDuration($matching['request']->getProposalRequest()->getCriteria()->getSunMarginDuration());
                        }
                    }
                    $item->setMultipleTimes();
                    if ($item->hasMultipleTimes()) {
                        $item->setTime(null);
                    }
                    // fromDate is the max between the search date and the fromDate of the matching proposal
                    $item->setFromDate(max(
                        $matching['request']->getProposalRequest()->getCriteria()->getFromDate(),
                        $proposal->getCriteria()->getFromDate()
                    ));
                    $item->setToDate($matching['request']->getProposalRequest()->getCriteria()->getToDate());
                }
            }
            // waypoints of the item
            $waypoints = [];
            $time = $item->getTime() ? clone $item->getTime() : null;

            // we will have to compute the number of steps for each candidate
            $origins = [
                'requester' => 9999,
                'carpooler' => 9999,
            ];
            $destinations = [
                'requester' => 0,
                'carpooler' => 0,
            ];
            // first pass to get the minimum and maximum position fo each candidate
            foreach ($matching['request']->getWaypoints() as $waypoint) {
                if (1 == $waypoint->getRole() && $waypoint->getPosition() > $destinations['requester']) {
                    $destinations['requester'] = $waypoint->getPosition();
                }
                if (1 == $waypoint->getRole() && $waypoint->getPosition() < $origins['requester']) {
                    $origins['requester'] = $waypoint->getPosition();
                }
                if (2 == $waypoint->getRole() && $waypoint->getPosition() > $destinations['carpooler']) {
                    $destinations['carpooler'] = $waypoint->getPosition();
                }
                if (2 == $waypoint->getRole() && $waypoint->getPosition() < $origins['carpooler']) {
                    $origins['carpooler'] = $waypoint->getPosition();
                }
            }

            $i = 0;
            foreach ($matching['request']->getWaypoints() as $waypoint) {
                $curTime = null;
                if ($time) {
                    $curTime = clone $time;
                }
                if ($curTime) {
                    $curTime->add(new \DateInterval('PT'.$waypoint->getDuration().'S'));
                }
                $type = 'step';
                // origin and destination guess
                if (2 == $waypoint->getRole() && $waypoint->getPosition() == $origins['carpooler']) {
                    $type = 'origin';
                    $item->setOrigin($waypoint->getAddress());
                    $item->setOriginPassenger($waypoint->getAddress());
                } elseif (2 == $waypoint->getRole() && $waypoint->getPosition() == $destinations['carpooler']) {
                    $type = 'destination';
                    $item->setDestination($waypoint->getAddress());
                    $item->setDestinationPassenger($waypoint->getAddress());
                } elseif (1 == $waypoint->getRole() && $waypoint->getPosition() == $origins['requester']) {
                    $type = 'origin';
                    $item->setOriginDriver($waypoint->getAddress());
                } elseif (1 == $waypoint->getRole() && $waypoint->getPosition() == $destinations['requester']) {
                    $type = 'destination';
                    $item->setDestinationDriver($waypoint->getAddress());
                }
                $waypoints[$i] = [
                    'id' => $i,
                    'person' => 1 == $waypoint->getRole() ? 'requester' : 'carpooler',
                    'role' => 1 == $waypoint->getRole() ? 'driver' : 'passenger',
                    'time' => $curTime,
                    'address' => $waypoint->getAddress(),
                    'type' => $type,
                ];
                ++$i;
            }
            $item->setWaypoints($waypoints);

            // statistics
            if (!is_null($matching['request']->getPickUpDuration())) {
                $item->setOriginalDistance($matching['request']->getOriginalDistance());
                $item->setAcceptedDetourDistance($matching['request']->getAcceptedDetourDistance());
                $item->setNewDistance($matching['request']->getNewDistance());
                $item->setDetourDistance($matching['request']->getDetourDistance());
                $result->setDetourDistance($matching['request']->getDetourDistance());
                $item->setDetourDistancePercent($matching['request']->getDetourDistancePercent());
                $item->setOriginalDuration($matching['request']->getOriginalDuration());
                $item->setAcceptedDetourDuration($matching['request']->getAcceptedDetourDuration());
                $item->setNewDuration($matching['request']->getNewDuration());
                $item->setDetourDuration($matching['request']->getDetourDuration());
                $result->setDetourDuration($matching['request']->getDetourDuration());
                $item->setDetourDurationPercent($matching['request']->getDetourDurationPercent());
                $item->setCommonDistance($matching['request']->getCommonDistance());
            } else {
                $item->setOriginalDistance($matching['request']->getFilters()['originalDistance']);
                $item->setAcceptedDetourDistance($matching['request']->getFilters()['acceptedDetourDistance']);
                $item->setNewDistance($matching['request']->getFilters()['newDistance']);
                $item->setDetourDistance($matching['request']->getFilters()['detourDistance']);
                $result->setDetourDistance($matching['request']->getFilters()['detourDistance']);
                $item->setDetourDistancePercent($matching['request']->getFilters()['detourDistancePercent']);
                $item->setOriginalDuration($matching['request']->getFilters()['originalDuration']);
                $item->setAcceptedDetourDuration($matching['request']->getFilters()['acceptedDetourDuration']);
                $item->setNewDuration($matching['request']->getFilters()['newDuration']);
                $item->setDetourDuration($matching['request']->getFilters()['detourDuration']);
                $result->setDetourDuration($matching['request']->getFilters()['detourDuration']);
                $item->setDetourDurationPercent($matching['request']->getFilters()['detourDurationPercent']);
                $item->setCommonDistance($matching['request']->getFilters()['commonDistance']);
            }

            // Check if the detour is "noticeable"
            $result->setNoticeableDetour(false);

            $driverOriginalDistance = $proposal->getCriteria()->getDirectionDriver()->getDistance();
            $driverOriginalDuration = $proposal->getCriteria()->getDirectionDriver()->getDuration();

            $minDetourDistanceToBeNoticeable = (0 !== $this->carpoolNoticeableDetourDistancePercent) ? $driverOriginalDistance * $this->carpoolNoticeableDetourDistancePercent / 100 : $driverOriginalDistance;
            $minDetourDurationToBeNoticeable = (0 !== $this->carpoolNoticeableDetourDurationPercent) ? $driverOriginalDuration * $this->carpoolNoticeableDetourDurationPercent / 100 : $driverOriginalDuration;

            if ($result->getDetourDistance() >= $minDetourDistanceToBeNoticeable || $result->getDetourDuration() >= $minDetourDurationToBeNoticeable) {
                $result->setNoticeableDetour(true);
            }

            // prices

            // we set the prices of the driver (the requester)
            // if the requester price per km is set we use it
            if ($proposal->getCriteria()->getPriceKm()) {
                $item->setDriverPriceKm($proposal->getCriteria()->getPriceKm());
            } else {
                // otherwise we use the common price
                $item->setDriverPriceKm($this->params['defaultPriceKm']);
            }
            // if the requester price is set we use it
            if ($proposal->getCriteria()->getDriverPrice()) {
                $item->setDriverOriginalPrice($proposal->getCriteria()->getDriverPrice());
            } else {
                // otherwise we use the common price, rounded
                if (!is_null($matching['request']->getOriginalDistance())) {
                    $item->setDriverOriginalPrice((string) $this->formatDataManager->roundPrice($matching['request']->getOriginalDistance() * (float) $item->getDriverPriceKm() / 1000, $proposal->getCriteria()->getFrequency()));
                } else {
                    $item->setDriverOriginalPrice((string) $this->formatDataManager->roundPrice((int) $matching['request']->getFilters()['originalDistance'] * (float) $item->getDriverPriceKm() / 1000, $proposal->getCriteria()->getFrequency()));
                }
            }

            // we set the prices of the passenger (the carpooler)
            $item->setPassengerPriceKm($matching['request']->getProposalRequest()->getCriteria()->getPriceKm());
            $item->setPassengerOriginalPrice($matching['request']->getProposalRequest()->getCriteria()->getPassengerPrice());

            // the computed price is the price to be paid by the passenger
            // it's ((common distance + detour distance) * driver price by km)
            if (!is_null($matching['request']->getCommonDistance())) {
                $item->setComputedPrice((string) (((int) $matching['request']->getCommonDistance() + (int) $matching['request']->getDetourDistance()) * (float) $item->getDriverPriceKm() / 1000));
            } else {
                $item->setComputedPrice((string) (((int) $matching['request']->getFilters()['commonDistance'] + (int) $matching['request']->getFilters()['detourDistance']) * (float) $item->getDriverPriceKm() / 1000));
            }
            $item->setComputedRoundedPrice((string) $this->formatDataManager->roundPrice((float) $item->getComputedPrice(), $matching['request']->getCriteria()->getFrequency()));

            // check if an ask exists
            $item->setPendingAsk(false);
            $item->setAcceptedAsk(false);
            $item->setInitiatedAsk(false);
            if (count($matching['request']->getAsks())) {
                foreach ($matching['request']->getAsks() as $ask) {
                    switch ($ask->getStatus()) {
                        case Ask::STATUS_INITIATED:
                            $item->setInitiatedAsk(true);

                            break;

                        case Ask::STATUS_PENDING_AS_DRIVER:
                        case Ask::STATUS_PENDING_AS_PASSENGER:
                            $item->setPendingAsk(true);

                            break;

                        case Ask::STATUS_ACCEPTED_AS_DRIVER:
                        case Ask::STATUS_ACCEPTED_AS_PASSENGER:
                            $item->setAcceptedAsk(true);

                            break;
                    }
                }
            } else {
                // search for existing matchings with same proposalId as passenger
                // first we check if a user is associated to the proposal (if a user is logged)
                if ($matching['request']->getProposalOffer()->getUser()) {
                    if ($asks = $this->askRepository->findAskForAd(
                        $matching['request']->getProposalRequest(),
                        $matching['request']->getProposalOffer()->getUser(),
                        [
                            Ask::STATUS_INITIATED,
                            Ask::STATUS_PENDING_AS_DRIVER,
                            Ask::STATUS_PENDING_AS_PASSENGER,
                            Ask::STATUS_ACCEPTED_AS_DRIVER,
                            Ask::STATUS_ACCEPTED_AS_PASSENGER,
                        ]
                    )) {
                        foreach ($asks as $ask) {
                            switch ($ask->getStatus()) {
                                    case Ask::STATUS_INITIATED:
                                        $item->setInitiatedAsk(true);

                                        break;

                                    case Ask::STATUS_PENDING_AS_DRIVER:
                                    case Ask::STATUS_PENDING_AS_PASSENGER:
                                        $item->setPendingAsk(true);

                                        break;

                                    case Ask::STATUS_ACCEPTED_AS_DRIVER:
                                    case Ask::STATUS_ACCEPTED_AS_PASSENGER:
                                        $item->setAcceptedAsk(true);

                                        break;
                                }
                        }
                    }
                }
            }

            if (!$return) {
                $resultDriver->setOutward($item);
            } else {
                $resultDriver->setReturn($item);
            }

            // seats
            $resultDriver->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger() ? $proposal->getCriteria()->getSeatsPassenger() : 1);
            $result->setResultDriver($resultDriver);
            $passenger = true;
        }

        // OFFER

        if (isset($matching['offer'])) {
            // the carpooler can be driver
            if (is_null($result->getFrequency())) {
                $result->setFrequency($matching['offer']->getCriteria()->getFrequency());
            }
            if (is_null($result->getFrequencyResult())) {
                $result->setFrequencyResult($matching['offer']->getProposalOffer()->getCriteria()->getFrequency());
            }
            if (is_null($result->getCarpooler())) {
                $carpooler = $matching['offer']->getProposalOffer()->getUser();
                // Clone doesn't treat avatars as it's a loadListener
                $resultCarpooler = clone $carpooler;
                $resultCarpooler->setAvatars($carpooler->getAvatars());
                $resultCarpooler->setExperienced($carpooler->isExperienced());
                $result->setCarpooler($resultCarpooler);
                // We check if we have accepted carpool
                $hasAsk = false;
                $asks = $matching['offer']->getAsks();
                foreach ($asks as $ask) {
                    if ($ask->getStatus() == (ASK::STATUS_ACCEPTED_AS_DRIVER || ASK::STATUS_ACCEPTED_AS_PASSENGER)) {
                        $hasAsk = true;

                        break;
                    }
                }
                // if we don't have accepted carpools AND (no user is logged OR the phone display is restricted) we pass the telephone at null
                if (!$hasAsk && (!$matching['offer']->getProposalRequest()->getUser() || USER::PHONE_DISPLAY_RESTRICTED == $carpooler->getPhoneDisplay())) {
                    $result->getCarpooler()->setTelephone(null);
                }
            }
            if (is_null($result->getComment()) && !is_null($matching['offer']->getProposalOffer()->getComment())) {
                $result->setComment($matching['offer']->getProposalOffer()->getComment());
            }
            if (is_null($result->getAskId()) && !empty($matching['offer']->getAsks())) {
                $result->setAskId($matching['offer']->getAsks()[0]->getId());
            }

            // solidary : the offer can be solidaryExclusive
            $result->setSolidary(false);
            $result->setSolidaryExclusive($matching['offer']->getProposalOffer()->getCriteria()->isSolidaryExclusive());

            // communities
            foreach ($matching['offer']->getProposalOffer()->getCommunities() as $community) {
                $communities[$community->getId()] = [
                    'name' => $community->getName(),
                    'image' => $community->getImages(),
                ];
            }
            // outward
            $item = new ResultItem();
            // we set the proposalId
            $item->setProposalId($matchingProposal->getId());
            if (Matching::DEFAULT_ID !== $matching['offer']->getId()) {
                $item->setMatchingId($matching['offer']->getId());
            }
            $driverFromTime = null;
            if (Criteria::FREQUENCY_PUNCTUAL == $proposal->getCriteria()->getFrequency()) {
                // the search/ad proposal is punctual
                // we have to calculate the date and time of the carpool
                // date :
                // - if the matching proposal is also punctual, it's the date of the matching proposal (as the date of the matching proposal could be the same or after the date of the search/ad)
                // - if the matching proposal is regular we search for the first valid carpool day
                if (Criteria::FREQUENCY_PUNCTUAL == $matching['offer']->getProposalOffer()->getCriteria()->getFrequency()) {
                    $item->setDate($matching['offer']->getProposalOffer()->getCriteria()->getFromDate());
                } else {
                    $regularDayInfos = $this->getMatchingRegularDayAsRequest($matching['offer'], $item);
                    $item = $regularDayInfos['item'];
                    $fromTime = $regularDayInfos['time'];
                }

                // time
                // the carpooler is driver, the proposal owner is passenger
                // we have to calculate the starting time using the carpooler time
                // we init the time to the one of the carpooler
                if (Criteria::FREQUENCY_PUNCTUAL == $matching['offer']->getProposalOffer()->getCriteria()->getFrequency()) {
                    // if the proposal is dynamic, we take the updated time of the position linked with the proposal
                    if ($matching['offer']->getProposalOffer()->isDynamic()) {
                        $fromTime = $matching['offer']->getProposalOffer()->getPosition()->getUpdatedDate();
                    } else {
                        // the carpooler proposal is punctual, we take the fromTime
                        $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFromTime();
                    }
                    $item->setMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getMarginDuration());
                }

                // we search the pickup duration
                $pickupDuration = null;
                if (!is_null($matching['offer']->getPickUpDuration())) {
                    $pickupDuration = $matching['offer']->getPickUpDuration();
                } else {
                    $filters = $matching['offer']->getFilters();
                    foreach ($filters['route'] as $value) {
                        if (2 == $value['candidate'] && 0 == $value['position']) {
                            $pickupDuration = (int) round($value['duration']);

                            break;
                        }
                    }
                }
                $driverFromTime = clone $fromTime;
                if ($pickupDuration) {
                    $fromTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                }
                $item->setTime($fromTime);
            } else {
                if (Criteria::FREQUENCY_PUNCTUAL == $matching['offer']->getCriteria()->getFrequency()) {
                    // Set the date to the carpooler's date
                    $item->setDate($matching['offer']->getProposalOffer()->getCriteria()->getFromDate());

                    // the carpooler proposal is punctual, we have to take the proposal time matching to the carpooler day
                    $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFromTime();

                    // we search the pickup duration
                    $pickupDuration = null;
                    if (!is_null($matching['offer']->getPickUpDuration())) {
                        $pickupDuration = $matching['offer']->getPickUpDuration();
                    } else {
                        $filters = $matching['offer']->getFilters();
                        foreach ($filters['route'] as $value) {
                            if (2 == $value['candidate'] && 0 == $value['position']) {
                                $pickupDuration = (int) round($value['duration']);

                                break;
                            }
                        }
                    }
                    if ($pickupDuration) {
                        $fromTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                    }
                    $item->setTime($fromTime);
                } else {
                    // the search or ad is regular => no date
                    // we have to find common days (if it's a search the common days should be the carpooler days)
                    // we check if pickup times have been calculated already
                    // we set the global time for each day, we will erase it if we discover that all days have not the same time
                    // this way we are sure that if all days have the same time, the global time will be set and ok
                    if (isset($matching['offer']->getFilters()['pickup'])) {
                        // we have pickup times, it must be the matching results of an ad (and not a search)
                        // the carpooler is driver, the proposal owner is passenger : we use his time as it must be set
                        if (isset($matching['offer']->getFilters()['pickup']['monMinPickupTime'], $matching['offer']->getFilters()['pickup']['monMaxPickupTime'])) {
                            $item->setMonCheck(true);
                            $item->setMonTime($proposal->getCriteria()->getMonTime());
                            $item->setTime($proposal->getCriteria()->getMonTime());
                            $item->setMonMarginDuration($proposal->getCriteria()->getMonMarginDuration());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['tueMinPickupTime'], $matching['offer']->getFilters()['pickup']['tueMaxPickupTime'])) {
                            $item->setTueCheck(true);
                            $item->setTueTime($proposal->getCriteria()->getTueTime());
                            $item->setTime($proposal->getCriteria()->getTueTime());
                            $item->setTueMarginDuration($proposal->getCriteria()->getTueMarginDuration());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['wedMinPickupTime'], $matching['offer']->getFilters()['pickup']['wedMaxPickupTime'])) {
                            $item->setWedCheck(true);
                            $item->setWedTime($proposal->getCriteria()->getWedTime());
                            $item->setTime($proposal->getCriteria()->getWedTime());
                            $item->setWedMarginDuration($proposal->getCriteria()->getWedMarginDuration());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['thuMinPickupTime'], $matching['offer']->getFilters()['pickup']['thuMaxPickupTime'])) {
                            $item->setThuCheck(true);
                            $item->setThuTime($proposal->getCriteria()->getThuTime());
                            $item->setTime($proposal->getCriteria()->getThuTime());
                            $item->setThuMarginDuration($proposal->getCriteria()->getThuMarginDuration());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['friMinPickupTime'], $matching['offer']->getFilters()['pickup']['friMaxPickupTime'])) {
                            $item->setFriCheck(true);
                            $item->setFriTime($proposal->getCriteria()->getFriTime());
                            $item->setTime($proposal->getCriteria()->getFriTime());
                            $item->setFriMarginDuration($proposal->getCriteria()->getFriMarginDuration());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['satMinPickupTime'], $matching['offer']->getFilters()['pickup']['satMaxPickupTime'])) {
                            $item->setSatCheck(true);
                            $item->setSatTime($proposal->getCriteria()->getSatTime());
                            $item->setTime($proposal->getCriteria()->getSatTime());
                            $item->setSatMarginDuration($proposal->getCriteria()->getSatMarginDuration());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['sunMinPickupTime'], $matching['offer']->getFilters()['pickup']['sunMaxPickupTime'])) {
                            $item->setSunCheck(true);
                            $item->setSunTime($proposal->getCriteria()->getSunTime());
                            $item->setTime($proposal->getCriteria()->getSunTime());
                            $item->setSunMarginDuration($proposal->getCriteria()->getSunMarginDuration());
                        }
                        $driverFromTime = $item->getTime();
                    } else {
                        // no pick up times, it must be the matching results of a search (and not an ad)
                        // the days are the carpooler days
                        $item->setMonCheck($matching['offer']->getProposalOffer()->getCriteria()->isMonCheck());
                        $item->setTueCheck($matching['offer']->getProposalOffer()->getCriteria()->isTueCheck());
                        $item->setWedCheck($matching['offer']->getProposalOffer()->getCriteria()->isWedCheck());
                        $item->setThuCheck($matching['offer']->getProposalOffer()->getCriteria()->isThuCheck());
                        $item->setFriCheck($matching['offer']->getProposalOffer()->getCriteria()->isFriCheck());
                        $item->setSatCheck($matching['offer']->getProposalOffer()->getCriteria()->isSatCheck());
                        $item->setSunCheck($matching['offer']->getProposalOffer()->getCriteria()->isSunCheck());
                        // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // even if we don't use them, maybe we'll need them in the future
                        $pickupDuration = null;
                        if (!is_null($matching['offer']->getPickUpDuration())) {
                            $pickupDuration = $matching['offer']->getPickUpDuration();
                        } else {
                            $filters = $matching['offer']->getFilters();
                            foreach ($filters['route'] as $value) {
                                if (2 == $value['candidate'] && 0 == $value['position']) {
                                    $pickupDuration = (int) round($value['duration']);

                                    break;
                                }
                            }
                        }
                        // we init the time to the one of the carpooler
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isMonCheck()) {
                            $monTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getMonTime();
                            $driverFromTime = clone $monTime;
                            if ($pickupDuration) {
                                $monTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setMonTime($monTime);
                            $item->setTime($monTime);
                            $item->setMonMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getMonMarginDuration());
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isTueCheck()) {
                            $tueTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getTueTime();
                            $driverFromTime = clone $tueTime;
                            if ($pickupDuration) {
                                $tueTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setTueTime($tueTime);
                            $item->setTime($tueTime);
                            $item->setTueMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getTueMarginDuration());
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isWedCheck()) {
                            $wedTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getWedTime();
                            $driverFromTime = clone $wedTime;
                            if ($pickupDuration) {
                                $wedTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setWedTime($wedTime);
                            $item->setTime($wedTime);
                            $item->setWedMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getWedMarginDuration());
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isThuCheck()) {
                            $thuTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getThuTime();
                            $driverFromTime = clone $thuTime;
                            if ($pickupDuration) {
                                $thuTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setThuTime($thuTime);
                            $item->setTime($thuTime);
                            $item->setThuMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getThuMarginDuration());
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isFriCheck()) {
                            $friTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFriTime();
                            $driverFromTime = clone $friTime;
                            if ($pickupDuration) {
                                $friTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setFriTime($friTime);
                            $item->setTime($friTime);
                            $item->setFriMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getFriMarginDuration());
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isSatCheck()) {
                            $satTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSatTime();
                            $driverFromTime = clone $satTime;
                            if ($pickupDuration) {
                                $satTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setSatTime($satTime);
                            $item->setTime($satTime);
                            $item->setSatMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getSatMarginDuration());
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isSunCheck()) {
                            $sunTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSunTime();
                            $driverFromTime = clone $sunTime;
                            if ($pickupDuration) {
                                $sunTime->add(new \DateInterval('PT'.$pickupDuration.'S'));
                            }
                            $item->setSunTime($sunTime);
                            $item->setTime($sunTime);
                            $item->setSunMarginDuration($matching['offer']->getProposalOffer()->getCriteria()->getSunMarginDuration());
                        }
                    }
                    $item->setMultipleTimes();
                    if ($item->hasMultipleTimes()) {
                        $item->setTime(null);
                        $driverFromTime = null;
                    }
                    // fromDate is the max between the search date and the fromDate of the matching proposal
                    $item->setFromDate(max(
                        $matching['offer']->getProposalOffer()->getCriteria()->getFromDate(),
                        $proposal->getCriteria()->getFromDate()
                    ));
                    $item->setToDate($matching['offer']->getProposalOffer()->getCriteria()->getToDate());
                }
            }
            // waypoints of the item
            $waypoints = [];
            $time = $driverFromTime ? clone $driverFromTime : null;

            // // we will have to compute the number of steps for each candidate
            // $steps = [
            //     'requester' => 0,
            //     'carpooler' => 0
            // ];
            // // first pass to get the maximum position for each candidate
            // foreach ($matching['offer']->getFilters()['route'] as $key=>$waypoint) {
            //     if ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['requester']) {
            //         $steps['requester'] = (int)$waypoint['position'];
            //     } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['carpooler']) {
            //         $steps['carpooler'] = (int)$waypoint['position'];
            //     }
            // }
            // // second pass to fill the waypoints array
            // foreach ($matching['offer']->getFilters()['route'] as $key=>$waypoint) {
            //     $curTime = null;
            //     if ($time) {
            //         $curTime = clone $time;
            //     }
            //     if ($curTime) {
            //         $curTime->add(new \DateInterval('PT' . (int)round($waypoint['duration']) . 'S'));
            //     }
            //     $waypoints[$key] = [
            //         'id' => $key,
            //         'person' => $waypoint['candidate'] == 2 ? 'requester' : 'carpooler',
            //         'role' => $waypoint['candidate'] == 1 ? 'driver' : 'passenger',
            //         'time' =>  $curTime,
            //         'address' => $waypoint['address'],
            //         'type' => $waypoint['position'] == '0' ? 'origin' :
            //             (
            //                 ($waypoint['candidate'] == 2) ? ((int)$waypoint['position'] == $steps['requester'] ? 'destination' : 'step') :
            //                 ((int)$waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step')
            //             )
            //     ];
            //     // origin and destination guess
            //     if ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
            //         $item->setOrigin($waypoint['address']);
            //         $item->setOriginDriver($waypoint['address']);
            //     } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['carpooler']) {
            //         $item->setDestination($waypoint['address']);
            //         $item->setDestinationDriver($waypoint['address']);
            //     } elseif ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
            //         $item->setOriginPassenger($waypoint['address']);
            //     } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['requester']) {
            //         $item->setDestinationPassenger($waypoint['address']);
            //     }
            // }

            // we will have to compute the number of steps for each candidate
            $origins = [
                'requester' => 9999,
                'carpooler' => 9999,
            ];
            $destinations = [
                'requester' => 0,
                'carpooler' => 0,
            ];
            // first pass to get the minimum and maximum position fo each candidate
            foreach ($matching['offer']->getWaypoints() as $waypoint) {
                if (2 == $waypoint->getRole() && $waypoint->getPosition() > $destinations['requester']) {
                    $destinations['requester'] = $waypoint->getPosition();
                }
                if (2 == $waypoint->getRole() && $waypoint->getPosition() < $origins['requester']) {
                    $origins['requester'] = $waypoint->getPosition();
                }
                if (1 == $waypoint->getRole() && $waypoint->getPosition() > $destinations['carpooler']) {
                    $destinations['carpooler'] = $waypoint->getPosition();
                }
                if (1 == $waypoint->getRole() && $waypoint->getPosition() < $origins['carpooler']) {
                    $origins['carpooler'] = $waypoint->getPosition();
                }
            }

            $i = 0;
            foreach ($matching['offer']->getWaypoints() as $waypoint) {
                $curTime = null;
                if ($time) {
                    $curTime = clone $time;
                }
                if ($curTime) {
                    $curTime->add(new \DateInterval('PT'.$waypoint->getDuration().'S'));
                }
                $type = 'step';
                // origin and destination guess
                if (1 == $waypoint->getRole() && $waypoint->getPosition() == $origins['carpooler']) {
                    $type = 'origin';
                    $item->setOrigin($waypoint->getAddress());
                    $item->setOriginDriver($waypoint->getAddress());
                } elseif (1 == $waypoint->getRole() && $waypoint->getPosition() == $destinations['carpooler']) {
                    $type = 'destination';
                    $item->setDestination($waypoint->getAddress());
                    $item->setDestinationDriver($waypoint->getAddress());
                } elseif (2 == $waypoint->getRole() && $waypoint->getPosition() == $origins['requester']) {
                    $type = 'origin';
                    $item->setOriginPassenger($waypoint->getAddress());
                } elseif (2 == $waypoint->getRole() && $waypoint->getPosition() == $destinations['requester']) {
                    $type = 'destination';
                    $item->setDestinationPassenger($waypoint->getAddress());
                }
                $waypoints[$i] = [
                    'id' => $i,
                    'person' => 2 == $waypoint->getRole() ? 'requester' : 'carpooler',
                    'role' => 1 == $waypoint->getRole() ? 'driver' : 'passenger',
                    'time' => $curTime,
                    'address' => $waypoint->getAddress(),
                    'type' => $type,
                ];
                ++$i;
            }

            $item->setWaypoints($waypoints);

            // statistics
            if (!is_null($matching['offer']->getPickUpDuration())) {
                $item->setOriginalDistance($matching['offer']->getOriginalDistance());
                $item->setAcceptedDetourDistance($matching['offer']->getAcceptedDetourDistance());
                $item->setNewDistance($matching['offer']->getNewDistance());
                $item->setDetourDistance($matching['offer']->getDetourDistance());
                $result->setDetourDistance($matching['offer']->getDetourDistance());
                $item->setDetourDistancePercent($matching['offer']->getDetourDistancePercent());
                $item->setOriginalDuration($matching['offer']->getOriginalDuration());
                $item->setAcceptedDetourDuration($matching['offer']->getAcceptedDetourDuration());
                $item->setNewDuration($matching['offer']->getNewDuration());
                $item->setDetourDuration($matching['offer']->getDetourDuration());
                $result->setDetourDuration($matching['offer']->getDetourDuration());
                $item->setDetourDurationPercent($matching['offer']->getDetourDurationPercent());
                $item->setCommonDistance($matching['offer']->getCommonDistance());
            } else {
                $item->setOriginalDistance($matching['offer']->getFilters()['originalDistance']);
                $item->setAcceptedDetourDistance($matching['offer']->getFilters()['acceptedDetourDistance']);
                $item->setNewDistance($matching['offer']->getFilters()['newDistance']);
                $item->setDetourDistance($matching['offer']->getFilters()['detourDistance']);
                $result->setDetourDistance($matching['offer']->getFilters()['detourDistance']);
                $item->setDetourDistancePercent($matching['offer']->getFilters()['detourDistancePercent']);
                $item->setOriginalDuration($matching['offer']->getFilters()['originalDuration']);
                $item->setAcceptedDetourDuration($matching['offer']->getFilters()['acceptedDetourDuration']);
                $item->setNewDuration($matching['offer']->getFilters()['newDuration']);
                $item->setDetourDuration($matching['offer']->getFilters()['detourDuration']);
                $result->setDetourDuration($matching['offer']->getFilters()['detourDuration']);
                $item->setDetourDurationPercent($matching['offer']->getFilters()['detourDurationPercent']);
                $item->setCommonDistance($matching['offer']->getFilters()['commonDistance']);
            }

            // Check if the detour is "noticeable"
            $result->setNoticeableDetour(false);

            $driverOriginalDistance = $matching['offer']->getProposalOffer()->getCriteria()->getDirectionDriver()->getDistance();
            $driverOriginalDuration = $matching['offer']->getProposalOffer()->getCriteria()->getDirectionDriver()->getDuration();

            $minDetourDistanceToBeNoticeable = (0 !== $this->carpoolNoticeableDetourDistancePercent) ? $driverOriginalDistance * $this->carpoolNoticeableDetourDistancePercent / 100 : $driverOriginalDistance;
            $minDetourDurationToBeNoticeable = (0 !== $this->carpoolNoticeableDetourDurationPercent) ? $driverOriginalDuration * $this->carpoolNoticeableDetourDurationPercent / 100 : $driverOriginalDuration;

            if ($result->getDetourDistance() >= $minDetourDistanceToBeNoticeable || $result->getDetourDuration() >= $minDetourDurationToBeNoticeable) {
                $result->setNoticeableDetour(true);
            }

            // prices

            // we set the prices of the driver (the carpooler)
            $item->setDriverPriceKm($matching['offer']->getProposalOffer()->getCriteria()->getPriceKm());
            $item->setDriverOriginalPrice($matching['offer']->getProposalOffer()->getCriteria()->getDriverPrice());

            // we set the prices of the passenger (the requester)
            if ($proposal->getCriteria()->getPriceKm()) {
                $item->setPassengerPriceKm($proposal->getCriteria()->getPriceKm());
            } else {
                // otherwise we use the common price
                $item->setPassengerPriceKm($this->params['defaultPriceKm']);
            }
            // if the requester price is set we use it
            if ($proposal->getCriteria()->getPassengerPrice()) {
                $item->setPassengerOriginalPrice($proposal->getCriteria()->getPassengerPrice());
            } else {
                // otherwise we use the common price
                if (!is_null($matching['offer']->getCommonDistance())) {
                    $item->setPassengerOriginalPrice((string) $this->formatDataManager->roundPrice($matching['offer']->getCommonDistance() * (float) $item->getPassengerPriceKm() / 1000, $proposal->getCriteria()->getFrequency()));
                } else {
                    $item->setPassengerOriginalPrice((string) $this->formatDataManager->roundPrice((int) $matching['offer']->getFilters()['commonDistance'] * (float) $item->getPassengerPriceKm() / 1000, $proposal->getCriteria()->getFrequency()));
                }
            }

            // the computed price is the price to be paid by the passenger
            // it's ((common distance + detour distance) * driver price by km)
            if (!is_null($matching['offer']->getCommonDistance())) {
                $item->setComputedPrice((string) (($matching['offer']->getCommonDistance() + $matching['offer']->getDetourDistance()) * (float) $item->getDriverPriceKm() / 1000));
            } else {
                $item->setComputedPrice((string) (((int) $matching['offer']->getFilters()['commonDistance'] + (int) $matching['offer']->getFilters()['detourDistance']) * (float) $item->getDriverPriceKm() / 1000));
            }
            $item->setComputedRoundedPrice((string) $this->formatDataManager->roundPrice((float) $item->getComputedPrice(), $matching['offer']->getCriteria()->getFrequency()));

            // check if an ask exists
            $item->setPendingAsk(false);
            $item->setAcceptedAsk(false);
            $item->setInitiatedAsk(false);
            if (count($matching['offer']->getAsks())) {
                foreach ($matching['offer']->getAsks() as $ask) {
                    switch ($ask->getStatus()) {
                        case Ask::STATUS_INITIATED:
                            $item->setInitiatedAsk(true);

                            break;

                        case Ask::STATUS_PENDING_AS_DRIVER:
                        case Ask::STATUS_PENDING_AS_PASSENGER:
                            $item->setPendingAsk(true);

                            break;

                        case Ask::STATUS_ACCEPTED_AS_DRIVER:
                        case Ask::STATUS_ACCEPTED_AS_PASSENGER:
                            $item->setAcceptedAsk(true);

                            break;
                    }
                }
            } else {
                // search for existing matchings with same proposalId as passenger
                // first we check if a user is associated to the proposal (if a user is logged)
                if ($matching['offer']->getProposalRequest()->getUser()) {
                    if ($asks = $this->askRepository->findAskForAd(
                        $matching['offer']->getProposalOffer(),
                        $matching['offer']->getProposalRequest()->getUser(),
                        [
                            Ask::STATUS_INITIATED,
                            Ask::STATUS_PENDING_AS_DRIVER,
                            Ask::STATUS_PENDING_AS_PASSENGER,
                            Ask::STATUS_ACCEPTED_AS_DRIVER,
                            Ask::STATUS_ACCEPTED_AS_PASSENGER,
                        ]
                    )) {
                        foreach ($asks as $ask) {
                            switch ($ask->getStatus()) {
                                    case Ask::STATUS_INITIATED:
                                        $item->setInitiatedAsk(true);

                                        break;

                                    case Ask::STATUS_PENDING_AS_DRIVER:
                                    case Ask::STATUS_PENDING_AS_PASSENGER:
                                        $item->setPendingAsk(true);

                                        break;

                                    case Ask::STATUS_ACCEPTED_AS_DRIVER:
                                    case Ask::STATUS_ACCEPTED_AS_PASSENGER:
                                        $item->setAcceptedAsk(true);

                                        break;
                                }
                        }
                    }
                }
            }

            if (!$return) {
                $resultPassenger->setOutward($item);
            } else {
                $resultPassenger->setReturn($item);
            }

            // seats
            $resultPassenger->setSeatsDriver($matching['offer']->getProposalOffer()->getCriteria()->getSeatsDriver() ? $matching['offer']->getProposalOffer()->getCriteria()->getSeatsDriver() : 1);
            $result->setResultPassenger($resultPassenger);
            $driver = true;
        }

        if ($driver && $passenger) {
            $result->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        } elseif ($passenger) {
            $result->setRole(Ad::ROLE_PASSENGER);
        } else {
            $result->setRole(Ad::ROLE_DRIVER);
        }

        $result->setCommunities($communities);

        // Check if the matching proposal is owned by the caller (if not anonymous)
        if (!is_null($proposal->getUser())) {
            $result->setMyOwn($matchingProposal->getUser()->getId() === $proposal->getUser()->getId());
        }

        return $result;
    }

    /**
     * Get the first carpooled day in a regular trip.
     *
     * @param Proposal $searchProposal   The search Proposal
     * @param Proposal $matchingProposal The matching Proposal
     * @param string   $role             The role (request or offer)
     * @param int      $nbLoop           Current number of try (to avoid infinite loop)
     */
    private function getFirstCarpooledRegularDay(Proposal $searchProposal, Proposal $matchingProposal, string $role = 'request', int $nbLoop = 0): ?array
    {
        // we search the first possible compatible day => the max fromDate between the 2 proposals
        $firstDate = max($searchProposal->getCriteria()->getFromDate(), $matchingProposal->getCriteria()->getFromDate());

        // we will loop for 7 times max to find the first compatible day
        $curDay = $firstDate->format('w');

        $day = $nbLoop + $curDay;
        if ($day >= 7) {
            $day = $day - 7;
        }
        $rdate = new \DateTime();
        $rdate->setTimestamp($firstDate->getTimestamp());
        $rdate->modify('+'.$nbLoop.'days');

        // we check if the tested day is the current day : if so we will force the time check to avoid presenting a past carpool
        $isToday = (new DateTime())->format('Ymd') == $rdate->format('Ymd');
        ++$nbLoop;
        if ($nbLoop > 8) {
            return null;
        } // safeguard to avoid infinite loop
        if ('request' == $role) {
            $result = $this->getValidCarpoolAsRequest($day, $matchingProposal, $searchProposal->getUseTime(), $isToday ? max($searchProposal->getCriteria()->getFromTime(), $rdate) : ($searchProposal->getUseTime() ? $searchProposal->getCriteria()->getFromTime() : null));
        } else {
            $result = $this->getValidCarpoolAsOffer($day, $matchingProposal, $searchProposal->getUseTime() ? $searchProposal->getCriteria()->getFromTime() : null);
        }
        if (!is_array($result)) {
            $result = $this->getFirstCarpooledRegularDay($searchProposal, $matchingProposal, $role, $nbLoop);
        } else {
            $result['date'] = $rdate;
        }

        return $result;
    }

    /**
     * Valid the carpool day between a regular and a part of a regular as Request
     * TO : Use the pickup time instead of the driver's start time.
     *
     * @param int           $day      Day's number
     * @param Proposal      $proposal The Proposal that is matching
     * @param bool          $useTime  If we use the time
     * @param null|DateTime $time     Time of the search if we want to check it
     */
    private function getValidCarpoolAsRequest(int $day, Proposal $proposal, bool $useTime = true, DateTime $time = null): ?array
    {
        switch ($day) {
            case 0:
                if ($proposal->getCriteria()->isSunCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSunTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getSunMinTime()
                        && $time <= $proposal->getCriteria()->getSunMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSunTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getSunMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSunTime()];
                    }
                }

                break;

            case 1:
                if ($proposal->getCriteria()->isMonCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getMonTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getMonMinTime()
                        && $time <= $proposal->getCriteria()->getMonMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getMonTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getMonMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getMonTime()];
                    }
                }

                break;

            case 2:
                if ($proposal->getCriteria()->isTueCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getTueTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getTueMinTime()
                        && $time <= $proposal->getCriteria()->getTueMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getTueTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getTueMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getTueTime()];
                    }
                }

                break;

            case 3:
                if ($proposal->getCriteria()->isWedCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getWedTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getWedMinTime()
                        && $time <= $proposal->getCriteria()->getWedMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getWedTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getWedMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getWedTime()];
                    }
                }

                break;

            case 4:
                if ($proposal->getCriteria()->isThuCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getThuTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getThuMinTime()
                        && $time <= $proposal->getCriteria()->getThuMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getThuTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getThuMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getThuTime()];
                    }
                }

                break;

            case 5:
                if ($proposal->getCriteria()->isFriCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getFriTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getFriMinTime()
                        && $time <= $proposal->getCriteria()->getFriMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getFriTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getFriMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getFriTime()];
                    }
                }

                break;

            case 6:
                if ($proposal->getCriteria()->isSatCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSatTime()];
                    }
                    if (
                        $useTime
                        && $time >= $proposal->getCriteria()->getSatMinTime()
                        && $time <= $proposal->getCriteria()->getSatMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSatTime()];
                    }
                    if (
                        !$useTime
                        && $time <= $proposal->getCriteria()->getSatMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSatTime()];
                    }
                }

                break;
        }

        return null;
    }

    /**
     * Valid the carpool day between a regular and a part of a regular as Offer
     * TO : Use the pickup time instead of the driver's start time.
     *
     * @param int           $day      Day's number
     * @param Proposal      $proposal The Proposal that is matching
     * @param null|DateTime $time     Time of the search if we want to check it
     */
    private function getValidCarpoolAsOffer(int $day, Proposal $proposal, DateTime $time = null): ?array
    {
        switch ($day) {
            case 0:
                if ($proposal->getCriteria()->isSunCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSunTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getSunMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSunTime()];
                    }
                }

                break;

            case 1:
                if ($proposal->getCriteria()->isMonCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getMonTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getMonMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getMonTime()];
                    }
                }

                break;

            case 2:
                if ($proposal->getCriteria()->isTueCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getTueTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getTueMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getTueTime()];
                    }
                }

                break;

            case 3:
                if ($proposal->getCriteria()->isWedCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getWedTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getWedMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getWedTime()];
                    }
                }

                break;

            case 4:
                if ($proposal->getCriteria()->isThuCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getThuTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getThuMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getThuTime()];
                    }
                }

                break;

            case 5:
                if ($proposal->getCriteria()->isFriCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getFriTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getFriMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getFriTime()];
                    }
                }

                break;

            case 6:
                if ($proposal->getCriteria()->isSatCheck()
                ) {
                    if (is_null($time)) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSatTime()];
                    }
                    if (
                        $time <= $proposal->getCriteria()->getSatMaxTime()
                    ) {
                        return ['numday' => $day, 'time' => $proposal->getCriteria()->getSatTime()];
                    }
                }

                break;
        }

        return null;
    }

    /**
     * Get the right matching day of a regular as a Request.
     *
     * @param Matching   $matching The matching
     * @param ResultItem $item     The result item
     */
    private function getMatchingRegularDayAsRequest(Matching $matching, ResultItem $item): ?array
    {
        $proposal = $matching->getProposalRequest();
        $result = $this->getFirstCarpooledRegularDay($proposal, $matching->getProposalOffer(), 'request');
        $item->setDate($result['date']);

        switch ($result['numday']) {
            case 0:
                $item->setSunMarginDuration($matching->getProposalOffer()->getCriteria()->getSunMarginDuration());

                break;

            case 1:
                $item->setMonMarginDuration($matching->getProposalOffer()->getCriteria()->getMonMarginDuration());

                break;

            case 2:
                $item->setTueMarginDuration($matching->getProposalOffer()->getCriteria()->getTueMarginDuration());

                break;

            case 3:
                $item->setWedMarginDuration($matching->getProposalOffer()->getCriteria()->getWedMarginDuration());

                break;

            case 4:
                $item->setThuMarginDuration($matching->getProposalOffer()->getCriteria()->getThuMarginDuration());

                break;

            case 5:
                $item->setFriMarginDuration($matching->getProposalOffer()->getCriteria()->getFriMarginDuration());

                break;

            case 6:
                $item->setSatMarginDuration($matching->getProposalOffer()->getCriteria()->getSatMarginDuration());

                break;

            default:
                return null;
        }

        return [
            'item' => $item,
            'time' => $result['time'],
        ];
    }

    /**
     * Get the right matching day of a regular as an Offer.
     *
     * @param Matching   $matching The matching
     * @param ResultItem $item     The result item
     */
    private function getMatchingRegularDayAsOffer(Matching $matching, ResultItem $item): ?array
    {
        $proposal = $matching->getProposalOffer();
        $result = $this->getFirstCarpooledRegularDay($proposal, $matching->getProposalRequest(), 'offer');
        $item->setDate($result['date']);

        switch ($result['numday']) {
            case 0:
                $item->setSunMarginDuration($matching->getProposalRequest()->getCriteria()->getSunMarginDuration());

                break;

            case 1:
                $item->setMonMarginDuration($matching->getProposalRequest()->getCriteria()->getMonMarginDuration());

                break;

            case 2:
                $item->setTueMarginDuration($matching->getProposalRequest()->getCriteria()->getTueMarginDuration());

                break;

            case 3:
                $item->setWedMarginDuration($matching->getProposalRequest()->getCriteria()->getWedMarginDuration());

                break;

            case 4:
                $item->setThuMarginDuration($matching->getProposalRequest()->getCriteria()->getThuMarginDuration());

                break;

            case 5:
                $item->setFriMarginDuration($matching->getProposalRequest()->getCriteria()->getFriMarginDuration());

                break;

            case 6:
                $item->setSatMarginDuration($matching->getProposalRequest()->getCriteria()->getSatMarginDuration());

                break;

            default:
                return null;
        }

        return [
            'item' => $item,
            'time' => $result['time'],
        ];
    }

    /**
     * Check if the given result complies with the given role.
     *
     * @param Result $result The result to test
     * @param int    $role   The role
     */
    private static function filterByRole(Result $result, int $role): bool
    {
        switch ($role) {
            case Ad::ROLE_DRIVER: return !is_null($result->getResultPassenger()) && is_null($result->getResultDriver());

            case Ad::ROLE_PASSENGER: return !is_null($result->getResultDriver()) && is_null($result->getResultPassenger());

            case Ad::ROLE_DRIVER_OR_PASSENGER: return !is_null($result->getResultDriver()) && !is_null($result->getResultPassenger());
        }

        return false;
    }

    /**
     * Create a ResultRole for a given Ask.
     *
     * @param Ask $ask  The ask
     * @param int $role The role of the requester
     *
     * @return ResultRole The resultRole
     */
    private function createAskResultRole(Ask $ask, int $role): ResultRole
    {
        $resultRole = new ResultRole();
        $resultRole->setSeatsDriver($ask->getCriteria()->getSeatsDriver());
        $resultRole->setSeatsPassenger($ask->getCriteria()->getSeatsPassenger());

        $outward = null;
        $return = null;

        // we create the results for the outward
        $outward = $this->createAskResultItem($ask, $role);

        // we create the results for the return
        if ($ask->getAskLinked()) {
            $return = $this->createAskResultItem($ask->getAskLinked(), $role);
        }

        // we return the result

        $resultRole->setOutward($outward);
        $resultRole->setReturn($return);

        return $resultRole;
    }

    /**
     * Create a ResultItem for a given Ask.
     *
     * @param Ask $ask  The ask
     * @param int $role The role of the requester
     *
     * @return ResultItem The resultItem
     */
    private function createAskResultItem(Ask $ask, int $role): ResultItem
    {
        // we compute the filters
        if (is_null($ask->getFilters())) {
            $ask->setFilters($this->proposalMatcher->getAskFilters($ask));
        }

        $item = new ResultItem();

        $filters = $ask->getFilters();
        foreach ($filters['route'] as $value) {
            if (2 == $value['candidate'] && 0 == $value['position']) {
                break;
            }
        }

        if (Criteria::FREQUENCY_PUNCTUAL == $ask->getCriteria()->getFrequency()) {
            // the ask is punctual; for now the time are the same
            // if the proposal is private we use matching proposal date and time
            $date = $ask->getCriteria()->getFromDate();
            $time = $ask->getCriteria()->getFromTime();
            $time = (null == $time) ? null : $time;
            $item->setDate($date);
            $item->setTime($time);
        } else {
            // the ask is regular, the days depends on the ask status
            $item->setMonCheck($ask->getCriteria()->isMonCheck());
            $item->setTueCheck($ask->getCriteria()->isTueCheck());
            $item->setWedCheck($ask->getCriteria()->isWedCheck());
            $item->setThuCheck($ask->getCriteria()->isThuCheck());
            $item->setFriCheck($ask->getCriteria()->isFriCheck());
            $item->setSatCheck($ask->getCriteria()->isSatCheck());
            $item->setSunCheck($ask->getCriteria()->isSunCheck());
            $hasTime = false;
            if ($ask->getCriteria()->getMonTime()) {
                $item->setMonTime($ask->getCriteria()->getMonTime());
                $item->setTime($item->getMonTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getTueTime()) {
                $item->setTueTime($ask->getCriteria()->getTueTime());
                $item->setTime($item->getTueTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getWedTime()) {
                $item->setWedTime($ask->getCriteria()->getWedTime());
                $item->setTime($item->getWedTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getThuTime()) {
                $item->setThuTime($ask->getCriteria()->getThuTime());
                $item->setTime($item->getThuTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getFriTime()) {
                $item->setFriTime($ask->getCriteria()->getFriTime());
                $item->setTime($item->getFriTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getSatTime()) {
                $item->setSatTime($ask->getCriteria()->getSatTime());
                $item->setTime($item->getSatTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getSunTime()) {
                $item->setSunTime($ask->getCriteria()->getSunTime());
                $item->setTime($item->getSunTime());
                $hasTime = true;
            }
            if (!$hasTime) {
                // no time has been set, we have to compute them
                // it can be the case after a regular search, as the times are not asked
                // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                // we init the time to the one of the carpooler
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isMonCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getMonTime())) {
                    $monTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getMonTime();
                    $item->setMonTime($monTime);
                    $item->setTime($monTime);
                }
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isTueCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getTueTime())) {
                    $tueTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getTueTime();
                    $item->setTueTime($tueTime);
                    $item->setTime($tueTime);
                }
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isWedCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getWedTime())) {
                    $wedTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getWedTime();
                    $item->setWedTime($wedTime);
                    $item->setTime($wedTime);
                }
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isThuCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getThuTime())) {
                    $thuTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getThuTime();
                    $item->setThuTime($thuTime);
                    $item->setTime($thuTime);
                }
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isFriCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getFriTime())) {
                    $friTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getFriTime();
                    $item->setFriTime($friTime);
                    $item->setTime($friTime);
                }
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isSatCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getSatTime())) {
                    $satTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getSatTime();
                    $item->setSatTime($satTime);
                    $item->setTime($satTime);
                }
                if ($ask->getMatching()->getProposalRequest()->getCriteria()->isSunCheck()
                    && !is_null($ask->getMatching()->getProposalRequest()->getCriteria()->getSunTime())) {
                    $sunTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getSunTime();
                    $item->setSunTime($sunTime);
                    $item->setTime($sunTime);
                }
            }
            // we update times with pick up duration
            else {
                if ($item->isMonCheck()
                    && !is_null($item->getMonTime())) {
                    $monTime = $item->getMonTime();
                    $item->setMonTime($monTime);
                    $item->setTime($monTime);
                }
                if ($item->isTueCheck()
                    && !is_null($item->getTueTime())) {
                    $tueTime = $item->getTueTime();
                    $item->setTueTime($tueTime);
                    $item->setTime($tueTime);
                }
                if ($item->isWedCheck()
                    && !is_null($item->getWedTime())) {
                    $wedTime = $item->getWedTime();
                    $item->setWedTime($wedTime);
                    $item->setTime($wedTime);
                }
                if ($item->isThuCheck()
                    && !is_null($item->getThuTime())) {
                    $thuTime = $item->getThuTime();
                    $item->setThuTime($thuTime);
                    $item->setTime($thuTime);
                }
                if ($item->isFriCheck()
                    && !is_null($item->getFriTime())) {
                    $friTime = $item->getFriTime();
                    $item->setFriTime($friTime);
                    $item->setTime($friTime);
                }
                if ($item->isSatCheck()
                    && !is_null($item->getSatTime())) {
                    $satTime = $item->getSatTime();
                    $item->setSatTime($satTime);
                    $item->setTime($satTime);
                }
                if ($item->isSunCheck()
                    && !is_null($item->getSunTime())) {
                    $sunTime = $item->getSunTime();
                    $item->setSunTime($sunTime);
                    $item->setTime($sunTime);
                }
            }
            $item->setMultipleTimes($hasTime);
            if ($item->hasMultipleTimes()) {
                $item->setTime(null);
            }
            $item->setFromDate($ask->getCriteria()->getFromDate());
            $item->setToDate($ask->getCriteria()->getToDate());
        }
        // waypoints of the outward
        $waypoints = [];

        $time = $item->getTime() ? clone $item->getTime() : null;

        // we will have to compute the number of steps for each candidate
        $steps = [
            'requester' => 0,
            'carpooler' => 0,
        ];
        // first pass to get the maximum position fo each candidate
        foreach ($ask->getFilters()['route'] as $key => $waypoint) {
            if (Ad::ROLE_DRIVER == $role) {
                if (1 == $waypoint['candidate'] && (int) $waypoint['position'] > $steps['requester']) {
                    $steps['requester'] = (int) $waypoint['position'];
                } elseif (2 == $waypoint['candidate'] && (int) $waypoint['position'] > $steps['carpooler']) {
                    $steps['carpooler'] = (int) $waypoint['position'];
                }
            } else {
                if (1 == $waypoint['candidate'] && (int) $waypoint['position'] > $steps['carpooler']) {
                    $steps['carpooler'] = (int) $waypoint['position'];
                } elseif (2 == $waypoint['candidate'] && (int) $waypoint['position'] > $steps['requester']) {
                    $steps['requester'] = (int) $waypoint['position'];
                }
            }
        }
        // second pass to fill the waypoints array
        foreach ($ask->getFilters()['route'] as $key => $waypoint) {
            $curTime = null;
            if ($time) {
                $curTime = clone $time;
            }
            if ($curTime) {
                $curTime->add(new \DateInterval('PT'.(int) round($waypoint['duration']).'S'));
            }
            if (Ad::ROLE_DRIVER == $role) {
                $waypoints[$key] = [
                    'id' => $key,
                    'person' => 1 == $waypoint['candidate'] ? 'requester' : 'carpooler',
                    'role' => 1 == $waypoint['candidate'] ? 'driver' : 'passenger',
                    'time' => $curTime,
                    'address' => $waypoint['address'],
                    'type' => '0' == $waypoint['position'] ? 'origin' :
                        (
                            (1 == $waypoint['candidate']) ? ((int) $waypoint['position'] == $steps['requester'] ? 'destination' : 'step') :
                            ((int) $waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step')
                        ),
                ];
            } else {
                $waypoints[$key] = [
                    'id' => $key,
                    'person' => 1 == $waypoint['candidate'] ? 'carpooler' : 'requester',
                    'role' => 1 == $waypoint['candidate'] ? 'driver' : 'passenger',
                    'time' => $curTime,
                    'address' => $waypoint['address'],
                    'type' => '0' == $waypoint['position'] ? 'origin' :
                        (
                            (1 == $waypoint['candidate']) ? ((int) $waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step') :
                            ((int) $waypoint['position'] == $steps['requester'] ? 'destination' : 'step')
                        ),
                ];
            }
            // origin and destination guess
            if (Ad::ROLE_DRIVER == $role) {
                if (2 == $waypoint['candidate'] && '0' == $waypoint['position']) {
                    $item->setOrigin($waypoint['address']);
                    $item->setOriginPassenger($waypoint['address']);
                } elseif (2 == $waypoint['candidate'] && (int) $waypoint['position'] == $steps['carpooler']) {
                    $item->setDestination($waypoint['address']);
                    $item->setDestinationPassenger($waypoint['address']);
                } elseif (1 == $waypoint['candidate'] && '0' == $waypoint['position']) {
                    $item->setOriginDriver($waypoint['address']);
                } elseif (1 == $waypoint['candidate'] && (int) $waypoint['position'] == $steps['requester']) {
                    $item->setDestinationDriver($waypoint['address']);
                }
            } else {
                if (2 == $waypoint['candidate'] && '0' == $waypoint['position']) {
                    $item->setOrigin($waypoint['address']);
                    $item->setOriginPassenger($waypoint['address']);
                } elseif (2 == $waypoint['candidate'] && (int) $waypoint['position'] == $steps['requester']) {
                    $item->setDestination($waypoint['address']);
                    $item->setDestinationPassenger($waypoint['address']);
                } elseif (1 == $waypoint['candidate'] && '0' == $waypoint['position']) {
                    $item->setOriginDriver($waypoint['address']);
                } elseif (1 == $waypoint['candidate'] && (int) $waypoint['position'] == $steps['carpooler']) {
                    $item->setDestinationDriver($waypoint['address']);
                }
            }
        }
        $item->setWaypoints($waypoints);

        // statistics
        $item->setOriginalDistance($ask->getFilters()['originalDistance']);
        $item->setAcceptedDetourDistance($ask->getFilters()['acceptedDetourDistance']);
        $item->setNewDistance($ask->getFilters()['newDistance']);
        $item->setDetourDistance($ask->getFilters()['detourDistance']);
        $item->setDetourDistancePercent($ask->getFilters()['detourDistancePercent']);
        $item->setOriginalDuration($ask->getFilters()['originalDuration']);
        $item->setAcceptedDetourDuration($ask->getFilters()['acceptedDetourDuration']);
        $item->setNewDuration($ask->getFilters()['newDuration']);
        $item->setDetourDuration($ask->getFilters()['detourDuration']);
        $item->setDetourDurationPercent($ask->getFilters()['detourDurationPercent']);
        $item->setCommonDistance($ask->getFilters()['commonDistance']);

        // prices
        $item->setDriverPriceKm($ask->getMatching()->getProposalOffer()->getCriteria()->getPriceKm());
        $item->setDriverOriginalPrice($ask->getMatching()->getProposalOffer()->getCriteria()->getDriverPrice());
        $item->setPassengerPriceKm($ask->getMatching()->getProposalRequest()->getCriteria()->getPriceKm());
        $item->setPassengerOriginalPrice($ask->getMatching()->getProposalRequest()->getCriteria()->getPassengerPrice());
        // to check...
        $item->setComputedPrice($ask->getCriteria()->getPassengerComputedPrice());
        $item->setComputedRoundedPrice($ask->getCriteria()->getPassengerComputedRoundedPrice());

        return $item;
    }

    /**
     * Determine if the $user can get a review from the current User.
     *
     * @param User $reviewer The reviewer
     * @param User $reviewed The reviewed
     *
     * @return User The reviewed with "canReceiveReview" setted
     */
    private function canReceiveReview(User $reviewer, User $reviewed): User
    {
        // Using the dashboard of the currentUser but specifically with the user possibly to review
        // If there is a 'reviewsToGive' in the array, then the current user can leave a review for this specific user
        $reviews = $this->reviewManager->getReviewDashboard($reviewer, $reviewed);
        $reviewed->setCanReceiveReview(false);
        if (!$this->userReview) {
            // Review system disable.
            return $reviewed;
        }
        if (is_array($reviews->getReviewsToGive()) && count($reviews->getReviewsToGive()) > 0) {
            $reviewed->setCanReceiveReview(true);
        }

        return $reviewed;
    }
}
