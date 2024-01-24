<?php

namespace App\Incentive\Command;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\JourneyManager;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EecCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $_currentInput;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    protected $_currentSubscription;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var JourneyManager
     */
    protected $_journeyManager;

    /**
     * @var SubscriptionManager
     */
    protected $_subscriptionManager;

    public function __construct(EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        $this->_em = $em;
        $this->_journeyManager = $journeyManager;

        parent::__construct();
    }

    protected function checkCarpoolPayment(CarpoolPayment $carpoolPayment)
    {
        if (is_null($carpoolPayment)) {
            throw new NotFoundHttpException('The payment was not found');
        }

        foreach ($carpoolPayment->getCarpoolItems() as $carpoolItem) {
            if (
                is_null($carpoolItem->getCreditorUser())
                || $this->_currentSubscription->getUser()->getId() !== $carpoolItem->getCreditorUser()->getId()
            ) {
                throw new BadRequestHttpException('The user associated with the incentive is not the one associated with the CarpoolPayment');
            }
        }
    }

    protected function checkCarpoolProof(CarpoolProof $carpoolProof)
    {
        if (is_null($carpoolProof)) {
            $this->throwException(Response::HTTP_NOT_FOUND, 'The journey (CarpoolProof) was not found');
        }

        if ($this->_currentSubscription->getUser()->getId() !== $carpoolProof->getDriver()->getId()) {
            $this->throwException(Response::HTTP_BAD_REQUEST, 'The user associated with the incentive is not the one associated with the CarpoolProof');
        }
    }

    protected function checkProposal(Proposal $proposal)
    {
        if (is_null($proposal)) {
            $this->throwException(Response::HTTP_NOT_FOUND, 'The journey (Proposal) was not found');
        }

        if ($this->_currentSubscription->getUser()->getId() !== $proposal->getUser()->getId()) {
            $this->throwException(Response::HTTP_BAD_REQUEST, 'The user associated with the incentive is not the one associated with the Proposal');
        }
    }

    protected function throwException(int $exception, string $msg = null)
    {
        switch ($exception) {
            case Response::HTTP_NOT_FOUND: throw new NotFoundHttpException($msg);

            case Response::HTTP_BAD_REQUEST: throw new BadRequestHttpException($msg);
        }
    }
}
