<?php

namespace App\Solidary\Service;

use App\Action\Entity\Action;
use App\Carpool\Entity\Criteria;
use App\Communication\Entity\Notification;
use App\Communication\Repository\NotifiedRepository;
use App\Communication\Service\NotificationManager;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Entity\SolidarySolution;

class SolidaryNotificationManager
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var NotifiedRepository
     */
    private $_notifiedRepository;

    /**
     * @var NotificationManager
     */
    private $_notificationManager;

    /**
     * @var Solidary
     */
    private $_solidary;

    /**
     * @var User[]
     */
    private $_volunteers = [];

    /**
     * @var int
     */
    private $_timeBeforeDeadline;

    public function __construct(
        EntityManagerInterface $em,
        NotifiedRepository $notifiedRepository,
        NotificationManager $notificationManager,
        int $timeBeforeDeadline
    ) {
        $this->_em = $em;
        $this->_notifiedRepository = $notifiedRepository;
        $this->_notificationManager = $notificationManager;

        $this->_timeBeforeDeadline = $timeBeforeDeadline;
    }

    public function notifyMatched(Solidary $solidary): void
    {
        $this->_solidary = $solidary;

        $this->_setVolunteers();

        foreach ($this->_volunteers as $volunteer) {
            $this->notifyWhenMatchFounded($volunteer);
        }
    }

    public function notifyWhenMatchFounded(User $volunteer): void
    {
        if (!$this->_isProcessExecutable($volunteer) || $this->_hasAllreadyBeenNotified($volunteer)) {
            return;
        }

        $this->_notificationManager->notifies(
            Action::ACTION_SOLIDARY_VOLUNTEER_MATCHING_SUCCESS,
            $volunteer,
            $this->_solidary
        );
    }

    private function _isProcessExecutable(User $volunteer): bool
    {
        return !$this->_isSolidaryEnded() && !$this->_isAllreadyVolonteerInSolutions($volunteer);
    }

    private function _isSolidaryEnded(): bool
    {
        if (Criteria::FREQUENCY_PUNCTUAL === $this->_solidary->getFrequency()) {
            $solidaryFromDateTime = $this->_solidary->getProposal()->getCriteria()->getFromDate()->format('Y-m-d') . $this->_solidary->getProposal()->getCriteria()->getFromTime()->format('H:i:s');
            $deadline = \DateTime::createFromFormat('Y-m-d H:i:s', $solidaryFromDateTime);
            $deadline->sub(new \DateInterval('PT' . $this->_timeBeforeDeadline . 'H'));

            return $this->_solidary->getProposal()->getCriteria()->getFromDate() < new \DateTime();
        }

        return $this->_solidary->getProposal()->getCriteria()->getToDate() < new \DateTime();
    }

    private function _isAllreadyVolonteerInSolutions(User $volunteer): bool
    {
        /**
         * @var SolidarySolution[]
         */
        $solutions = $this->_solidary->getSolidarySolutions();

        return !empty(array_filter($solutions, function (SolidarySolution $solution) use ($volunteer) {
            return $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getId() === $volunteer->getId();
        }));
    }

    private function _hasAllreadyBeenNotified(User $volunteer): bool
    {
        return !empty($this->_notifiedRepository->findNotifiedByUserAndNotificationAndSolidary(
            $volunteer->getId(),
            Notification::SOLIDARY_VOLUNTEER_MATCHING_SUCCESS,
            $this->_solidary->getId()
        ));
    }

    private function _setVolunteers(): void
    {
        foreach ($this->_solidary->getSolidaryMatchings() as $solidaryMatching) {
            $user = !is_null($solidaryMatching->getSolidaryUser())
                ? $solidaryMatching->getSolidaryUser()->getUser()
                : $this->_getRecipientFromMatching($solidaryMatching);

            if (
                !is_null($user)
                && empty(array_filter(
                    $this->_volunteers,
                    function ($volunteer) use ($user) {
                        return $volunteer->getId() === $user->getId();
                    }
                ))
            ) {
                array_push($this->_volunteers, $user);
            }
        }
    }

    private function _getRecipientFromMatching(SolidaryMatching $solidaryMatching): User
    {
        $userId = $this->_solidary->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getId();

        $matching = $solidaryMatching->getMatching();

        return $matching->getProposalOffer()->getUser()->getId() !== $userId
            ? $matching->getProposalOffer()->getUser()
            : $matching->getProposalRequest()->getUser();
    }
}
