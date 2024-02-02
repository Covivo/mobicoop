<?php

namespace App\Communication\Service\PushToCertifiedJourney\PushEvents;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Communication\Service\NotificationManager;
use App\Service\Date\DateService;

class PushBeforeCarpoolEndEvent extends PushEvent
{
    public const PUSH_ACTION = 'certify_pick_up_before_end';

    /**
     * @var CarpoolProofRepository
     */
    protected $_carpoolProofRepository;

    /**
     * @var int
     */
    protected $_timeMargin;

    public function __construct(NotificationManager $notificationManager, int $interval, CarpoolProofRepository $carpoolProofRepository, int $timeMargin)
    {
        parent::__construct($notificationManager, $interval);

        $this->_carpoolProofRepository = $carpoolProofRepository;

        $this->_timeMargin = $timeMargin;
    }

    public function execute(): bool
    {
        $this->_deadlineDate = DateService::getDateAccordingPastInterval(DateService::MINUTE, $this->_interval);

        $this->_journeys = $this->_carpoolProofRepository->findCarpoolsReadyToEnd($this->_deadlineDate, $this->_now, $this->_timeMargin);

        return parent::execute();
    }
}
