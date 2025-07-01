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

namespace App\Payment\Service;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\AskRepository;
use App\DataProvider\Ressource\Hook;
use App\Geography\Entity\Address;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Entity\PaymentResult;
use App\Payment\Event\ConfirmDirectPaymentEvent;
use App\Payment\Event\ConfirmDirectPaymentRegularEvent;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use App\Payment\Event\IdentityProofAcceptedEvent;
use App\Payment\Event\IdentityProofOutdatedEvent;
use App\Payment\Event\IdentityProofRejectedEvent;
use App\Payment\Event\PayAfterCarpoolEvent;
use App\Payment\Event\PayAfterCarpoolRegularEvent;
use App\Payment\Event\SignalDeptEvent;
use App\Payment\Exception\PaymentException;
use App\Payment\Repository\CarpoolItemRepository;
use App\Payment\Repository\CarpoolPaymentRepository;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Payment\Ressource\PaymentItem;
use App\Payment\Ressource\PaymentPayment;
use App\Payment\Ressource\PaymentPeriod;
use App\Payment\Ressource\PaymentWeek;
use App\Payment\Ressource\ValidationDocument;
use App\User\DataProvider\ConsumptionFeedbackDataProvider;
use App\User\Entity\User;
use App\User\Event\UserHomeAddressUpdateEvent;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Payment manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentManager
{
    public const MIN_WEEK = 1;
    public const MAX_WEEK = 52;
    public const MIN_YEAR = 1970;
    public const MAX_YEAR = 2999;

    public const KYC_DOCUMENT_VALIDATED = 'VALIDATED';
    public const KYC_DOCUMENT_REFUSED = 'REFUSED';
    public const KYC_DOCUMENT_OUTDATED = 'OUT_OF_DATE';

    private $entityManager;
    private $carpoolItemRepository;
    private $askRepository;
    private $provider;
    private $paymentProvider;
    private $paymentProviderUsesWallet;
    private $paymentProfileRepository;
    private $userManager;
    private $paymentActive;
    private $paymentActiveDate;
    private $securityTokenActive;
    private $securityToken;
    private $exportPath;
    private $carpoolPaymentRepository;
    private $validationDocsPath;
    private $validationDocsAuthorizedExtensions;
    private $eventDispatcher;
    private $actionRepository;
    private $consumptionFeedbackProvider;
    private $logger;

    private $_defaultCarpoolTimezone;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface   $entityManager                      The entity manager
     * @param CarpoolItemRepository    $carpoolItemRepository              The carpool items repository
     * @param AskRepository            $askRepository                      The ask repository
     * @param CarpoolItemRepository    $carpoolItemRepository              The carpool items repository
     * @param PaymentDataProvider      $paymentProvider                    The payment data provider
     * @param PaymentProfileRepository $paymentProfileRepository           The payment profile repository
     * @param string                   $paymentActive                      If the online payment is active
     * @param string                   $paymentProviderService             The payment provider service
     * @param string                   $securityToken                      The payment security token (for hooks)
     * @param string                   $validationDocsPath                 Path to the temp directory for validation documents
     * @param array                    $validationDocsAuthorizedExtensions Authorized extensions for validation documents
     * @param array                    $exportPath
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CarpoolItemRepository $carpoolItemRepository,
        CarpoolPaymentRepository $carpoolPaymentRepository,
        AskRepository $askRepository,
        PaymentDataProvider $paymentProvider,
        PaymentProfileRepository $paymentProfileRepository,
        UserManager $userManager,
        LoggerInterface $logger,
        string $paymentActive,
        string $paymentProviderService,
        bool $paymentProviderUsesWallet,
        bool $securityTokenActive,
        string $securityToken,
        string $validationDocsPath,
        array $validationDocsAuthorizedExtensions,
        string $exportPath,
        EventDispatcherInterface $eventDispatcher,
        ActionRepository $actionRepository,
        ConsumptionFeedbackDataProvider $consumptionFeedbackProvider,
        JourneyValidation $journeyValidation,
        string $defaultCarpoolTimezone
    ) {
        $this->entityManager = $entityManager;
        $this->carpoolItemRepository = $carpoolItemRepository;
        $this->carpoolPaymentRepository = $carpoolPaymentRepository;
        $this->askRepository = $askRepository;
        $this->provider = $paymentProviderService;
        $this->paymentProviderUsesWallet = $paymentProviderUsesWallet;
        $this->entityManager = $entityManager;
        $this->paymentProvider = $paymentProvider;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->userManager = $userManager;
        $this->paymentActive = false;
        if ($this->paymentActiveDate = \DateTime::createFromFormat('Y-m-d', $paymentActive)) {
            $this->paymentActiveDate->setTime(0, 0);
            $this->paymentActive = true;
        }
        $this->securityTokenActive = $securityTokenActive;
        $this->securityToken = $securityToken;
        $this->validationDocsPath = $validationDocsPath;
        $this->validationDocsAuthorizedExtensions = $validationDocsAuthorizedExtensions;
        $this->exportPath = $exportPath;
        $this->eventDispatcher = $eventDispatcher;
        $this->actionRepository = $actionRepository;
        $this->logger = $logger;
        $this->consumptionFeedbackProvider = $consumptionFeedbackProvider;
        $this->_journeyValidation = $journeyValidation;
        $this->_defaultCarpoolTimezone = $defaultCarpoolTimezone;
    }

    /**
     * Get the payment items of a user : create the lacking items from the accepted asks, construct the array of Payment Items.
     *
     * @param User        $user      The user concerned
     * @param int         $frequency The frequency for the items (1 = punctual, 2 = regular)
     * @param int         $type      The type of items (1 = to pay, 2 = to collect)
     * @param null|string $day       A day for regular items, from which we extrapolate the week and year, under the form YYYYMMDD
     * @param null|string $week      The week and year for regular items, under the form WWYYYY (ex : 052020 pour for the week 05 of year 2020)
     *
     * @return array The payment items found
     */
    public function getPaymentItems(User $user, int $frequency = 1, int $type = 1, ?string $day = null, ?string $week = null)
    {
        $items = [];

        $fromDate = null;
        $toDate = null;

        $minDate = new \DateTime('2999-01-01');
        $maxDate = new \DateTime('1970-01-01');

        if (Criteria::FREQUENCY_REGULAR == $frequency) {
            if (is_null($day) && is_null($week)) {
                throw new PaymentException(PaymentException::DAY_OR_WEEK_NOT_PROVIDED);
            }
            if (!is_null($day)) {
                $fromDate = \DateTime::createFromFormat('Ymd', $day, new \DateTimeZone($this->_defaultCarpoolTimezone));
                $fromDate->modify('first day of this month');
                $toDate = clone $fromDate;
                $toDate->modify('last day of this month');
            } else {
                $weekNumber = (int) substr($week, 0, 2);
                $weekYear = (int) substr($week, 2);
                if ($weekNumber < self::MIN_WEEK || $weekNumber > self::MAX_WEEK || $weekYear < self::MIN_YEAR || $weekYear > self::MAX_YEAR) {
                    throw new PaymentException(PaymentException::WEEK_WRONG_FORMAT);
                }
                $fromDate = new \DateTime();
                $fromDate->setISODate($weekYear, $weekNumber);
                $toDate = new \DateTime();
                $toDate->setISODate($weekYear, $weekNumber, 7);
            }
            $fromDate->setTime(0, 0, 0);
            $toDate->setTime(23, 59, 59);
        }
        // we create the carpool items in case they don't exist yet
        $this->createCarpoolItems($fromDate, $toDate, $user);

        // we get the carpool items
        $carpoolItems = $this->getCarpoolItems($frequency, $type, $user, $fromDate, $toDate);
        // for regular items, we need to find the outward and return amount, and the days carpooled
        $regularAmounts = [];
        $regularDays = [];
        if (Criteria::FREQUENCY_REGULAR == $frequency) {
            foreach ($carpoolItems as $carpoolItem) {
                /**
                 * @var CarpoolItem $carpoolItem
                 */
                if ((Proposal::TYPE_ONE_WAY == $carpoolItem->getType() || Proposal::TYPE_OUTWARD == $carpoolItem->getType()) && !isset($regularAmounts[$carpoolItem->getAsk()->getId()]['outward'])) {
                    $regularAmounts[$carpoolItem->getAsk()->getId()]['outward'] = $carpoolItem->getAmount();
                } elseif (Proposal::TYPE_RETURN == $carpoolItem->getType() && !isset($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'])) {
                    $regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'] = $carpoolItem->getAmount();
                }
                // we initialize each week day
                if (Proposal::TYPE_RETURN == $carpoolItem->getType() && !isset($regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()])) {
                    $regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['outward'] = [
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                    ];
                    $regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'] = $regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['outward'];
                } elseif (Proposal::TYPE_RETURN != $carpoolItem->getType() && !isset($regularDays[$carpoolItem->getAsk()->getId()])) {
                    $regularDays[$carpoolItem->getAsk()->getId()]['outward'] = [
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                        ['id' => null, 'status' => PaymentItem::DAY_UNAVAILABLE],
                    ];
                    $regularDays[$carpoolItem->getAsk()->getId()]['return'] = $regularDays[$carpoolItem->getAsk()->getId()]['outward'];
                }
                // we set the corresponding day
                if (Proposal::TYPE_RETURN == $carpoolItem->getType()) {
                    $regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'][$carpoolItem->getItemDate()->format('w')]['id'] = $carpoolItem->getId();
                    $regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'][$carpoolItem->getItemDate()->format('w')]['status'] = PaymentItem::DAY_CARPOOLED;
                } else {
                    $regularDays[$carpoolItem->getAsk()->getId()]['outward'][$carpoolItem->getItemDate()->format('w')]['id'] = $carpoolItem->getId();
                    $regularDays[$carpoolItem->getAsk()->getId()]['outward'][$carpoolItem->getItemDate()->format('w')]['status'] = PaymentItem::DAY_CARPOOLED;
                }
                $minDate = min($minDate, $carpoolItem->getItemDate());
                $maxDate = max($maxDate, $carpoolItem->getItemDate());
            }
        }
        // we keep a trace of already treated asks (we return one item for a single ask, even for regular items)
        $treatedAsks = [];
        // then we create each payment item from the carpool items
        foreach ($carpoolItems as $carpoolItem) {
            /**
             * @var CarpoolItem $carpoolItem
             */
            if (Proposal::TYPE_RETURN == $carpoolItem->getType() && in_array($carpoolItem->getAsk()->getAskLinked()->getId(), $treatedAsks)) {
                continue;
            }
            if (Proposal::TYPE_RETURN != $carpoolItem->getType() && in_array($carpoolItem->getAsk()->getId(), $treatedAsks)) {
                continue;
            }
            $paymentItem = new PaymentItem($carpoolItem->getId());
            $paymentItem->setAskId($carpoolItem->getAsk()->getId());
            $paymentItem->setType($type);
            if (PaymentItem::TYPE_PAY == $type) {
                $paymentItem->setGivenName($carpoolItem->getCreditorUser()->getGivenName());
                $paymentItem->setShortFamilyName($carpoolItem->getCreditorUser()->getShortFamilyName());
            } else {
                $paymentItem->setGivenName($carpoolItem->getDebtorUser()->getGivenName());
                $paymentItem->setShortFamilyName($carpoolItem->getDebtorUser()->getShortFamilyName());
            }
            $paymentItem->setFrequency($frequency);
            if (Criteria::FREQUENCY_PUNCTUAL == $paymentItem->getFrequency()) {
                $itemDate = new \DateTime($carpoolItem->getItemDate()->format('Y-m-d H:i:s'), new \DateTimeZone($this->_defaultCarpoolTimezone));
                $paymentItem->setDate($itemDate);
                $paymentItem->setAmount($carpoolItem->getAmount());
            } else {
                $itemFromDate = new \DateTime($fromDate->format('Y-m-d H:i:s'), new \DateTimeZone($this->_defaultCarpoolTimezone));
                $itemToDate = new \DateTime($toDate->format('Y-m-d H:i:s'), new \DateTimeZone($this->_defaultCarpoolTimezone));
                $paymentItem->setFromDate($itemFromDate);
                $paymentItem->setToDate($itemToDate);

                if (Proposal::TYPE_RETURN == $carpoolItem->getType() && isset($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['outward'])) {
                    $paymentItem->setOutwardAmount($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['outward']);
                    $paymentItem->setOutwardDays($regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['outward']);
                } elseif (Proposal::TYPE_RETURN != $carpoolItem->getType() && isset($regularAmounts[$carpoolItem->getAsk()->getId()]['outward'])) {
                    $paymentItem->setOutwardAmount($regularAmounts[$carpoolItem->getAsk()->getId()]['outward']);
                    $paymentItem->setOutwardDays($regularDays[$carpoolItem->getAsk()->getId()]['outward']);
                }

                if (Proposal::TYPE_RETURN == $carpoolItem->getType() && isset($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['return'])) {
                    $paymentItem->setReturnAmount($regularAmounts[$carpoolItem->getAsk()->getAskLinked()->getId()]['return']);
                    $paymentItem->setReturnDays($regularDays[$carpoolItem->getAsk()->getAskLinked()->getId()]['return']);
                } elseif (Proposal::TYPE_RETURN != $carpoolItem->getType() && isset($regularAmounts[$carpoolItem->getAsk()->getId()]['return'])) {
                    $paymentItem->setReturnAmount($regularAmounts[$carpoolItem->getAsk()->getId()]['return']);
                    $paymentItem->setReturnDays($regularDays[$carpoolItem->getAsk()->getId()]['return']);
                }
            }
            // we iterate through the waypoints to get the passenger origin and destination
            $minPos = 9999;
            $maxPos = -1;
            $ask = $carpoolItem->getAsk();
            if (Proposal::TYPE_RETURN == $carpoolItem->getType()) {
                $ask = $carpoolItem->getAsk()->getAskLinked();
            }
            foreach ($ask->getWaypoints() as $waypoint) {
                /**
                 * @var Waypoint $waypoint
                 */
                if (Waypoint::ROLE_PASSENGER == $waypoint->getRole()) {
                    if ($waypoint->getPosition() < $minPos) {
                        $paymentItem->setOrigin($waypoint->getAddress());
                        $minPos = $waypoint->getPosition();
                    } elseif ($waypoint->getPosition() > $maxPos) {
                        $paymentItem->setDestination($waypoint->getAddress());
                        $maxPos = $waypoint->getPosition();
                    }
                }
            }

            // Set if the paymentItem is payable electonically (if the Creditor User has a paymentProfile electronicallyPayable)

            // default value
            $paymentItem->setElectronicallyPayable(false);
            $paymentItem->setCanPayElectronically(false);

            if ($this->paymentActive && '' !== $this->provider) {
                $paymentProfile = $this->paymentProvider->getPaymentProfiles($carpoolItem->getCreditorUser(), false);
                if (is_null($paymentProfile) || 0 == count($paymentProfile)) {
                    $paymentItem->setElectronicallyPayable(false);
                } else {
                    if ($paymentProfile[0]->isElectronicallyPayable()
                        && PaymentProfile::VALIDATION_VALIDATED == $paymentProfile[0]->getValidationStatus()) {
                        $paymentItem->setElectronicallyPayable(true);
                    }
                }

                // Determine if the user can pay electronically
                // Complete address or an already existing user profile validated
                $userPaymentProfiles = $this->paymentProvider->getPaymentProfiles($user, false);
                if (is_null($userPaymentProfiles) || 0 == count($userPaymentProfiles)) {
                    // No payment profile. It means that in case of electronic payment, we will register the user to the provider.
                    $paymentItem->setCanPayElectronically(false);
                    // Check if the User is valid for automatic registration
                    if ($this->checkValidForRegistrationToTheProvider($user)) {
                        $paymentItem->setCanPayElectronically(true);
                    }
                } else {
                    // The user has a payment profile. It means that he already has an account and a wallet to the provider
                    $paymentItem->setCanPayElectronically(true);
                }
            }

            // If there is an Unpaid Date, we set the unpaid date of the PaymentItem
            $paymentItem->setUnpaidDate($carpoolItem->getUnpaidDate());

            $items[] = $paymentItem;
            if (Proposal::TYPE_RETURN == $carpoolItem->getType()) {
                $treatedAsks[] = $carpoolItem->getAsk()->getAskLinked()->getId();
            } else {
                $treatedAsks[] = $carpoolItem->getAsk()->getId();
            }
        }

        // finally we return the array of PaymentItem
        return $items;
        // return [
        //     'items' => $items,
        //     'minDate' => $minDate,
        //     'maxDate' => $maxDate
        // ];
    }

    /**
     * Check missing data to be eligible to electronical payment for a User.
     *
     * @param User $user The User to check
     */
    public function checkMissingDataToPayElectronically(User $user): array
    {
        $missingData = [];
        if (is_null($user->getGivenName()) || '' == $user->getGivenName()) {
            $missingData[] = 'givenName';
        }
        if (is_null($user->getFamilyName()) || '' == $user->getFamilyName()) {
            $missingData[] = 'familyName';
        }
        if (is_null($user->getBirthDate()) || '' == $user->getBirthDate()) {
            $missingData[] = 'birthDate';
        }
        if (is_null($user->getEmail()) || '' == $user->getEmail()) {
            $missingData[] = 'email';
        }

        /** @var Address */
        $address = null;
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $homeAddress = $address;

                break;
            }
        }

        if (!isset($homeAddress) || is_null($homeAddress)) {
            $missingData[] = 'homeAddress';
        } else {
            if (is_null($homeAddress->getAddressLocality()) || '' == $homeAddress->getAddressLocality()) {
                $missingData[] = 'addressLocality';
            }
            if (is_null($homeAddress->getCountryCode()) || '' == $homeAddress->getCountryCode()) {
                $missingData[] = 'countryCode';
            }
            if (is_null($homeAddress->getPostalCode()) || '' == $homeAddress->getPostalCode()) {
                $missingData[] = 'postalCode';
            }

            $street = '';
            if ('' != $homeAddress->getStreetAddress()) {
                $street = $homeAddress->getStreetAddress();
            } else {
                $street = trim($homeAddress->getHouseNumber().' '.$homeAddress->getStreet());
            }

            if ('' == $street) {
                $missingData[] = 'street';
            }
        }

        return $missingData;
    }

    /**
     * Check if the User is valid for a registration to the provider.
     *
     * @param User    $user        The User to check
     * @param Address $homeAddress Ignore the home address of the user, use this address instead
     */
    public function checkValidForRegistrationToTheProvider(User $user, ?Address $homeAddress = null): bool
    {
        // If the user already has a profile, we return true
        $paymentProfiles = $this->paymentProvider->getPaymentProfiles($user, false);
        if (!is_null($paymentProfiles) && count($paymentProfiles) > 0) {
            return true;
        }

        // We check if the user has a complete identify
        if (is_null($user->getGivenName()) || '' == $user->getGivenName()
            || is_null($user->getFamilyName()) || '' == $user->getFamilyName()
            || is_null($user->getBirthDate()) || '' == $user->getBirthDate()
        ) {
            return false;
        }

        if (is_null($homeAddress)) {
            // We check if he has a complete home address otherwise, he can't register automatically
            $address = null;
            foreach ($user->getAddresses() as $address) {
                if ($address->isHome()) {
                    $homeAddress = $address;

                    break;
                }
            }

            if (is_null($homeAddress)) {
                return false;
            }
        }

        // the nationality and country of residence are required
        if ('' == $homeAddress->getCountryCode()) {
            return false;
        }

        return true;
    }

    /**
     * Return the payment periods for which the given user has regular carpools planned.
     *
     * @param User $user The user
     * @param int  $type The type of payments (1 = pay, 2 = collect)
     *
     * @return array The periods found
     */
    public function getPaymentPeriods(User $user, int $type)
    {
        $periods = [];

        // we get the accepted asks of the user
        if (PaymentItem::TYPE_COLLECT == $type) {
            // we want the payments to collect => as a driver
            $asks = $this->askRepository->findAcceptedRegularAsksForUserAsDriver($user);
        } else {
            // we want the payments to pay => as a passenger
            $asks = $this->askRepository->findAcceptedRegularAsksForUserAsPassenger($user);
        }

        // first we get the periods and the days
        foreach ($asks as $ask) {
            $key = null;
            if (Ask::TYPE_RETURN_ROUNDTRIP == $ask->getType()) {
                $key = $ask->getAskLinked()->getId();
            } else {
                $key = $ask->getId();
            }
            if ($key && !isset($periods[$key])) {
                $period = new PaymentPeriod();
                $period->setId($key);
                $period->setFromDate($ask->getCriteria()->getFromDate());
                $period->setToDate($ask->getCriteria()->getToDate());
                $days = [];
                if ($ask->getCriteria()->isMonCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 1;
                }
                if ($ask->getCriteria()->isTueCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 2;
                }
                if ($ask->getCriteria()->isWedCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 3;
                }
                if ($ask->getCriteria()->isThuCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 4;
                }
                if ($ask->getCriteria()->isFriCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 5;
                }
                if ($ask->getCriteria()->isSatCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 6;
                }
                if ($ask->getCriteria()->isSunCheck() || (!is_null($ask->getAskLinked()) && $ask->getAskLinked()->getCriteria()->isMonCheck())) {
                    $days[] = 0;
                }
                $period->setDays($days);
                $periods[$key] = $period;
            }
        }

        return array_values($periods);
    }

    /**
     * Get the first non validated week of a carpool Item.
     *
     * @param User $user The user concerned
     * @param int  $id   The id of the carpool Item to look for
     *
     * @return int The week number found
     */
    public function getFirstNonValidatedWeek(User $user, int $id)
    {
        $week = null;
        $validated = false;
        if (!$carpoolItem = $this->carpoolItemRepository->find($id)) {
            return new \Exception('Wrong carpoolItem id');
        }
        $ask = $carpoolItem->getAsk();
        if ($ask->getUser()->getId() != $user->getId() && $ask->getUserRelated()->getId() != $user->getId()) {
            return new \Exception('Unauthaurized');
        }
        if (count($ask->getCarpoolItems()) > 0) {
            $week = $ask->getCarpoolItems()[0]->getItemDate()->format('WY');
        }
        if (!is_null($week)) {
            foreach ($ask->getCarpoolItems() as $carpoolItem) {
                /**
                 * @var CarpoolItem $carpoolItem
                 */
                if ($carpoolItem->getItemDate()->format('WY') != $week) {
                    // if the week has changed and the previous week is not validated, we found what we searched !
                    if (!$validated) {
                        break;
                    }
                    // else we change the week
                    $week = $carpoolItem->getItemDate()->format('WY');
                    $validated = false;
                }

                // The validated status depends on the point of vue of the current user
                if (CarpoolItem::STATUS_INITIALIZED !== $carpoolItem->getItemStatus()) {
                    if ($carpoolItem->getDebtorUser()->getId() == $user->getId()
                        && CarpoolItem::DEBTOR_STATUS_PENDING !== $carpoolItem->getDebtorStatus()
                    ) {
                        // The day has been confirmed by the debtor, the week is validated for him
                        $validated = true;
                    } elseif ($carpoolItem->getCreditorUser()->getId() == $user->getId()
                        && CarpoolItem::CREDITOR_STATUS_PENDING !== $carpoolItem->getCreditorStatus()
                    ) {
                        // The day has been confirmed by the creditor, the week is validated for him
                        $validated = true;
                    }
                }
            }
        }
        $paymentWeek = new PaymentWeek();
        $paymentWeek->setId($id);
        $paymentWeek->setWeek($week);

        return $paymentWeek;
    }

    /**
     * Create a Payment Payment : a payment for one or many carpool items.
     *
     * @param PaymentPayment $payment The payments to make
     * @param User           $user    The user that makes/receive the payment
     *
     * @return PaymentPayment The resulting payment (with updated statuses)
     */
    public function createPaymentPayment(PaymentPayment $payment, User $user)
    {
        if (PaymentPayment::TYPE_PAY != $payment->getType() && PaymentPayment::TYPE_VALIDATE != $payment->getType()) {
            throw new PaymentException('Wrong payment type');
        }

        // we assume the payment is failed until it's a success !
        // $payment->setStatus(PaymentPayment::STATUS_FAILURE);

        if (PaymentPayment::TYPE_PAY == $payment->getType()) {
            // PAY

            // we create the payment
            $carpoolPayment = new CarpoolPayment();
            $carpoolPayment->setUser($user);
            $carpoolPayment->setStatus(CarpoolPayment::STATUS_INITIATED);
            $carpoolPayment->setOrigin($payment->getOrigin());

            // for a payment, we need to compute the total amount
            $amountDirect = 0;
            $amountOnline = 0;

            // we set this askIds array to know asks affected we needed it to execute events
            $askIds = [];

            foreach ($payment->getItems() as $item) {
                if (!$carpoolItem = $this->carpoolItemRepository->find($item['id'])) {
                    throw new PaymentException('Wrong item id');
                }
                if ($carpoolItem->getDebtorUser()->getId() != $user->getId()) {
                    throw new PaymentException('This user is not the debtor of item #'.$item['id']);
                }
                // if the day is carpooled, we need to pay !
                if (PaymentItem::DAY_CARPOOLED == $item['status']) {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_REALIZED);
                    if (PaymentPayment::MODE_DIRECT == $item['mode']) {
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
                if (PaymentItem::DAY_CARPOOLED == $item['status']) {
                    if (PaymentPayment::MODE_DIRECT == $item['mode']) {
                        $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT);
                    } else {
                        $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE);
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_PENDING_ONLINE);
                    }
                }
                $this->entityManager->persist($carpoolItem);

                // we dispatch the gamification event associated to the carpoolItem
                // we dispatch for the debtor
                $action = $this->actionRepository->findOneBy(['name' => 'carpool_done']);
                $actionEvent = new ActionEvent($action, $carpoolItem->getDebtorUser());
                $actionEvent->setCarpoolItem($carpoolItem);
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
                // we also dispatch for the creditor
                $action = $this->actionRepository->findOneBy(['name' => 'carpool_done']);
                $actionEvent = new ActionEvent($action, $carpoolItem->getCreditorUser());
                $actionEvent->setCarpoolItem($carpoolItem);
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
            }
            $this->entityManager->flush();

            // we send execute event to inform driver that passenger paid by hand
            // case punctual
            if (Criteria::FREQUENCY_PUNCTUAL == $carpoolItem->getAsk()->getCriteria()->getFrequency() && CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT == $carpoolItem->getDebtorStatus()) {
                $event = new ConfirmDirectPaymentEvent($carpoolItem, $user);
                $this->eventDispatcher->dispatch(ConfirmDirectPaymentEvent::NAME, $event);
            // case regular
            } elseif (Criteria::FREQUENCY_REGULAR == $carpoolItem->getAsk()->getCriteria()->getFrequency() && CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT == $carpoolItem->getDebtorStatus()) {
                // We send only one email for the all week
                if (!in_array($carpoolItem->getAsk()->getId(), $askIds)) {
                    $event = new ConfirmDirectPaymentRegularEvent($carpoolItem, $user);
                    $this->eventDispatcher->dispatch(ConfirmDirectPaymentRegularEvent::NAME, $event);
                    // we put in array the ask and the ask linked
                    $askIds[] = $carpoolItem->getAsk()->getId();
                    if ($carpoolItem->getAsk()->getAskLinked()) {
                        $askIds[] = $carpoolItem->getAsk()->getAskLinked()->getId();
                    }
                }
            }

            // if online amount is not zero, we pay online
            if ($amountOnline > 0) {
                $carpoolPayment->setAmountOnline($amountOnline);
                $carpoolPayment = $this->paymentProvider->generateElectronicPaymentUrl($carpoolPayment);
                if (!is_null($carpoolPayment->getCreateCarpoolProfileIdentifier())) {
                    // We need to persits the carpoolProfile
                    $this->createPaymentProfile($user, $carpoolPayment->getCreateCarpoolProfileIdentifier());
                }
                $payment->setRedirectUrl($carpoolPayment->getRedirectUrl());
            } else {
                $carpoolPayment = $this->treatCarpoolPayment($carpoolPayment);
                $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
            }

            $payment->setStatus($carpoolPayment->getStatus());

            $this->entityManager->persist($carpoolPayment);
            $this->entityManager->flush();
        } else {
            // COLLECT
            // We will automatically create the carpoolPayments related to an accepted direct payment not previously validated by the debtor.
            $carpoolPayments = [];

            // we set this askIds array to know asks affected we needed it to execute events
            $askIds = [];

            foreach ($payment->getItems() as $item) {
                if (!$carpoolItem = $this->carpoolItemRepository->find($item['id'])) {
                    throw new PaymentException('Wrong item id');
                }
                if ($carpoolItem->getCreditorUser()->getId() != $user->getId()) {
                    throw new PaymentException('This user is not the creditor of item #'.$item['id']);
                }

                if (PaymentItem::DAY_UNPAID == $item['status']) {
                    // Unpaid has been declared
                    $carpoolItem->setUnpaidDate(new \DateTime('now'));

                // Unpaid doesn't change the status
                // $carpoolItem->setItemStatus(CarpoolItem::CREDITOR_STATUS_UNPAID);
                } elseif (PaymentItem::DAY_CARPOOLED == $item['status']) {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_REALIZED);
                    $carpoolItem->setUnpaidDate(null);
                    if (PaymentPayment::MODE_DIRECT == $item['mode']) {
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_DIRECT);

                        // Only for DIRECT payment

                        // When the creditor says he has been paid, we also valid the payement for the debtor if he hasn't done it.
                        // We might need to create a carpoolPayment if it does'nt exists already

                        // Check if there is a carpoolpayment made by the debtor for this carpool item
                        $currentCarpoolPayment = $this->carpoolPaymentRepository->findCarpoolPaymentByDebtorAndCarpoolItem($carpoolItem->getDebtorUser(), $carpoolItem);

                        if (is_null($currentCarpoolPayment) || 0 == count($currentCarpoolPayment)) {
                            if (CarpoolItem::DEBTOR_STATUS_PENDING == $carpoolItem->getDebtorStatus() || CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT == $carpoolItem->getDebtorStatus()) {
                                $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_DIRECT);

                                // search for an already instanciated carpoolPayment for this User
                                // If it doesn't exist, we create it and push it in the array
                                if (!isset($carpoolPayments[$carpoolItem->getDebtorUser()->getId()])) {
                                    $carpoolPayment = new CarpoolPayment();
                                    $carpoolPayment->setUser($carpoolItem->getDebtorUser());
                                    $carpoolPayment->setAmount(0);
                                    $carpoolPayments[$carpoolItem->getDebtorUser()->getId()] = $carpoolPayment;
                                    $carpoolPayment->setOrigin($payment->getOrigin());
                                }

                                $carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->setAmount($carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->getAmount() + $carpoolItem->getAmount());
                                $carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->addCarpoolItem($carpoolItem);

                                // I don't know why but the first persist put the status to 0 even if i set it before
                                $this->entityManager->persist($carpoolPayments[$carpoolItem->getDebtorUser()->getId()]);
                                $carpoolPayments[$carpoolItem->getDebtorUser()->getId()]->setStatus(CarpoolPayment::STATUS_SUCCESS);
                                $this->entityManager->persist($carpoolPayments[$carpoolItem->getDebtorUser()->getId()]);
                            }
                        } else {
                            $carpoolPayments[$carpoolItem->getDebtorUser()->getId()] = $currentCarpoolPayment[0];
                        }

                        $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_DIRECT);
                    } else {
                        // For online payment we don't change the debtor status, only the creditor's
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_ONLINE);
                    }
                } else {
                    $carpoolItem->setItemStatus(CarpoolItem::STATUS_NOT_REALIZED);
                }
                $this->entityManager->persist($carpoolItem);

                // we dispatch the gamification event associated to the carpoolItem
                // we dispatch for the debtor
                $action = $this->actionRepository->findOneBy(['name' => 'carpool_done']);
                $actionEvent = new ActionEvent($action, $carpoolItem->getDebtorUser());
                $actionEvent->setCarpoolItem($carpoolItem);
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
                // we also dispatch for the creditor
                $action = $this->actionRepository->findOneBy(['name' => 'carpool_done']);
                $actionEvent = new ActionEvent($action, $carpoolItem->getCreditorUser());
                $actionEvent->setCarpoolItem($carpoolItem);
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);

                // Flush also all the carpoolpayments
                $this->entityManager->flush();

                // event to inform passenger that driver alert about an unpaid carpool
                // case punctual
                if (Criteria::FREQUENCY_PUNCTUAL == $carpoolItem->getAsk()->getCriteria()->getFrequency() && $carpoolItem->getUnpaidDate()) {
                    $event = new SignalDeptEvent($carpoolItem, $user);
                    $this->eventDispatcher->dispatch(SignalDeptEvent::NAME, $event);
                // case regular
                } elseif (Criteria::FREQUENCY_REGULAR == $carpoolItem->getAsk()->getCriteria()->getFrequency() && $carpoolItem->getUnpaidDate()) {
                    // We send only one email for the all week
                    if (!in_array($carpoolItem->getAsk()->getId(), $askIds)) {
                        $event = new SignalDeptEvent($carpoolItem, $user);
                        $this->eventDispatcher->dispatch(SignalDeptEvent::NAME, $event);

                        // we put in array the ask and the ask linked
                        $askIds[] = $carpoolItem->getAsk()->getId();
                        if ($carpoolItem->getAsk()->getAskLinked()) {
                            $askIds[] = $carpoolItem->getAsk()->getAskLinked()->getId();
                        }
                    }
                }
            }
        }

        return $payment;
    }

    /**
     * Update the carpool items after a payment.
     *
     * @param CarpoolPayment $carpoolPayment Involved CarpoolPayment
     */
    public function treatCarpoolPayment(CarpoolPayment $carpoolPayment, array $onlineReturns = []): CarpoolPayment
    {
        foreach ($carpoolPayment->getCarpoolItems() as $item) {
            /**
             * @var CarpoolItem $item
             */
            switch ($item->getDebtorStatus()) {
                case CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT:
                    $item->setDebtorStatus((CarpoolPayment::STATUS_SUCCESS == $carpoolPayment->getStatus()) ? CarpoolItem::DEBTOR_STATUS_DIRECT : CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT);

                    break;

                case CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE:
                case CarpoolItem::DEBTOR_STATUS_ONLINE:
                    $updated = false;
                    if (count($onlineReturns) > 0) {
                        $updated = $this->_treatOnlineCarpoolPayment($carpoolPayment->getStatus(), $item, $onlineReturns);
                    }
                    // if (!$updated) {
                    //     $item->setDebtorStatus((CarpoolPayment::STATUS_SUCCESS == $carpoolPayment->getStatus()) ? CarpoolItem::DEBTOR_STATUS_ONLINE : CarpoolItem::DEBTOR_STATUS_PENDING);
                    //     $item->setCreditorStatus((CarpoolPayment::STATUS_SUCCESS == $carpoolPayment->getStatus()) ? CarpoolItem::CREDITOR_STATUS_ONLINE : CarpoolItem::CREDITOR_STATUS_PENDING);
                    // }

                    break;
            }
        }

        return $carpoolPayment;
    }

    /**
     * Create the carpool payment items from the accepted asks.
     *
     * @param null|\DateTime $fromDate The start of the period for which we want to create the items
     * @param null|\DateTime $toDate   The end of the period  for which we want to create the items
     * @param null|User      $user     The user concerned (if no user is provided we generate the items for everyone)
     */
    public function createCarpoolItems(?\DateTime $fromDate = null, ?\DateTime $toDate = null, ?User $user = null)
    {
        // if no dates are sent, we use the origin of times till "now" ("now" = now less the margin time)
        if (is_null($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate->modify('-3 month');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new \DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        // first we search the accepted asks for the given period and the given user
        $asks = $this->askRepository->findAcceptedAsksForPeriod($fromDate, $toDate, $user);

        // we initiate empty array of askIds
        $askIds = [];

        if (count($asks) > 0 && $this->consumptionFeedbackProvider->isActive()) {
            try {
                $this->consumptionFeedbackProvider->auth();
            } catch (\Exception $e) {
                $this->logger->error('ConsumptionFeedback authentication failed: '.$e->getMessage());
            }
        }

        if ($this->consumptionFeedbackProvider->isActive() && !is_null($this->consumptionFeedbackProvider->getAccessToken())) {
            $this->resendConsumptionFeedbackInError();
        }
        // then we create the corresponding items
        foreach ($asks as $ask) {
            /**
             * @var Ask $ask
             */
            if (Criteria::FREQUENCY_PUNCTUAL == $ask->getCriteria()->getFrequency()) {
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
                    // we check if the payment is active for the carpool date
                    if ($carpoolItem->getItemDate() < $this->paymentActiveDate || !$this->paymentActive) {
                        $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_NULL);
                        $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_NULL);
                    }
                    $waypoints = $carpoolItem->getAsk()->getMatching()->getProposalRequest()->getWaypoints();
                    $carpoolItem->setPickUp($waypoints[0]->getAddress()->getAddressLocality());
                    foreach ($waypoints as $waypoint) {
                        if ($waypoint->isDestination()) {
                            $carpoolItem->setDropOff($waypoint->getAddress()->getAddressLocality());
                        }
                    }
                    $carpoolItem->setDistance(!is_null($carpoolItem->getAsk()) ? round($carpoolItem->getAsk()->getMatching()->getCommonDistance() / 1000) : null);
                    $this->entityManager->persist($carpoolItem);

                    if ($this->consumptionFeedbackProvider->isActive() && !is_null($this->consumptionFeedbackProvider->getAccessToken())) {
                        $this->consumptionFeedbackProvider->setConsumptionCarpoolItem($carpoolItem);
                        $this->consumptionFeedbackProvider->sendConsumptionFeedback();
                        $carpoolItem = $this->consumptionFeedbackProvider->getConsumptionCarpoolItem();
                        $this->entityManager->persist($carpoolItem);
                        $this->consumptionFeedbackProvider->setConsumptionUser(null);
                        $this->consumptionFeedbackProvider->setConsumptionCarpoolItem(null);
                    }

                    if (CarpoolItem::DEBTOR_STATUS_NULL !== $carpoolItem->getDebtorStatus()) {
                        // we execute event to inform passenger to pay for the carpool only if the debtor status is not null
                        $event = new PayAfterCarpoolEvent($carpoolItem, $carpoolItem->getDebtorUser());
                        $this->eventDispatcher->dispatch(PayAfterCarpoolEvent::NAME, $event);
                    }
                }
            } else {
                // regular, we need to create a carpool item for each day between fromDate (or the ask fromDate if it's after the given fromDate) and toDate
                $curDate = clone max($fromDate, $ask->getCriteria()->getFromDate());
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
                        // we check if the payment is active for the carpool date
                        if ($carpoolItem->getItemDate() < $this->paymentActiveDate || !$this->paymentActive) {
                            $carpoolItem->setDebtorStatus(CarpoolItem::DEBTOR_STATUS_NULL);
                            $carpoolItem->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_NULL);
                        }
                        $waypoints = $carpoolItem->getAsk()->getMatching()->getProposalRequest()->getWaypoints();
                        $carpoolItem->setPickUp($waypoints[0]->getAddress()->getAddressLocality());
                        foreach ($waypoints as $waypoint) {
                            if ($waypoint->isDestination()) {
                                $carpoolItem->setDropOff($waypoint->getAddress()->getAddressLocality());
                            }
                        }
                        $carpoolItem->setDistance(!is_null($carpoolItem->getAsk()) ? round($carpoolItem->getAsk()->getMatching()->getCommonDistance() / 1000) : null);
                        $this->entityManager->persist($carpoolItem);

                        if ($this->consumptionFeedbackProvider->isActive() && !is_null($this->consumptionFeedbackProvider->getAccessToken())) {
                            $this->consumptionFeedbackProvider->setConsumptionCarpoolItem($carpoolItem);
                            $this->consumptionFeedbackProvider->sendConsumptionFeedback();
                            $carpoolItem = $this->consumptionFeedbackProvider->getConsumptionCarpoolItem();
                            $this->entityManager->persist($carpoolItem);
                            $this->consumptionFeedbackProvider->setConsumptionUser(null);
                            $this->consumptionFeedbackProvider->setConsumptionCarpoolItem(null);
                        }

                        // We send only one email for the all week
                        // We check in array if we already send an email for the ask
                        if (!in_array($carpoolItem->getAsk()->getId(), $askIds)) {
                            if (CarpoolItem::DEBTOR_STATUS_NULL !== $carpoolItem->getDebtorStatus()) {
                                $event = new PayAfterCarpoolRegularEvent($carpoolItem, $carpoolItem->getDebtorUser());
                                $this->eventDispatcher->dispatch(PayAfterCarpoolRegularEvent::NAME, $event);
                            }
                            // we put in array the askId and the askid linked
                            $askIds[] = $carpoolItem->getAsk()->getId();
                            if ($carpoolItem->getAsk()->getAskLinked()) {
                                $askIds[] = $carpoolItem->getAsk()->getAskLinked()->getId();
                            }
                        }
                    }

                    if ($curDate->format('Y-m-d') == $toDate->format('Y-m-d') || $curDate->format('Y-m-d') == $ask->getCriteria()->getToDate()->format('Y-m-d')) {
                        // we reached the end of the period
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
     * Get the carpool payment items for the given frequency, type, period and user.
     *
     * @param int            $frequency The frequency for the items
     * @param int            $type      The type of items (1 = to pay, 2 = to collect)
     * @param User           $user      The user concerned
     * @param null|\DateTime $fromDate  The start of the period for which we want to get the items
     * @param null|\DateTime $toDate    The end of the period  for which we want to get the items
     *
     * @return array The items found
     */
    public function getCarpoolItems(int $frequency, int $type, User $user, ?\DateTime $fromDate = null, ?\DateTime $toDate = null)
    {
        // if no dates are sent, we use the origin of times till the previous day
        if (is_null($fromDate)) {
            $fromDate = new \DateTime('1970-01-01');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new \DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        return $this->carpoolItemRepository->findForPayments($frequency, $type, $user, $fromDate, $toDate);
    }

    /**
     * Create a bank account.
     *
     * @param User        $user        The user for whom we create a bank account
     * @param BankAccount $bankAccount The bank account
     *
     * @return null|BankAccount The bank account created
     */
    public function createBankAccount(User $user, BankAccount $bankAccount)
    {
        // Check if there is a paymentProfile
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $user, 'provider' => $this->paymentProvider->getPaymentProviderName()]);

        // We update the home Address by the bank account address
        if (!is_null($bankAccount->getAddress())) {
            if (!is_null($user->getHomeAddress())) {
                $currentHomeAddress = $user->getHomeAddress();
                $currentHomeAddress->setHome(false);
                $this->entityManager->persist($currentHomeAddress);
            }

            $homeAddress = $bankAccount->getAddress();
            $homeAddress->setId(null);
            $homeAddress->setHome(true);
            $homeAddress->setUser($user);

            $this->entityManager->persist($homeAddress);

            $event = new UserHomeAddressUpdateEvent($user);
            $this->eventDispatcher->dispatch(UserHomeAddressUpdateEvent::NAME, $event);
        }

        $identifier = null;
        if (!$this->hasPaymentProfileForCurrentProvider($user)) {
            // No Payment Profile, we create one

            // RPC we check the user have already a bank account with anotherEmail
            $this->checkExistingPaymentProfile($user);

            // First we register the User on the payment provider to get an identifier
            try {
                $identifier = $this->paymentProvider->registerUser($user, $bankAccount->getAddress());
            } catch (PaymentException $e) {
                throw new PaymentException(PaymentException::REGISTER_USER_FAILED.' - '.$e->getMessage());
            }

            if (null == $identifier || '' == $identifier) {
                throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
            }

            // Now, we create a Wallet for this User
            if ($this->paymentProviderUsesWallet) {
                $wallet = null;
                $wallet = $this->paymentProvider->createWallet($identifier);
                if (null == $wallet || '' == $wallet) {
                    throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
                }
            }

            $paymentProfile = $this->createPaymentProfile($user, $identifier);
            // we set it by default at false since the identity is not confirmed yet
            $paymentProfile->setElectronicallyPayable(false);
        } else {
            $paymentProfile = $paymentProfiles[0];
            $identifier = $paymentProfile->getIdentifier();
            if (PaymentProfile::VALIDATION_VALIDATED === $paymentProfile->getValidationStatus()) {
                $paymentProfile->setElectronicallyPayable(true);
            }
        }

        $bankAccount = $this->paymentProvider->addBankAccount($bankAccount, $identifier);

        // Update the payment profile
        $paymentProfile->setStatus(PaymentProfile::STATUS_ACTIVE);
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $bankAccount;
    }

    /**
     * Disable a bank account.
     *
     * @return BankAccount
     */
    public function disableBankAccount(User $user, BankAccount $bankAccount)
    {
        // Check if there is a paymentProfile
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $user, 'provider' => $this->paymentProvider->getPaymentProviderName()]);

        if (is_null($paymentProfiles) || 0 == count($paymentProfiles)) {
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

        $bankAccount->setUserIdentifier($userWithPaymentProfile->getPaymentProfileIdentifier());
        $bankAccount = $this->paymentProvider->disableBankAccount($bankAccount);

        // If there is no more active account, we need to update de PaymentProfile
        $profileBankAccounts = $this->paymentProvider->getPaymentProfileBankAccounts($paymentProfiles[0]);
        if (0 == count($profileBankAccounts)) {
            $paymentProfiles[0]->setElectronicallyPayable(false);
            $paymentProfiles[0]->setStatus(PaymentProfile::STATUS_INACTIVE);
            $this->entityManager->persist($paymentProfiles[0]);
            $this->entityManager->flush();
        }

        return $bankAccount;
    }

    /**
     * Create a paymentProfile.
     *
     * @param User   $user                  The User we want to create a profile
     * @param string $identifier            The User identifier on the payment provider service
     * @param bool   $electronicallyPayable If the User can be payed electronically
     *
     * @return PaymentProfile
     */
    public function createPaymentProfile(User $user, string $identifier, bool $electronicallyPayable = false)
    {
        $paymentProfile = new PaymentProfile();
        $paymentProfile->setUser($user);
        $paymentProfile->setProvider($this->provider);
        $paymentProfile->setIdentifier($identifier);
        $paymentProfile->setStatus(PaymentProfile::STATUS_INACTIVE);
        $paymentProfile->setElectronicallyPayable($electronicallyPayable);
        $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_PENDING);
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $paymentProfile;
    }

    public function updatePaymentProfileStatus(array $paymentProfile)
    {
        $user = $this->paymentProvider->getUser($paymentProfile['identifier']);

        $kycDocument = $this->paymentProvider->getIdentityValidationStatus($user);

        $this->updatePaymentProfile($paymentProfile['id'], $kycDocument);
    }

    public function updatePaymentProfile(int $id, $kycDocument)
    {
        $paymentProfile = $this->paymentProfileRepository->find($id);

        switch ($kycDocument['Status']) {
            case self::KYC_DOCUMENT_OUTDATED:
                $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_OUTDATED);
                $paymentProfile->setValidationId($kycDocument['Id']);
                $paymentProfile->setElectronicallyPayable(false);
                $paymentProfile->setValidationOutdatedDate(new \DateTime());

                break;

            case self::KYC_DOCUMENT_REFUSED:
                $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_REJECTED);
                $paymentProfile->setValidationId($kycDocument['Id']);
                $paymentProfile->setElectronicallyPayable(false);

                switch ($kycDocument['RefusedReasonType']) {
                    case PaymentProfile::OUT_OF_DATE:
                        $paymentProfile->setRefusalReason(PaymentProfile::OUT_OF_DATE);

                        break;

                    case PaymentProfile::UNDERAGE_PERSON:
                        $paymentProfile->setRefusalReason(PaymentProfile::UNDERAGE_PERSON);

                        break;

                    case PaymentProfile::DOCUMENT_FALSIFIED:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_FALSIFIED);

                        break;

                    case PaymentProfile::DOCUMENT_MISSING:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_MISSING);

                        break;

                    case PaymentProfile::DOCUMENT_HAS_EXPIRED:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_HAS_EXPIRED);

                        break;

                    case PaymentProfile::DOCUMENT_NOT_ACCEPTED:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_NOT_ACCEPTED);

                        break;

                    case PaymentProfile::DOCUMENT_DO_NOT_MATCH_USER_DATA:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_DO_NOT_MATCH_USER_DATA);

                        break;

                    case PaymentProfile::DOCUMENT_UNREADABLE:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_UNREADABLE);

                        break;

                    case PaymentProfile::DOCUMENT_INCOMPLETE:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_INCOMPLETE);

                        break;

                    case PaymentProfile::SPECIFIC_CASE:
                        $paymentProfile->setRefusalReason(PaymentProfile::SPECIFIC_CASE);

                        break;

                    case PaymentProfile::DOCUMENT_MISSING_FRONT:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_MISSING_FRONT);

                        break;

                    case PaymentProfile::DOCUMENT_MISSING_BACK:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_MISSING_BACK);

                        break;

                    case PaymentProfile::DOCUMENT_FAILED_OTHER_CASE:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_FAILED_OTHER_CASE);

                        break;

                    case PaymentProfile::DOCUMENT_FAILED_COPY:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_FAILED_COPY);

                        break;

                    case PaymentProfile::DOCUMENT_DOB_MISMATCH:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_DOB_MISMATCH);

                        break;

                    default:
                        $paymentProfile->setRefusalReason(PaymentProfile::DOCUMENT_FAILED_OTHER_CASE);

                        break;
                }

                break;

            case self::KYC_DOCUMENT_VALIDATED:
                $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_VALIDATED);
                $paymentProfile->setValidationId($kycDocument['Id']);
                $paymentProfile->setElectronicallyPayable(true);
                $paymentProfile->setValidatedDate(new \DateTime());
                $paymentProfile->setRefusalReason(null);

                break;
        }
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();
    }

    /**
     * Handle a payin web hook.
     *
     * @var Hook The web hook from the payment provider
     */
    public function handleHookPayIn(Hook $hook)
    {
        if ($this->securityTokenActive && $this->securityToken !== $hook->getSecurityToken()) {
            throw new PaymentException(PaymentException::INVALID_SECURITY_TOKEN);
        }
        $hook = $this->paymentProvider->handleHook($hook);

        $carpoolPayment = $this->carpoolPaymentRepository->findOneBy(['transactionId' => $hook->getRessourceId()]);

        if (is_null($carpoolPayment)) {
            throw new PaymentException(PaymentException::CARPOOL_PAYMENT_NOT_FOUND);
        }

        switch ($hook->getStatus()) {
            case Hook::STATUS_SUCCESS:
                $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);

                break;

            case Hook::STATUS_FAILED:
                $carpoolPayment->setStatus(CarpoolPayment::STATUS_FAILURE);

                break;
        }

        $this->entityManager->persist($carpoolPayment);
        $this->entityManager->flush();
    }

    public function tryToFullfillPendingCarpoolPayments()
    {
        if (!$this->paymentActive || '' == $this->provider) {
            return;
        }

        $initiatedCarpoolPayments = $this->carpoolPaymentRepository->findPendingCarpoolPayments();
        foreach ($initiatedCarpoolPayments as $carpoolPayment) {
            $this->_fullfillCarpoolPayment($carpoolPayment);
        }
    }

    /**
     * Handle a validation web hook.
     *
     * @param Hook $hook The hook to handle
     */
    public function handleHookValidation(Hook $hook)
    {
        if ($this->securityTokenActive && $this->securityToken !== $hook->getSecurityToken()) {
            throw new PaymentException(PaymentException::INVALID_SECURITY_TOKEN);
        }

        $paymentProfile = $this->paymentProfileRepository->findOneBy(['validationId' => $hook->getRessourceId()]);
        if (is_null($paymentProfile)) {
            $this->logger->info($hook->getRessourceId());

            throw new PaymentException(PaymentException::NO_PAYMENT_PROFILE);
        }

        $hook = $this->paymentProvider->handleHook($hook);

        switch ($hook->getStatus()) {
            case Hook::STATUS_SUCCESS:
                if (PaymentProfile::VALIDATION_VALIDATED == $paymentProfile->getValidationStatus() && !is_null($paymentProfile->getValidatedDate())) {
                    $this->logger->info('Payment profile validation success but return');

                    return;
                }

                $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_VALIDATED);
                $paymentProfile->setElectronicallyPayable(true);
                $paymentProfile->setValidatedDate(new \DateTime());
                $paymentProfile->setValidationOutdatedDate(null);
                // we dispatch the event
                $event = new IdentityProofAcceptedEvent($paymentProfile);
                $this->eventDispatcher->dispatch(IdentityProofAcceptedEvent::NAME, $event);
                //  we dispatch the gamification event associated
                $action = $this->actionRepository->findOneBy(['name' => 'identity_proof_accepted']);
                $actionEvent = new ActionEvent($action, $event->getPaymentProfile()->getUser());
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);

                break;

            case Hook::STATUS_FAILED:
                $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_REJECTED);
                $paymentProfile->setElectronicallyPayable(false);
                $paymentProfile = $this->getRefusalReason($paymentProfile, $hook->getContextualStatus());

                // we dispatch the event
                $event = new IdentityProofRejectedEvent($paymentProfile);
                $this->eventDispatcher->dispatch(IdentityProofRejectedEvent::NAME, $event);

                break;

            case Hook::STATUS_OUTDATED_RESSOURCE:
                $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_OUTDATED);
                $paymentProfile->setElectronicallyPayable(false);
                $paymentProfile->setValidationOutdatedDate(new \DateTime());
                // We reinit the dates
                $paymentProfile->setValidationAskedDate(null);
                $paymentProfile->setValidatedDate(null);
                // we dispatch the event
                $event = new IdentityProofOutdatedEvent($paymentProfile);
                $this->eventDispatcher->dispatch(IdentityProofOutdatedEvent::NAME, $event);

                break;
        }

        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();
    }

    /**
     * Upload an identity validation document to the payment provider
     * The document is not stored on the platform. It has to be deleted.
     */
    public function uploadValidationDocument(ValidationDocument $validationDocument): ValidationDocument
    {
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $validationDocument->getUser(), 'provider' => $this->paymentProvider->getPaymentProviderName()]);

        $this->_checkMandatoryValidationDocument($validationDocument, $paymentProfiles);

        if (!is_null($validationDocument->getFile2())) {
            $this->_checkOptionalValidationDocument($validationDocument);
        }

        $validationDocument = $this->paymentProvider->uploadValidationDocument($validationDocument);

        // We don't store this kind of documents. We remove it.
        unlink($this->validationDocsPath.''.$validationDocument->getFileName());

        // We set the date of the validation asked
        $paymentProfile = $paymentProfiles[0];
        $paymentProfile->setValidationId($validationDocument->getIdentifier());
        $paymentProfile->setValidationAskedDate(new \DateTime());
        $paymentProfile->setValidationStatus(0);
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $validationDocument;
    }

    /**
     * Build a PaymentPayment from a CarpoolPayment.
     *
     * @param int $carpoolPaymentId The carpoolPayment
     */
    public function buildPaymentPaymentFromCarpoolPayment(int $carpoolPaymentId): ?PaymentPayment
    {
        $carpoolPayment = $this->carpoolPaymentRepository->find($carpoolPaymentId);
        if (is_null($carpoolPayment)) {
            throw new PaymentException(PaymentException::CARPOOL_PAYMENT_NOT_FOUND);
        }
        $paymentPayment = new PaymentPayment();
        $paymentPayment->setId($carpoolPayment->getId());
        $paymentPayment->setStatus($carpoolPayment->getStatus());

        return $paymentPayment;
    }

    // PAYMENT EXPORT

    /**
     * Export online payment to xml files.
     *
     * @param null|\DateTime $fromDate The start date for the export
     * @param null|\DateTime $toDate   The end date for the export
     */
    public function exportPayments(?\DateTime $fromDate = null, ?\DateTime $toDate = null)
    {
        // if no dates are sent, we use the previous day
        if (is_null($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate->modify('-1 day');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new \DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        // first we search the successful carpool payments for the given period
        $carpoolPayments = $this->carpoolPaymentRepository->findSuccessfulElectronicPaymentsForPeriod($fromDate, $toDate);

        // then we create the xml string for each payment
        $payments = [];
        foreach ($carpoolPayments as $carpoolPayment) {
            foreach ($carpoolPayment->getCarpoolItems() as $carpoolItem) {
                /**
                 * @var CarpoolItem $carpoolItem
                 */
                $string = "\t<payment>\n";
                $string .= "\t\t<paymentDate>".$carpoolPayment->getTransactionDate()->format('Y-m-d\TH:i:s')."</paymentDate>\n";
                $string .= "\t\t<amount>".$carpoolItem->getAmount()."</amount>\n";
                $string .= "\t\t<debtor>\n";
                $string .= "\t\t\t<givenName>".$carpoolItem->getDebtorUser()->getGivenName()."</givenName>\n";
                $string .= "\t\t\t<familyName>".$carpoolItem->getDebtorUser()->getFamilyName()."</familyName>\n";
                $string .= "\t\t\t<email>".$carpoolItem->getDebtorUser()->getEmail()."</email>\n";
                $string .= "\t\t</debtor>\n";
                $string .= "\t\t<creditor>\n";
                $string .= "\t\t\t<givenName>".$carpoolItem->getCreditorUser()->getGivenName()."</givenName>\n";
                $string .= "\t\t\t<familyName>".$carpoolItem->getCreditorUser()->getFamilyName()."</familyName>\n";
                $string .= "\t\t\t<email>".$carpoolItem->getCreditorUser()->getEmail()."</email>\n";
                $string .= "\t\t</creditor>\n";
                $string .= "\t\t<provider>".$this->provider."</provider>\n";
                $string .= "\t\t<postData>".$carpoolPayment->getTransactionPostData()."</postData>\n";
                $string .= "\t</payment>\n";
                $payments[$carpoolPayment->getTransactionDate()->format('Ymd')][] = $string;
            }
        }

        // finally we create a file for each day
        foreach ($payments as $date => $items) {
            $file = $this->exportPath.'payments_'.$date.'.xml';
            $fileContent = "<?xml version=\"1.0\"?>\n";
            $fileContent .= "<payments>\n";
            foreach ($items as $item) {
                $fileContent .= $item;
            }
            $fileContent .= '</payments>';
            $fpr = fopen($file, 'w');
            fwrite($fpr, $fileContent);
            fclose($fpr);
        }
    }

    /**
     * Get the reason why the document is refused.
     *
     * @return PaymentProfile
     */
    public function getRefusalReason(PaymentProfile $paymentProfile, string $contextualStatus = '')
    {
        $validationDocument = $this->paymentProvider->getDocument($paymentProfile->getValidationId(), $contextualStatus);
        $paymentProfile->setRefusalReason($validationDocument->getStatus());

        return $paymentProfile;
    }

    public function hasPaymentProfileForCurrentProvider(User $user): bool
    {
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $user, 'provider' => $this->paymentProvider->getPaymentProviderName()]);

        if (!is_null($paymentProfiles) && 0 < count($paymentProfiles)) {
            return true;
        }

        return false;
    }

    private function _checkMandatoryValidationDocument(ValidationDocument $validationDocument, array $paymentProfiles)
    {
        if (!file_exists($this->validationDocsPath.''.$validationDocument->getFileName())) {
            throw new PaymentException(PaymentException::ERROR_UPLOAD);
        }
        if (!in_array(strtolower($validationDocument->getExtension()), $this->validationDocsAuthorizedExtensions)) {
            throw new PaymentException(PaymentException::ERROR_VALIDATION_DOC_BAD_EXTENTION.' ('.implode(',', $this->validationDocsAuthorizedExtensions).')');
        }

        if (is_null($paymentProfiles) || 0 == count($paymentProfiles)) {
            throw new PaymentException(PaymentException::CARPOOL_PAYMENT_NOT_FOUND);
        }
        if (null !== $paymentProfiles[0]->getValidationId() && (PaymentProfile::VALIDATION_PENDING === $paymentProfiles[0]->getValidationStatus() || PaymentProfile::VALIDATION_VALIDATED === $paymentProfiles[0]->getValidationStatus())) {
            throw new PaymentException(PaymentException::ERROR_VALIDATION_DOC_ALREADY_UNDER_REVIEW);
        }
    }

    private function _checkOptionalValidationDocument(ValidationDocument $validationDocument)
    {
        if (!file_exists($this->validationDocsPath.''.$validationDocument->getFileName2())) {
            throw new PaymentException(PaymentException::ERROR_UPLOAD_OPTIONAL);
        }
        if (!in_array(strtolower($validationDocument->getExtension2()), $this->validationDocsAuthorizedExtensions)) {
            throw new PaymentException(PaymentException::ERROR_VALIDATION_OPTIONAL_DOC_BAD_EXTENTION.' ('.implode(',', $this->validationDocsAuthorizedExtensions).')');
        }
    }

    private function _treatOnlineCarpoolPayment(int $carpoolPaymentStatus, CarpoolItem $item, array $onlineReturns): bool
    {
        foreach ($onlineReturns as $onlineReturn) {
            /**
             * @var PaymentResult $onlineReturn
             */
            if (PaymentResult::RESULT_ONLINE_PAYMENT_STATUS_SUCCESS == $onlineReturn->getStatus() && $onlineReturn->getCarpoolItemId() == $item->getId()) {
                if (PaymentResult::RESULT_ONLINE_PAYMENT_TYPE_TRANSFER == $onlineReturn->getType()) {
                    $item->setDebtorStatus((CarpoolPayment::STATUS_SUCCESS == $carpoolPaymentStatus) ? CarpoolItem::DEBTOR_STATUS_ONLINE : CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE);
                    $item->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_PENDING_ONLINE);
                    $this->entityManager->persist($item);
                }
                if (PaymentResult::RESULT_ONLINE_PAYMENT_TYPE_PAYOUT == $onlineReturn->getType()) {
                    $item->setDebtorStatus((CarpoolPayment::STATUS_SUCCESS == $carpoolPaymentStatus) ? CarpoolItem::DEBTOR_STATUS_ONLINE : CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE);
                    $item->setCreditorStatus(CarpoolItem::CREDITOR_STATUS_ONLINE);
                    $this->entityManager->persist($item);
                }
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }

    private function _fullfillCarpoolPayment(CarpoolPayment $carpoolPayment)
    {
        // Get the creditors
        if (is_null($carpoolPayment->getCarpoolItems()) || 0 == count($carpoolPayment->getCarpoolItems())) {
            throw new PaymentException(PaymentException::NO_CARPOOL_ITEMS);
        }
        $creditors = [];
        foreach ($carpoolPayment->getCarpoolItems() as $carpoolItem) {
            /**
             * @var CarpoolItem $carpoolItem
             */
            if (CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE !== $carpoolItem->getDebtorStatus() && CarpoolItem::DEBTOR_STATUS_ONLINE !== $carpoolItem->getDebtorStatus()) {
                continue;
            }

            if (!isset($creditors[$carpoolItem->getCreditorUser()->getId()])) {
                // New creditor. We set the amount and the payment profile
                $creditors[$carpoolItem->getCreditorUser()->getId()] = [
                    'user' => $this->userManager->getPaymentProfile($carpoolItem->getCreditorUser()),
                    'amount' => $carpoolItem->getAmount(),
                    'carpoolItemId' => $carpoolItem->getId(),
                    'creditorStatus' => $carpoolItem->getCreditorStatus(),
                    'debtorStatus' => $carpoolItem->getDebtorStatus(),
                ];
            } else {
                // We already know this creditor, we add the current amount to the global amount
                $creditors[$carpoolItem->getCreditorUser()->getId()]['amount'] += $carpoolItem->getAmount();
            }
        }
        $debtor = $this->userManager->getPaymentProfile($carpoolPayment->getUser());

        $return = $this->paymentProvider->processAsyncElectronicPayment($debtor, $creditors, $carpoolPayment->getId());
        $this->treatCarpoolPayment($carpoolPayment, $return);

        $this->entityManager->persist($carpoolPayment);
        $this->entityManager->flush();

        if (CarpoolPayment::STATUS_SUCCESS == $carpoolPayment->getStatus()) {
            $event = new ElectronicPaymentValidatedEvent($carpoolPayment);
            $this->eventDispatcher->dispatch($event, ElectronicPaymentValidatedEvent::NAME);

            //  we dispatch the gamification event associated
            $action = $this->actionRepository->findOneBy(['name' => 'electronic_payment_made']);
            $actionEvent = new ActionEvent($action, $carpoolPayment->getUser());
            $actionEvent->setCarpoolPayment($carpoolPayment);
            $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
        }
    }

    /**
     * Resend the previous consumption feedback that failed previouly.
     */
    private function resendConsumptionFeedbackInError()
    {
        $carpoolItems = $this->carpoolItemRepository->findConsumptionFeedbackInError();

        foreach ($carpoolItems as $carpoolItem) {
            $this->consumptionFeedbackProvider->setConsumptionCarpoolItem($carpoolItem);
            $this->consumptionFeedbackProvider->sendConsumptionFeedback();
            $carpoolItem = $this->consumptionFeedbackProvider->getConsumptionCarpoolItem();
            $this->consumptionFeedbackProvider->setConsumptionUser(null);
            $this->consumptionFeedbackProvider->setConsumptionCarpoolItem(null);
        }
    }

    private function checkExistingPaymentProfile(User $user)
    {
        $paymentProfiles = $this->paymentProfileRepository->findPaymentProfileByUserInfos($user, $this->paymentProvider->getPaymentProviderName());

        if (!is_null($paymentProfiles) && 0 < count($paymentProfiles)) {
            throw new PaymentException(PaymentException::USER_ALREADY_HAVE_BANK_ACCOUNT);
        }
    }
}
