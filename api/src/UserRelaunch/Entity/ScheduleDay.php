<?php

namespace App\UserRelaunch\Entity;

class ScheduleDay
{
    public const MONDAY = 'monday';
    public const TUESDAY = 'tuesday';
    public const WEDNESDAY = 'wednesday';
    public const THURSDAY = 'thursday';
    public const FRIDAY = 'friday';
    public const SATURDAY = 'saturday';
    public const SUNDAY = 'sunday';

    private const DAY_CORRESPONDENCES = [
        'mon' => self::MONDAY,
        'tue' => self::TUESDAY,
        'wed' => self::WEDNESDAY,
        'thu' => self::THURSDAY,
        'fri' => self::FRIDAY,
        'sat' => self::SATURDAY,
        'sun' => self::SUNDAY,
    ];

    /**
     * @var string
     */
    private $_dayShortened;

    /**
     * @var string
     */
    private $_day;

    /**
     * @var \DateTime
     */
    private $_date;

    public function __construct(string $dayShortened)
    {
        $this->_dayShortened = $dayShortened;
        $this->_day = self::DAY_CORRESPONDENCES[$dayShortened];
        $this->_setDates();
    }

    /**
     * Get the value of _dayShortened.
     */
    public function getDayShortened(): string
    {
        return $this->_dayShortened;
    }

    /**
     * Get the value of _day.
     */
    public function getDay(): string
    {
        return $this->_day;
    }

    /**
     * Get the value of _chronologicDate.
     */
    public function getDate(): \DateTime
    {
        return $this->_date;
    }

    private function _setDates(): self
    {
        $now = new \DateTime('now');

        $today = \DateTime::createFromFormat('Y-m-d', $now->format('Y-m-d'));
        $this->_date = clone $today->modify("previous {$this->_day}");

        return $this;
    }
}
