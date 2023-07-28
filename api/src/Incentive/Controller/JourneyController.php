<?php

namespace App\Incentive\Controller;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/eec/journeys")
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class JourneyController extends AbstractController
{
    public const PARAM_SUBSCRIPTION_ID = 'subscription_id';
    public const PARAM_SUBSCRIPTION_TYPE = 'subscription_type';

    private const MANDATORY_COMMIT_PARAMS = [];

    private const MANDATORY_UPDATE_PARAMS = [
        self::PARAM_SUBSCRIPTION_ID,
        self::PARAM_SUBSCRIPTION_TYPE,
    ];

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var Request
     */
    private $_request;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, EventDispatcherInterface $eventDispatcherInterface)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;
        $this->_eventDispatcher = $eventDispatcherInterface;
    }

    /**
     * Step 9 - EEC subscription commit.
     *
     * @Route("/commit")
     */
    public function commitSubscription()
    {
    }

    /**
     * Step 17 - EEC subscription update.
     *
     * @Route("/update")
     */
    public function updateEECSubscription()
    {
        foreach (self::MANDATORY_UPDATE_PARAMS as $key => $param) {
            if (is_null($this->_request->get($param))) {
                throw new BadRequestHttpException('The mandatory param '.$param.' is missing');
            }
        }

        /**
         * @var LongDistanceSubscription|ShortDistanceSubscription
         */
        $subscription = $this->_getRepository()->findOneBy([
            'subscriptionId' => $this->_request->get(self::PARAM_SUBSCRIPTION_ID),
        ]);

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription with the '.$this->_request->get(self::PARAM_SUBSCRIPTION_ID).' ID was not found');
        }

        if ($subscription->getCommitmentProofJourney()) {
            $commitmentJourney = $subscription->getCommitmentProofJourney();

            if ($subscription instanceof LongDistanceSubscription && !is_null($commitmentJourney->getCarpoolPayment())) {
                $event = new ElectronicPaymentValidatedEvent($commitmentJourney->getCarpoolPayment());
                $this->_eventDispatcher->dispatch(ElectronicPaymentValidatedEvent::NAME, $event);
            }

            if ($subscription instanceof ShortDistanceSubscription && !is_null($commitmentJourney->getCarpoolProof())) {
                $event = new CarpoolProofValidatedEvent($commitmentJourney->getCarpoolProof());
                $this->_eventDispatcher->dispatch(CarpoolProofValidatedEvent::NAME, $event);
            }
        }

        return new JsonResponse([
            'code' => Response::HTTP_OK,
            'message' => 'The process is complete',
        ]);
    }

    /**
     * @return EntityRepository
     */
    private function _getRepository()
    {
        switch ($this->_request->get(self::PARAM_SUBSCRIPTION_TYPE)) {
            case LongDistanceSubscription::SUBSCRIPTION_TYPE:
                /**
                 * @var LongDistanceSubscription
                 */
                $repository = $this->_em->getRepository(LongDistanceSubscription::class);

                break;

            case ShortDistanceSubscription::SUBSCRIPTION_TYPE:
                /**
                 * @var ShortDistanceSubscription
                 */
                $repository = $this->_em->getRepository(ShortDistanceSubscription::class);

                break;

            default:
                throw new BadRequestHttpException('The subscription type '.$this->_request->get(self::PARAM_SUBSCRIPTION_TYPE).' does not exists');
        }

        return $repository;
    }
}
