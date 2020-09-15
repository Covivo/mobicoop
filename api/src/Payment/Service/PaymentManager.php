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
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\AskRepository;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Repository\CarpoolItemRepository;
use DateTime;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Exception\PaymentException;
use App\Payment\Repository\CarpoolPaymentRepository;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Payment manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
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
    private $provider;
    private $paymentProvider;
    private $paymentProfileRepository;
    private $userManager;
    private $paymentActive;
    private $securityToken;
    private $carpoolPaymentRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager                 The entity manager
     * @param CarpoolItemRepository $carpoolItemRepository          The carpool items repository
     * @param AskRepository $askRepository                          The ask repository
     * @param CarpoolItemRepository $carpoolItemRepository          The carpool items repository
     * @param PaymentDataProvider $paymentProvider                  The payment data provider
     * @param PaymentProfileRepository $paymentProfileRepository    The payment profile repository
     * @param boolean $paymentActive                                If the online payment is active
     * @param string $paymentProviderService                        The payment provider service
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CarpoolItemRepository $carpoolItemRepository,
        CarpoolPaymentRepository $carpoolPaymentRepository,
        AskRepository $askRepository,
        PaymentDataProvider $paymentProvider,
        PaymentProfileRepository $paymentProfileRepository,
        UserManager $userManager,
        bool $paymentActive,
        String $paymentProviderService,
        string $securityToken
    ) {
        $this->entityManager = $entityManager;
        $this->carpoolItemRepository = $carpoolItemRepository;
        $this->carpoolPaymentRepository = $carpoolPaymentRepository;
        $this->askRepository = $askRepository;
        $this->provider = $paymentProviderService;
        $this->entityManager = $entityManager;
        $this->paymentProvider = $paymentProvider;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->userManager = $userManager;
        $this->paymentActive = $paymentActive;
        $this->securityToken = $securityToken;
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
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE],
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE],
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE],
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE],
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE],
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE],
                        ['id'=>null, 'status'=>PaymentItem::DAY_UNAVAILABLE]
                    ];
                    $regularDays[$carpoolItem->getAsk()->getId()]['return'] = $regularDays[$carpoolItem->getAsk()->getId()]['outward'];
                }
                // we set the corresponding day
                //if ($carpoolItem->getType() == Proposal::TYPE_RETURN) {
                $regularDays[$carpoolItem->getAsk()->getId()]['return'][$carpoolItem->getItemDate()->format('w')]['id'] = $carpoolItem->getId();
                $regularDays[$carpoolItem->getAsk()->getId()]['return'][$carpoolItem->getItemDate()->format('w')]['status'] = PaymentItem::DAY_CARPOOLED;
                //} else {
                $regularDays[$carpoolItem->getAsk()->getId()]['outward'][$carpoolItem->getItemDate()->format('w')]['id'] = $carpoolItem->getId();
                $regularDays[$carpoolItem->getAsk()->getId()]['outward'][$carpoolItem->getItemDate()->format('w')]['status'] = PaymentItem::DAY_CARPOOLED;
                // }
            }
        }
        //var_dump($regularAmounts);die;
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
            $paymentItem->setAskId($carpoolItem->getAsk()->getId());
            $paymentItem->setType($type);
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
                
                if (isset($regularAmounts[$carpoolItem->getAsk()->getId()]['outward'])) {
                    $paymentItem->setOutwardAmount($regularAmounts[$carpoolItem->getAsk()->getId()]['outward']);
                    $paymentItem->setOutwardDays($regularDays[$carpoolItem->getAsk()->getId()]['outward']);
                }

                // If there a return, we treat it now to return only one payementItem
                if (!is_null($carpoolItem->getAsk()->getAskLinked()) && isset($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'])) {
                    $paymentItem->setReturnAmount($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['return']);
                    $paymentItem->setReturnDays($regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['return']);
                }
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

            // Set if the paymentItem is payable electonically (if the Creditor User has a paymentProfile electronicallyPayable)
            
            // default value
            $paymentItem->setElectronicallyPayable(false);

            if ($this->paymentActive && $this->provider !== "") {
                $paymentProfile = $this->paymentProvider->getPaymentProfiles($carpoolItem->getCreditorUser(), false);
                if (is_null($paymentProfile) || count($paymentProfile)==0) {
                    $paymentItem->setElectronicallyPayable(false);
                } else {
                    $paymentItem->setElectronicallyPayable($paymentProfile[0]->isElectronicallyPayable());
                }
            }

            // If there is an Unpaid Date, we set the unpaid date of the PaymentItem
            $paymentItem->setUnpaidDate($carpoolItem->getUnpaidDate());
            
            $items[] = $paymentItem;
            $treatedAsks[] = $carpoolItem->getAsk()->getId();
            if (!is_null($carpoolItem->getAsk()->getAskLinked())) {
                $treatedAsks[] = $carpoolItem->getAsk()->getAskLinked()->getId();
            }
        }

        // finally we return the array of PaymentItem
        return $items;
    }

    /**
     * Create a Payment Payment : a payment for one or many carpool items
     *
     * @param PaymentPayment $payment   The payments to make
     * @param User $user                The user that makes/receive the payment
     * @return PaymentPayment The resulting payment (with updated statuses)
     */
    public function createPaymentPayment(PaymentPayment $payment, User $user)
    {
        if ($payment->getType() != PaymentPayment::TYPE_PAY && $payment->getType() != PaymentPayment::TYPE_VALIDATE) {
            throw new PaymentException('Wrong payment type');
        }

        // we assume the payment is failed until it's a success !
        //$payment->setStatus(PaymentPayment::STATUS_FAILURE);

        if ($payment->getType() == PaymentPayment::TYPE_PAY) {
            // PAY
            
            // we create the payment
            $carpoolPayment = new CarpoolPayment();
            $carpoolPayment->setUser($user);
            $carpoolPayment->setStatus(CarpoolPayment::STATUS_INITIATED);

            // for a payment, we need to compute the total amount
            $amountDirect = 0;
            $amountOnline = 0;

            foreach ($payment->getItems() as $item) {
                if (!$carpoolItem = $this->carpoolItemRepository->find($item['id'])) {
                    throw new PaymentException('Wrong item id');
                }
                if ($carpoolItem->getDebtorUser()->getId() != $user->getId()) {
                    throw new PaymentException('This user is not the debtor of item #' . $item['id']);
                }
                // if the day is carpooled, we need to pay !
                if ($item["status"] == PaymentItem::DAY_CARPOOLED) {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_REALIZED);
                    if ($item['mode'] == PaymentPayment::MODE_DIRECT) {
                        $amountDirect += $carpoolItem->getAmount();
                    } else {
                        $amountOnline += $carpoolItem->getAmount();
                    }
                } else {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_NOT_REALIZED);
                }
                // we add the CarpoolItem to the array item
                $item['carpoolItem'] = $carpoolItem;
                $carpoolPayment->addCarpoolItem($carpoolItem);
            }

            $carpoolPayment->setAmount($amountDirect + $amountOnline);

            // we persist the payment
            $this->entityManager->persist($carpoolPayment);
            $this->entityManager->flush();

            foreach ($payment->getItems() as $item) {
                $carpoolItem = $this->carpoolItemRepository->find($item['id']);
                if ($item["status"] == PaymentItem::DAY_CARPOOLED) {
                    if ($item['mode'] == PaymentPayment::MODE_DIRECT) {
                        $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT);
                    } else {
                        $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE);
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_PENDING_ONLINE);
                    }
                }
                $this->entityManager->persist($carpoolItem);
            }

            // if online amount is not zero, we pay online
            if ($amountOnline>0) {
                $this->paymentProvider->generateElectronicPaymentUrl($carpoolPayment);
                $payment->setRedirectUrl($carpoolPayment->getRedirectUrl());
            } else {
                $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
                $this->treatCarpoolPayment($carpoolPayment);
            }

            $this->entityManager->persist($carpoolPayment);
            $this->entityManager->flush();
        } else {

            // COLLECT
            // Array of carpoolPayment we could generate if there are DIRECT payments not previously validated par debtors
            $carpoolPayments = [];
            

            foreach ($payment->getItems() as $item) {
                if (!$carpoolItem = $this->carpoolItemRepository->find($item['id'])) {
                    throw new PaymentException('Wrong item id');
                }
                if ($carpoolItem->getCreditorUser()->getId() != $user->getId()) {
                    throw new PaymentException('This user is not the creditor of item #' . $item['id]']);
                }
                
                if ($item["status"] == PaymentItem::DAY_UNPAID) {
                    // Unpaid has been declared
                    $carpoolItem->setUnpaidDate(new \DateTime('now'));
                    
                // Unpaid doesn't change the status
                    //$carpoolItem->setItemStatus(CarpoolItem::CREDITOR_STATUS_UNPAID);
                } elseif ($item["status"] == PaymentItem::DAY_CARPOOLED) {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_REALIZED);
                    if ($item['mode'] == PaymentPayment::MODE_DIRECT) {
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_DIRECT);

                        // Only for DIRECT payment

                        // When the creditor says he has been paid, we also valid the payement for the debtor if he hasn't done it.
                        if ($carpoolItem->getDebtorStatus() == CarpoolItem::DEBTOR_STATUS_PENDING) {
                            $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_DIRECT);
                            
                            // search for an already instanciated carpoolPayment for this User
                            // If it doesn't exist, we create it and push it in the array
                            if (!isset($carpoolPayments[$carpoolItem->getDebtorUser()->getId()])) {
                                $carpoolPayment = new CarpoolPayment();
                                $carpoolPayment->setUser($carpoolItem->getDebtorUser());
                                $carpoolPayment->setAmount(0);
                                $carpoolPayments[$carpoolItem->getDebtorUser()->getId()] = $carpoolPayment;
                            }

                            $carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->setAmount($carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->getAmount()+$carpoolItem->getAmount());
                            $carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->addCarpoolItem($carpoolItem);
                        }
                    } else {
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_ONLINE);
                    }
                } else {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_NOT_REALIZED);
                }
                $this->entityManager->persist($carpoolItem);

                // We need to persist the carpool payements
                foreach ($carpoolPayments as $carpoolPayment) {
                    $this->entityManager->persist($carpoolPayment);
                }
            }
            $this->entityManager->flush();
        }
        
        $payment->setStatus($carpoolPayment->getStatus());
        return $payment;
    }

    /**
     * Update the carpool items after a payment
     *
     * @param CarpoolPayment $carpoolPayment    Involved CarpoolPayment
     * @return void
     */
    public function treatCarpoolPayment(CarpoolPayment $carpoolPayment)
    {
        foreach ($carpoolPayment->getCarpoolItems() as $item) {
            /**
             * @var CarpoolItem $item
             */
            switch ($item->getDebtorStatus()) {
                case CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT:
                    $item->setDebtorStatus(($carpoolPayment->getStatus()==CarpoolPayment::STATUS_SUCCESS) ? CarpoolItem::DEBTOR_STATUS_DIRECT : CarpoolItem::DEBTOR_STATUS_PENDING);
                    break;
                case CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE:
                    $item->setDebtorStatus(($carpoolPayment->getStatus()==CarpoolPayment::STATUS_SUCCESS) ? CarpoolItem::DEBTOR_STATUS_ONLINE : CarpoolItem::DEBTOR_STATUS_PENDING);
                    $item->setCreditorStatus(($carpoolPayment->getStatus()==CarpoolPayment::STATUS_SUCCESS) ? CarpoolItem::CREDITOR_STATUS_ONLINE : CarpoolItem::CREDITOR_STATUS_PENDING);
                    break;
            }
        }
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
        // if no dates are sent, we use the origin of times till "now" ("now" = now less the margin time)
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
    
    /**
     * Create a bank account
     *
     * @param User $user                The user for whom we create a bank account
     * @param BankAccount $bankAccount  The bank account
     * @return BankAccount|null         The bank account created
     */
    public function createBankAccount(User $user, BankAccount $bankAccount)
    {
        // Check if there is a paymentProfile
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$user]);
        if (is_null($paymentProfiles) || count($paymentProfiles)==0) {
            // No Payment Profile, we create one
            $identifier = null;

            // First we register the User on the payment provider to get an identifier
            $identifier = $this->paymentProvider->registerUser($user);

            if ($identifier==null || $identifier=="") {
                throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
            }

            // Now, we create a Wallet for this User
            $wallet = null;
            $wallet = $this->paymentProvider->createWallet($identifier);
            if ($wallet==null || $wallet=="") {
                throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
            }


            $paymentProfile = $this->createPaymentProfile($user, $identifier);
        } else {
            $paymentProfile = $paymentProfiles[0];
        }

        $bankAccount = $this->paymentProvider->addBankAccount($bankAccount);

        // Update the payment profile
        $paymentProfile->setElectronicallyPayable(true);
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $bankAccount;
    }

    /**
     * Disable a bank account
     *
     * @param User $user
     * @param BankAccount $bankAccount
     * @return BankAccount
     */
    public function disableBankAccount(User $user, BankAccount $bankAccount)
    {
        // Check if there is a paymentProfile
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$user]);

        if (is_null($paymentProfiles) || count($paymentProfiles)==0) {
            throw new PaymentException(PaymentException::NO_PAYMENT_PROFILE);
        }

        // We check the ownership of this bankaccount
        $userWithPaymentProfile = $this->userManager->getPaymentProfile();
        if (is_null($userWithPaymentProfile)) {
            throw new PaymentException(PaymentException::NO_PAYMENT_PROFILE);
        }
        $owner = false;
        foreach ($userWithPaymentProfile->getBankAccounts() as $currentBankAccount) {
            if ($currentBankAccount->getId() == $bankAccount->getId()) {
                $owner = true;
            }
        }
        if (!$owner) {
            throw new PaymentException(PaymentException::NOT_THE_OWNER);
        }

        $bankAccount = $this->paymentProvider->disableBankAccount($bankAccount);

        // If there is no more active account, we need to update de PaymentProfile
        $profileBankAccounts = $this->paymentProvider->getPaymentProfileBankAccounts($paymentProfiles[0]);
        if (count($profileBankAccounts)==0) {
            $paymentProfiles[0]->setElectronicallyPayable(false);
            $this->entityManager->persist($paymentProfiles[0]);
            $this->entityManager->flush();
        }

        return $bankAccount;
    }
    
    /**
     * Create a paymentProfile
     *
     * @param User $user                     The User we want to create a profile
     * @param string $identifier             The User identifier on the payment provider service
     * @param bool $electronicallyPayable    If the User can be payed electronically
     * @return PaymentProfile
     */
    public function createPaymentProfile(User $user, string $identifier, bool $electronicallyPayable = false)
    {
        $paymentProfile = new PaymentProfile();
        $paymentProfile->setUser($user);
        $paymentProfile->setProvider($this->provider);
        $paymentProfile->setIdentifier($identifier);
        $paymentProfile->setStatus(1);
        $paymentProfile->setElectronicallyPayable($electronicallyPayable);
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $paymentProfile;
    }

    /**
     * Handle a payment web hook
     * @var object $hook The web hook from the payment provider
     * @return void
     */
    public function handleHook(object $hook)
    {
        if ($this->securityToken !== $hook->getSecurityToken()) {
            throw new PaymentException(PaymentException::INVALID_SECURITY_TOKEN);
        }

        $transaction = $this->paymentProvider->handleHook($hook);

        $carpoolPayment = $this->carpoolPaymentRepository->findOneBy(['transactionId'=>$transaction['transactionId']]);

        if (is_null($carpoolPayment)) {
            throw new PaymentException(PaymentException::CARPOOL_PAYMENT_NOT_FOUND);
        }
        $carpoolPayment->setStatus(($transaction['success']) ? CarpoolPayment::STATUS_SUCCESS : CarpoolPayment::STATUS_FAILURE);

        $this->treatCarpoolPayment($carpoolPayment, $transaction['success']);

        $this->entityManager->persist($carpoolPayment);
        $this->entityManager->flush();
    }
}
