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

namespace App\Payment\Service;

use App\Carpool\Entity\Criteria;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Ressource\PaymentItem;
use App\Payment\Ressource\PaymentPayment;
use App\User\Entity\User;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\AskRepository;
use App\Payment\Exception\PaymentException;
use App\Payment\Repository\CarpoolItemRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Payment manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PaymentManager
{
    const MIN_WEEK = 1;
    const MAX_WEEK = 52;
    const MIN_YEAR = 1970;
    const MAX_YEAR = 2999;

    private $entityManager;
    private $carpoolItemRepository;
    private $askRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager         The entity manager
     * @param CarpoolItemRepository $carpoolItemRepository  The carpool items repository
     * @param AskRepository $askRepository                  The ask repository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CarpoolItemRepository $carpoolItemRepository,
        AskRepository $askRepository
    ) {
        $this->entityManager = $entityManager;
        $this->carpoolItemRepository = $carpoolItemRepository;
        $this->askRepository = $askRepository;
    }

    /**
     * Get the payment items : create the lacking items from the accepted asks, construct the array of Payment Items
     *
     * @param User $user            The user concerned
     * @param integer $frequency    The frequency for the items (1 = punctual, 2 = regular)
     * @param integer $type         The type of items (1 = to pay, 2 = to collect)
     * @param string|null $week     The week and year for regular items, under the form WWYYYY (ex : 052020 pour for the week 05 of year 2020)
     * @return array The payment items found
     */
    public function getPaymentItems(User $user, int $frequency = 1, int $type = 1, ?string $week = null)
    {
        $items = [];
        
        $fromDate = null;
        $toDate = null;

        if ($frequency == Criteria::FREQUENCY_REGULAR) {
            if (is_null($week)) {
                throw new PaymentException(PaymentException::WEEK_NOT_PROVIDED);
            }
            $weekNumber = (int)substr($week, 0, 2);
            $weekYear = (int)substr($week, 2);
            if ($weekNumber<self::MIN_WEEK || $weekNumber>self::MAX_WEEK || $weekYear<self::MIN_YEAR || $weekYear>self::MAX_YEAR) {
                throw new PaymentException(PaymentException::WEEK_WRONG_FORMAT);
            }
            $fromDate = new DateTime();
            $fromDate->setISODate($weekYear, $weekNumber);
            $toDate = new DateTime();
            $toDate->setISODate($weekYear, $weekNumber, 7);
        }

        // we create the carpool items in case they don't exist yet
        $this->createCarpoolItems($fromDate, $toDate, $user);

        // we get the carpool items
        $carpoolItems = $this->getCarpoolItems($frequency, $type, $user, $fromDate, $toDate);

        // for regular items, we need to find the outward and return amount, and the days carpooled
        $regularAmounts = [];
        $regularDays = [];
        if ($frequency == Criteria::FREQUENCY_REGULAR) {
            foreach ($carpoolItems as $carpoolItem) {
                /**
                 * @var CarpoolItem $carpoolItem
                 */
                if (($carpoolItem->getType() == Proposal::TYPE_ONE_WAY || $carpoolItem->getType() == Proposal::TYPE_OUTWARD) && !isset($regularAmounts[$carpoolItem->getAsk()->getId()]['outward'])) {
                    $regularAmounts[$carpoolItem->getAsk()->getId()]['outward'] = $carpoolItem->getAmount();
                } elseif ($carpoolItem->getType() == Proposal::TYPE_RETURN && !isset($regularAmounts[$carpoolItem->getAsk()->getId()]['return'])) {
                    $regularAmounts[$carpoolItem->getAsk()->getId()]['return'] = $carpoolItem->getAmount();
                }
                // we initialize each week day
                if (!isset($regularDays[$carpoolItem->getAsk()->getId()])) {
                    $regularDays[$carpoolItem->getAsk()->getId()]['outward'] = [
                        PaymentItem::DAY_UNAVAILABLE,
                        PaymentItem::DAY_UNAVAILABLE,
                        PaymentItem::DAY_UNAVAILABLE,
                        PaymentItem::DAY_UNAVAILABLE,
                        PaymentItem::DAY_UNAVAILABLE,
                        PaymentItem::DAY_UNAVAILABLE,
                        PaymentItem::DAY_UNAVAILABLE
                    ];
                    $regularDays[$carpoolItem->getAsk()->getId()]['return'] = $regularDays[$carpoolItem->getAsk()->getId()]['outward'];
                }
                // we set the corresponding day
                if ($carpoolItem->getType() == Proposal::TYPE_RETURN) {
                    $regularDays[$carpoolItem->getAsk()->getId()]['return'][$carpoolItem->getItemDate()->format('w')] = PaymentItem::DAY_CARPOOLED;
                } else {
                    $regularDays[$carpoolItem->getAsk()->getId()]['outward'][$carpoolItem->getItemDate()->format('w')] = PaymentItem::DAY_CARPOOLED;
                }
            }
        }

        // we keep a trace of already treated asks (we return one item for a single ask, even for regular items)
        $treatedAsks = [];

        // then we create each payment item from the carpool items
        foreach ($carpoolItems as $carpoolItem) {
            /**
             * @var CarpoolItem $carpoolItem
             */
            if (in_array($carpoolItem->getAsk()->getId(), $treatedAsks)) {
                continue;
            }
            $paymentItem = new PaymentItem($carpoolItem->getId());
            $paymentItem->setType($carpoolItem->getType());
            if ($type == PaymentItem::TYPE_PAY) {
                $paymentItem->setGivenName($carpoolItem->getCreditorUser()->getGivenName());
                $paymentItem->setShortFamilyName($carpoolItem->getCreditorUser()->getShortFamilyName());
            } else {
                $paymentItem->setGivenName($carpoolItem->getDebtorUser()->getGivenName());
                $paymentItem->setShortFamilyName($carpoolItem->getDebtorUser()->getShortFamilyName());
            }
            $paymentItem->setFrequency($frequency);
            if ($paymentItem->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                $paymentItem->setDate($carpoolItem->getItemDate());
                $paymentItem->setAmount($carpoolItem->getAmount());
            } else {
                $paymentItem->setFromDate($fromDate);
                $paymentItem->setToDate($toDate);
                $paymentItem->setOutwardAmount($regularAmounts[$carpoolItem->getAsk()->getId()]['outward']);
                $paymentItem->setOutwardDays($regularDays[$carpoolItem->getAsk()->getId()]['outward']);
                $paymentItem->setReturnAmount($regularAmounts[$carpoolItem->getAsk()->getId()]['return']);
                $paymentItem->setReturnDays($regularDays[$carpoolItem->getAsk()->getId()]['return']);
            }
            // we iterate through the waypoints to get the passenger origin and destination
            $minPos = 9999;
            $maxPos = -1;
            foreach ($carpoolItem->getAsk()->getWaypoints() as $waypoint) {
                /**
                 * @var Waypoint $waypoint
                 */
                if ($waypoint->getRole() == Waypoint::ROLE_PASSENGER) {
                    if ($waypoint->getPosition()<$minPos) {
                        $paymentItem->setOrigin($waypoint->getAddress());
                        $minPos = $waypoint->getPosition();
                    } elseif ($waypoint->getPosition()>$maxPos) {
                        $paymentItem->setDestination($waypoint->getAddress());
                        $maxPos = $waypoint->getPosition();
                    }
                }
            }
            $items[] = $paymentItem;
            $treatedAsks[] = $carpoolItem->getAsk()->getId();
        }

        // finally we return the array of PaymentItem
        return $items;
    }

    public function createPaymentPayment(PaymentPayment $payment)
    {
        // TODO : create the real payment !
        $payment->setStatus(rand(PaymentPayment::STATUS_SUCCESS, PaymentPayment::STATUS_FAILURE));
        return $payment;
    }

    /**
     * Create the carpool payment items from the accepted asks.
     *
     * @param DateTime|null $fromDate   The start of the period for which we want to create the items
     * @param DateTime|null $toDate     The end of the period  for which we want to create the items
     * @param User|null $user           The user concerned (if no user is provided we generate the items for everyone)
     * @return void
     */
    public function createCarpoolItems(?DateTime $fromDate = null, ?DateTime $toDate = null, ?User $user = null)
    {
        // if no dates are sent, we use the origin of times till the previous day
        if (is_null($fromDate)) {
            $fromDate = new DateTime('1970-01-01');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        // first we search the accepted asks for the given period and the given user
        $asks = $this->askRepository->findAcceptedAsksForPeriod($fromDate, $toDate, $user);

        // then we create the corresponding items
        foreach ($asks as $ask) {
            /**
             * @var Ask $ask
             */
            if ($ask->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                // punctual, we search if a carpool payment item already exists for the date
                if (!$this->carpoolItemRepository->findByAskAndDate($ask, $ask->getCriteria()->getFromDate())) {
                    // no carpool item for this date, we create it
                    $carpoolItem = new CarpoolItem();
                    $carpoolItem->setAsk($ask);
                    $carpoolItem->setType($ask->getType());
                    $carpoolItem->setAmount($ask->getCriteria()->getPassengerComputedRoundedPrice());
                    $carpoolItem->setDebtorUser($ask->getMatching()->getProposalRequest()->getUser());
                    $carpoolItem->setCreditorUser($ask->getMatching()->getProposalOffer()->getUser());
                    $carpoolItem->setItemDate($ask->getCriteria()->getFromDate());
                    $this->entityManager->persist($carpoolItem);
                }
            } else {
                // regular, we need to create a carpool item for each day between fromDate and toDate
                $curDate = clone $fromDate;
                $continue = true;
                while ($continue) {
                    // we check if the current day is a carpool day
                    $carpoolDay = false;
                    switch ($curDate->format('w')) {
                        case 0:     // sunday
                            if ($ask->getCriteria()->isSunCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                        case 1:     // monday
                            if ($ask->getCriteria()->isMonCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                        case 2:     // tuesday
                            if ($ask->getCriteria()->isTueCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                        case 3:     // wednesday
                            if ($ask->getCriteria()->isWedCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                        case 4:     // thursday
                            if ($ask->getCriteria()->isThuCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                        case 5:     // friday
                            if ($ask->getCriteria()->isFriCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                        case 6:     // saturday
                            if ($ask->getCriteria()->isSatCheck()) {
                                $carpoolDay = true;
                            }
                            break;
                    }

                    // we search if a carpool item already exists for the date
                    if ($carpoolDay && !$this->carpoolItemRepository->findByAskAndDate($ask, $curDate)) {
                        // no carpool item for this date, we create it
                        $carpoolItem = new CarpoolItem();
                        $carpoolItem->setAsk($ask);
                        $carpoolItem->setType($ask->getType());
                        $carpoolItem->setAmount($ask->getCriteria()->getPassengerComputedRoundedPrice());
                        $carpoolItem->setDebtorUser($ask->getMatching()->getProposalRequest()->getUser());
                        $carpoolItem->setCreditorUser($ask->getMatching()->getProposalOffer()->getUser());
                        $carpoolItem->setItemDate(clone $curDate);
                        $this->entityManager->persist($carpoolItem);
                    }

                    if ($curDate->format('Y-m-d') == $toDate->format('Y-m-d')) {
                        $continue = false;
                    } else {
                        $curDate->modify('+1 day');
                    }
                }
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Get the carpool payment items for the given frequency, type, period and user
     *
     * @param integer $frequency        The frequency for the items
     * @param integer $type             The type of items (1 = to pay, 2 = to collect)
     * @param User $user                The user concerned
     * @param DateTime|null $fromDate   The start of the period for which we want to get the items
     * @param DateTime|null $toDate     The end of the period  for which we want to get the items
     * @return array                    The items found
     */
    public function getCarpoolItems(int $frequency, int $type, User $user, ?DateTime $fromDate = null, ?DateTime $toDate = null)
    {
        // if no dates are sent, we use the origin of times till the previous day
        if (is_null($fromDate)) {
            $fromDate = new DateTime('1970-01-01');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        return $this->carpoolItemRepository->findForPayments($frequency, $type, $user, $fromDate, $toDate);
    }
}
