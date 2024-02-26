<?php

namespace App\Communication\Service\PushToCertifiedJourney\PushEvents;

use App\Carpool\Repository\AskRepository;
use App\Communication\Service\NotificationManager;
use App\Service\Date\DateService;

class PushBeforeCarpoolStartEvent extends PushEvent
{
    public const PUSH_ACTION = 'certify_drop_off_before_start';

    /**
     * @var AskRepository
     */
    protected $_askRepository;

    public function __construct(
        NotificationManager $notificationManager,
        int $interval,
        AskRepository $askRepository,
        int $serverUtcTimeDiff = DateService::SERVER_UTC_TIME_DIFF
    ) {
        parent::__construct($notificationManager, $interval, $serverUtcTimeDiff);

        $this->_askRepository = $askRepository;
    }

    public function execute(): bool
    {
        $this->_deadlineDate = DateService::getDateAccordingFutureInterval(DateService::MINUTE, $this->_interval, $this->_now);

        $this->_journeys = $this->_askRepository->findCarpoolsReadyToStart($this->_now, $this->_deadlineDate);

        return parent::execute();
    }
}
