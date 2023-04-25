<?php

namespace App\UserRelaunch\Entity;

class Notification
{
    private const ERROR_MANDATORY_MISCONFIGURED = 'The mandatory property %s is mis configured';

    /**
     * @var string
     */
    private $_name;

    /**
     * @var null|Schedule
     */
    private $_schedule;

    /**
     * @var array
     */
    private $_notificationParameters;

    /**
     * @var \DateTime
     */
    private $_today;

    public function __construct(array $notification)
    {
        $this->_notificationParameters = $notification;
        $this->_setNotification();

        $this->_today = new \DateTime('now');
    }

    /**
     * Get the value of _name.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Get the value of _emailSchedule.
     */
    public function getSchedule(): ?Schedule
    {
        return $this->_schedule;
    }

    public function canNotify(): bool
    {
        return
            !is_null($this->_schedule)
            && !empty($this->_schedule->getScheduleDays())
            && in_array(strtolower($this->_today->format('D')), $this->_schedule->getScheduleDaysAsDaysArray())
        ;
    }

    public function getReminderDate()
    {
        $activeDay = $this->_schedule->getActiveScheduleDay();

        if ($this->canNotify() && !is_null($activeDay)) {
            switch (true) {
                case $this->_schedule->getOnceOnly():
                    return $activeDay->getDate();

                default:
                    return $this->_today->format('D');
            }
        }

        return null;
    }

    private function _setNotification(): void
    {
        $this->_setName();
        $this->_setSchedules();
    }

    private function _setSchedules()
    {
        if (
            !isset($this->_notificationParameters['schedules'])
            || !is_array($this->_notificationParameters['schedules'])
            || empty($this->_notificationParameters['schedules'])
        ) {
            throw new \LogicException(sprintf(self::ERROR_MANDATORY_MISCONFIGURED, 'schedules'));
        }

        $this->_setSchedule();
    }

    // * INTERNAL SETTERS ------------------------------------------------------------------------------------

    /**
     * Set the value of _name.
     */
    private function _setName(): self
    {
        if (!isset($this->_notificationParameters['name']) || empty($this->_notificationParameters['name'])) {
            throw new \LogicException(sprintf(self::ERROR_MANDATORY_MISCONFIGURED, 'name'));
        }

        $this->_name = $this->_notificationParameters['name'];

        return $this;
    }

    /**
     * Set the value of _emailSchedule.
     */
    private function _setSchedule(): self
    {
        if (!isset($this->_notificationParameters['schedules']) || !isset($this->_notificationParameters['schedules']['scheduleDays'])) {
            throw new \LogicException(sprintf(self::ERROR_MANDATORY_MISCONFIGURED, 'email/scheduleDays'));
        }

        $this->_schedule = empty($this->_notificationParameters['schedules']['scheduleDays'])
            ? null
            : new Schedule($this->_notificationParameters['schedules']);

        return $this;
    }
}
