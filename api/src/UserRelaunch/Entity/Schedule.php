<?php

namespace App\UserRelaunch\Entity;

class Schedule
{
    public const ORDER_CHRONOLOGICAL_ANTE = -1;
    public const ORDER_CHRONOLOGICAL = 1;

    private const ALLOWED_ORDERS = [self::ORDER_CHRONOLOGICAL, self::ORDER_CHRONOLOGICAL_ANTE];

    /**
     * @var int
     */
    private $_order = self::ORDER_CHRONOLOGICAL;

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

    public function __construct(array $scheduleParameters)
    {
        $this->_scheduleParameters = $scheduleParameters;

        $this->_setOrder();
        $this->_setSchedules();
    }

    /**
     * Get the value of _order.
     */
    public function getOrder(): int
    {
        return $this->_order;
    }

    /**
     * Get the value of scheduleDays.
     */
    public function getScheduleDays(): array
    {
        return $this->_scheduleDays;
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
        array_push($this->_scheduleDays, $day);

        return $this;
    }

    private function _addScheduleDelay(int $delay): self
    {
        array_push($this->_scheduleDelays, $delay);

        return $this;
    }

    /**
     * Set the value of _order.
     */
    private function _setOrder(): self
    {
        if (
            !isset($this->_scheduleParameters['order'])
            || is_null($this->_scheduleParameters['order'])
            || !in_array($this->_scheduleParameters['order'], self::ALLOWED_ORDERS)
        ) {
            $this->_order = self::ORDER_CHRONOLOGICAL;
        }

        $this->_order = $this->_scheduleParameters['order'];

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
