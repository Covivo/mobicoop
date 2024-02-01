<?php

namespace App\Communication\Service;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Service\Date\DateService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PushNotifyToCertifiedJourneyManager
{
    /**
     * @var CarpoolProofRepository
     */
    protected $_carpoolProofRepository;

    /**
     * @var MatchingRepository
     */
    protected $_matchingRepository;

    /**
     * @var int
     */
    protected $_interval;

    /**
     * @var \DateTimeInterface
     */
    private $_now;

    public function __construct(MatchingRepository $matchingRepository, CarpoolProofRepository $carpoolProofRepository)
    {
        $this->_matchingRepository = $matchingRepository;
        $this->_carpoolProofRepository = $carpoolProofRepository;
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
        $deadline = DateService::getDateAccordingFutureInterval(DateService::MINUTE, $this->_interval);

        $journeys = $this->_matchingRepository->findCarpoolsReadyToStart($this->_now, $deadline);
    }

    protected function _pushBeforeCarpoolEnd(): void
    {
        $deadline = DateService::getDateAccordingPastInterval(DateService::MINUTE, $this->_interval);

        $journeys = $this->_carpoolProofRepository->findCarpoolsReadyToEnd($deadline, $this->_now);
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

        $this->_now = DateService::getNow();
    }
}
