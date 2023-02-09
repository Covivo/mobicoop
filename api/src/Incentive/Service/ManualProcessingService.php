<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ManualProcessingService
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var LoggerService
     */
    private $_loggerService;

    /**
     * @var MobConnectSubscriptionManager
     */
    private $_mobConnectSubscriptionManager;

    /**
     * @var Request
     */
    private $_request;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        LoggerService $loggerService,
        MobConnectSubscriptionManager $mobConnectSubscriptionManager
    ) {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;
        $this->_loggerService = $loggerService;
        $this->_mobConnectSubscriptionManager = $mobConnectSubscriptionManager;
    }

    public function execute()
    {
        $carpoolProofIds = $this->_getIdsAsArray('carpoolProofs');
        $carpoolPaymentIds = $this->_getIdsAsArray('carpoolPayments');

        if (empty($carpoolProofIds) && empty($carpoolPaymentIds)) {
            throw new BadRequestHttpException('It is only possible to carry out a treatment if either the proofs or the payments are informed');
        }

        if (!empty($carpoolProofIds) && !empty($carpoolPaymentIds)) {
            throw new BadRequestHttpException('It is not possible to process proofs and payments at the same time');
        }

        if (!empty($carpoolPaymentIds)) {
            $this->executeForCarpoolPayments($carpoolPaymentIds);
        }

        if (!empty($carpoolProofIds)) {
            $this->_executeForCarpoolProofs($carpoolProofIds);
        }
    }

    private function executeForCarpoolPayments(array $carpoolPaymentIds)
    {
        $this->_loggerService->log('Start Payment processing');

        foreach ($carpoolPaymentIds as $key => $id) {
            $carpoolPayment = $this->_em->getRepository(CarpoolPayment::class)->find(intval($id));

            if (is_null($carpoolPayment)) {
                $this->_loggerService->log('There is no record for Id: '.$id, 'alert');

                continue;
            }

            $this->_loggerService->log('Processing Payment: '.$id);
            $this->_mobConnectSubscriptionManager->updateLongDistanceSubscriptionAfterPayment($carpoolPayment);
        }
    }

    private function _executeForCarpoolProofs(array $carpoolProofIds)
    {
        $this->_loggerService->log('Start Proof processing');

        foreach ($carpoolProofIds as $key => $id) {
            $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find(intval($id));

            if (is_null($carpoolProof)) {
                $this->_loggerService->log('There is no record for Id: '.$id, 'alert');

                continue;
            }

            $this->_loggerService->log('Processing Proof: '.$id);
            $this->_mobConnectSubscriptionManager->updateSubscription($carpoolProof);
        }
    }

    private function _getIdsAsArray(string $entityName): array
    {
        $idsAsString = $this->_request->get($entityName);

        return is_null($idsAsString) ? [] : explode(',', $idsAsString);
    }
}
