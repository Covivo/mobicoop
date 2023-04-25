<?php

namespace App\UserRelaunch\Entity;

class Schedule
{
    public const POSITION_NEXT = 'next';
    public const POSITION_PREVIOUS = 'previous';

    /**
     * Specifies whether the follow-up must be performed only once. Use with named weekdays.
     *
     * @var bool
     */
    private $_onceOnly = false;

    /**
     * @var array
     */
    private $_scheduleDays = [];

    /**
     * @var array
     */
    private $_scheduleDelays = [];

    /**
     * @var array
     */
    private $_scheduleParameters;

    /**
     * @var \DateTime
     */
    private $_today;

    public function __construct(array $scheduleParameters)
    {
        $this->_scheduleParameters = $scheduleParameters;

        $this->_setOnceOnly();
        $this->_setSchedules();

        $this->_today = new \DateTime('now');
    }

    /**
     * Get specifies whether the follow-up must be performed only once. Use with named weekdays.
     */
    public function getOnceOnly(): bool
    {
        return $this->_onceOnly;
    }

    /**
     * Get the value of scheduleDays.
     */
    public function getScheduleDays(): array
    {
        return $this->_scheduleDays;
    }

    public function getActiveScheduleDay(): ?ScheduleDay
    {
        $filteredScheduleDay = array_values(array_filter($this->getScheduleDays(), function ($scheduleDay) {
            return $scheduleDay->getDayShortened() === strtolower($this->_today->format('D'));
        }));

        return !empty($filteredScheduleDay) ? $filteredScheduleDay[0] : null;
    }

    public function getScheduleDaysAsDaysArray(): array
    {
        return array_map(function ($scheduleDay) {
            return $scheduleDay->getDayShortened();
        }, $this->getScheduleDays());
    }

    /**
     * Get the value of _scheduleDelays.
     */
    public function getScheduleDelays(): array
    {
        return $this->_scheduleDelays;
    }

    private function _addScheduleDay(string $day): self
    {
        array_push($this->_scheduleDays, new ScheduleDay(strtolower($day)));

        return $this;
    }

    private function _addScheduleDelay(int $delay): self
    {
        array_push($this->_scheduleDelays, $delay);

        return $this;
    }

    private function _setOnceOnly(): self
    {
        if (isset($this->_scheduleParameters['onceOnly']) && is_bool($this->_scheduleParameters['onceOnly'])) {
            $this->_onceOnly = $this->_scheduleParameters['onceOnly'];
        }

        return $this;
    }

    private function _setSchedules(): self
    {
        foreach ($this->_scheduleParameters['scheduleDays'] as $schedule) {
            switch (gettype($schedule)) {
                case 'string':
                    $this->_addScheduleDay($schedule);

                    break;

                case 'integer':
                    $this->_addScheduleDelay($schedule);

                    break;

                default:
                    throw new \LogicException("the value '{$schedule}' has a not allowed type");

                    break;
            }
        }

        return $this;
    }
}
