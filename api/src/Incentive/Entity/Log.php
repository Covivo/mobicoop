<?php

namespace App\Incentive\Entity;

use App\User\Entity\User;
use Psr\Log\LoggerInterface;

class Log
{
    public const CARPOOL_PROOF_ID = 'carpoolProofId';                   // CarpoolProof entity ID
    public const DATETIME = 'datetime';                                 // The log timestamp
    public const IS_CARPOOL_PROOFS_VALID = 'isCarpoolProofsValid';      // The journeys of the user, already made, correspond to the standard defined within the framework of the CEE
    public const IS_DATE_AFTER_REFERENCE_DATE = 'isDateAfterReference'; // Is the date after the référene date
    public const IS_DATE_IN_PERIOD = 'isDateInPeriod';                  // Is the date in the périod
    public const IS_FROM_FRANCE = 'isFromFrance';                       // Does the journey have an origin and/or a destination from France
    public const IS_LONG_DISTANCE = 'isLongDistance';                   // Is the trip long distance
    public const IS_SHORT_DISTANCE = 'isShortDistance';                 // Is the trip short distance
    public const IS_PAYMENT_REGULARIZED = 'isPaymentRegularized';       // Has the payment been regularized
    public const IS_USER_VALID = 'isUserValid';                         // Is the user data valid, according to the standard defined within the framework of the CEE
    public const MATCHING_ID = 'matchingId';                            // The Matching entity ID
    public const NAME = 'name';                                         // The log name
    public const TYPE_C = 'type_C';                                     // Is the journey of C type
    public const USER = 'user';                                         // The User

    private const ALLOWED_ARGUMENTS = [
        self::CARPOOL_PROOF_ID,
        self::DATETIME,
        self::IS_CARPOOL_PROOFS_VALID,
        self::IS_DATE_AFTER_REFERENCE_DATE,
        self::IS_DATE_IN_PERIOD,
        self::IS_FROM_FRANCE,
        self::IS_LONG_DISTANCE,
        self::IS_SHORT_DISTANCE,
        self::IS_PAYMENT_REGULARIZED,
        self::IS_USER_VALID,
        self::MATCHING_ID,
        self::NAME,
        self::TYPE_C,
        self::USER,
    ];

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var array
     */
    private $_log;

    public function __construct(
        LoggerInterface $logger,
        string $name,
        User $user,
        array $optionalArgs = []
    ) {
        $this->_logger = $logger;

        $this->_log = [
            'name' => $name,
            'datetime' => new \DateTime('now'),
            'user' => $user->getId(),
        ];

        foreach ($optionalArgs as $key => $arg) {
            $this->__addArgument($key, $arg);
        }

        $this->__writeLogInfo();
    }

    private function __addArgument(string $key, $value)
    {
        if (in_array($key, self::ALLOWED_ARGUMENTS)) {
            $this->_log[$key] = $value;
        }
    }

    private function __writeLogInfo()
    {
        $this->_logger->info(json_encode($this->_log));
    }
}
