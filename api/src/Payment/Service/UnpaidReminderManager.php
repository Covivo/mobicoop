<?php

namespace App\Payment\Service;

use App\Payment\Event\SignalDeptEvent;
use App\Payment\Repository\CarpoolItemRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UnpaidReminderManager
{
    /**
     * @var CarpoolItemRepository
     */
    private $_carpoolItemRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var array
     */
    private $_reminderDelays;

    public function __construct(CarpoolItemRepository $carpoolItemRepository, EventDispatcherInterface $eventDispatcherInterface, array $reminderDelays)
    {
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_eventDispatcher = $eventDispatcherInterface;
        $this->_reminderDelays = $reminderDelays;
    }

    public function SendReminderEmails()
    {
        foreach ($this->_reminderDelays as $delay) {
            $carpoolItems = $this->_carpoolItemRepository->findUnpaidForDelay($delay);

            foreach ($carpoolItems as $carpoolItem) {
                $event = new SignalDeptEvent($carpoolItem);
                $this->_eventDispatcher->dispatch(SignalDeptEvent::NAME, $event);
            }
        }
    }
}
