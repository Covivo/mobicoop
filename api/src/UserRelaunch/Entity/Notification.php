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
    private $_emailSchedule;

    /**
     * @var null|Schedule
     */
    private $_pushSchedule;

    /**
     * @var null|Schedule
     */
    private $_smsSchedule;

    /**
     * @var array
     */
    private $_notificationParameters;

    public function __construct(array $notification)
    {
        $this->_notificationParameters = $notification;
        $this->_setNotification();
    }

    /**
     * Get the value of _name.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Returns, based on the given date whether the notification should be sent.
     */
    public function getNotify(): bool
    {
        return false;
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

        $this->_setSchedule('email');
        $this->_setSchedule('push');
        $this->_setSchedule('sms');
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
    private function _setSchedule(string $type): self
    {
        if (!isset($this->_notificationParameters['schedules'][$type]) || !isset($this->_notificationParameters['schedules'][$type]['scheduleDays'])) {
            throw new \LogicException(sprintf(self::ERROR_MANDATORY_MISCONFIGURED, 'email/scheduleDays'));
        }

        $property = '_'.$type.'Schedule';

        $this->{$property} = empty($this->_notificationParameters['schedules'][$type]['scheduleDays'])
            ? null
            : new Schedule($this->_notificationParameters['schedules'][$type]);

        return $this;
    }
}
