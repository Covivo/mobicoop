<?php

namespace App\Communication\Service\PushToCertifiedJourney\PushEvents;

use App\Carpool\Repository\MatchingRepository;
use App\Communication\Service\NotificationManager;
use App\Service\Date\DateService;

class PushBeforeCarpoolStartEvent extends PushEvent
{
    public const PUSH_ACTION = 'certify_drop_off_before_start';

    /**
     * @var MatchingRepository
     */
    protected $_matchingRepository;

    public function __construct(NotificationManager $notificationManager, int $interval, MatchingRepository $matchingRepository)
    {
        parent::__construct($notificationManager, $interval);

        $this->_matchingRepository = $matchingRepository;
    }

    public function execute(): bool
    {
        $this->_deadlineDate = DateService::getDateAccordingFutureInterval(DateService::MINUTE, $this->_interval);

        $this->_journeys = $this->_matchingRepository->findCarpoolsReadyToStart($this->_now, $this->_deadlineDate);

        return parent::execute();
    }
}
