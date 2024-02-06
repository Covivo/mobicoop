<?php

namespace App\Communication\Service\PushToCertifiedJourney;

use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Communication\Service\NotificationManager;
use App\Communication\Service\PushToCertifiedJourney\PushEvents\PushBeforeCarpoolEndEvent;
use App\Communication\Service\PushToCertifiedJourney\PushEvents\PushBeforeCarpoolStartEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PushToCertifiedJourneyManager
{
    /**
     * @var CarpoolProofRepository
     */
    protected $_carpoolProofRepository;

    /**
     * @var NotificationManager
     */
    protected $_notificationManager;

    /**
     * @var AskRepository
     */
    protected $_askRepository;

    /**
     * @var int
     */
    protected $_interval;

    /**
     * @var int
     */
    protected $_timeMargin;

    public function __construct(
        NotificationManager $notificationManager,
        AskRepository $askRepository,
        CarpoolProofRepository $carpoolProofRepository,
        int $timeMargin
    ) {
        $this->_notificationManager = $notificationManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_askRepository = $askRepository;

        $this->_timeMargin = $timeMargin;
    }

    /**
     * @param int|string $interval
     */
    public function execute($interval): void
    {
        $this->_build($interval);

        $this->_pushBeforeCarpoolStart();
        $this->_pushBeforeCarpoolEnd();
    }

    protected function _pushBeforeCarpoolStart(): void
    {
        $event = new PushBeforeCarpoolStartEvent($this->_notificationManager, $this->_interval, $this->_askRepository);
        $event->execute();
    }

    protected function _pushBeforeCarpoolEnd(): void
    {
        $event = new PushBeforeCarpoolEndEvent($this->_notificationManager, $this->_interval, $this->_carpoolProofRepository, $this->_timeMargin);
        $event->execute();
    }

    protected function _validatedInterval(string $interval): int
    {
        if (!preg_match('/^\d+$/', $interval)) {
            throw new BadRequestHttpException('The given interval is not an integer.');
        }

        return intval($interval);
    }

    protected function _build($interval): void
    {
        $this->_interval = is_string($interval)
            ? $this->_validatedInterval($interval) : $interval;
    }
}
