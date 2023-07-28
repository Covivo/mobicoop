<?php

namespace App\Incentive\Controller;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Event\FirstLongDistanceJourneyPublishedEvent;
use App\Incentive\Event\FirstShortDistanceJourneyPublishedEvent;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/subscriptions")
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class SubscriptionController extends AbstractController
{
    public const PARAM_DEPENDENCY_ID = 'dependency_id';
    public const PARAM_SUBSCRIPTION_ID = 'subscription_id';
    public const PARAM_SUBSCRIPTION_TYPE = 'subscription_type';

    private const MANDATORY_COMMIT_PARAMS = [
        self::PARAM_DEPENDENCY_ID,
        self::PARAM_SUBSCRIPTION_ID,
        self::PARAM_SUBSCRIPTION_TYPE,
    ];

    private const MANDATORY_UPDATE_PARAMS = [
        self::PARAM_SUBSCRIPTION_ID,
        self::PARAM_SUBSCRIPTION_TYPE,
    ];

    private const MANDATORY_HONOR_CERTIFICATE = self::MANDATORY_UPDATE_PARAMS;

    private const MANDATORY_TIMESTAMPS = self::MANDATORY_UPDATE_PARAMS;

    private const MANDATORY_VERIFY = self::MANDATORY_UPDATE_PARAMS;

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

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_subscription;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, EventDispatcherInterface $eventDispatcherInterface, SubscriptionManager $subscriptionManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;
        $this->_eventDispatcher = $eventDispatcherInterface;

        $this->_subscriptionManager = $subscriptionManager;
    }

    /**
     * Step 9 - EEC subscription commit.
     *
     * Requires 3 parameters:
     * - dependency_id - LongDistanceSubscription = Proposal::id | ShortDistanceSubscription = CarpoolProof::id
     * - subscription_id
     * - subscription_type
     *
     * @Route("/commit")
     */
    public function commitSubscription()
    {
        $this->_setSubscription(self::MANDATORY_COMMIT_PARAMS);

        $dependency = $this->_getDependency();

        if ($dependency instanceof Proposal) {
            if ($dependency->getUser()->getId() != $this->_subscription->getUser()->getId()) {
                throw new BadRequestHttpException('The driver associated with the proposal is not the one associated with the subscription');
            }

            $event = new FirstLongDistanceJourneyPublishedEvent($dependency);
            $this->_eventDispatcher->dispatch(FirstLongDistanceJourneyPublishedEvent::NAME, $event);
        }

        if ($dependency instanceof CarpoolProof) {
            if ($dependency->getDriver()->getId() != $this->_subscription->getUser()->getId()) {
                throw new BadRequestHttpException('The driver associated with the carpoolProof is not the one associated with the subscription');
            }

            exit('Fin de test - Check driver');
            $event = new FirstShortDistanceJourneyPublishedEvent($dependency);
            $this->_eventDispatcher->dispatch(FirstShortDistanceJourneyPublishedEvent::NAME, $event);
        }

        return new JsonResponse([
            'code' => Response::HTTP_OK,
            'message' => 'The process is complete',
        ]);
    }

    /**
     * Step 17 - EEC subscription update.
     *
     * Requires 2 parameters:
     * - subscription_id
     * - subscription_type
     *
     * @Route("/update")
     */
    public function updateEECSubscription()
    {
        $this->_setSubscription(self::MANDATORY_UPDATE_PARAMS);

        if ($this->_subscription->getCommitmentProofJourney()) {
            $commitmentJourney = $this->_subscription->getCommitmentProofJourney();

            if ($this->_subscription instanceof LongDistanceSubscription && !is_null($commitmentJourney->getCarpoolPayment())) {
                $event = new ElectronicPaymentValidatedEvent($commitmentJourney->getCarpoolPayment());
                $this->_eventDispatcher->dispatch(ElectronicPaymentValidatedEvent::NAME, $event);
            }

            if ($this->_subscription instanceof ShortDistanceSubscription && !is_null($commitmentJourney->getCarpoolProof())) {
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
     * Step 17a - Return the honor certificate.
     *
     * Requires 2 parameters:
     * - subscription_id
     * - subscription_type
     *
     * @Route("/honor_certificate")
     */
    public function getHonorCertificate()
    {
        $this->_setSubscription(self::MANDATORY_HONOR_CERTIFICATE);

        if (is_null($this->_subscription->getCommitmentProofJourney())) {
            throw new BadRequestHttpException('The subscription has not been commited');
        }

        $this->_subscriptionManager->setDriver($this->_subscription->getUser());

        return new JsonResponse([
            'code' => Response::HTTP_OK,
            'message' => 'The process is complete',
            'data' => [
                'honor_certificate' => $this->_subscriptionManager->getHonorCertificate($this->_subscription instanceof LongDistanceSubscription ? true : false),
            ],
        ]);
    }

    /**
     * Gets the timestamp tokens and returns which ones have a value.
     *
     * Requires 2 parameters:
     * - subscription_id
     * - subscription_type
     *
     * @Route("/timestamps")
     */
    public function getTimestampToken()
    {
        $this->_setSubscription(self::MANDATORY_TIMESTAMPS);

        $this->_subscriptionManager->setTimestamps($this->_subscription);

        $tokens = $this->_subscriptionManager->getTimestamps();

        return new JsonResponse([
            'code' => Response::HTTP_OK,
            'message' => 'The process is complete',
            'data' => [
                'timestamp_tokens' => [
                    'subscription' => !is_null($tokens->getIncentiveProofTimestampToken()),
                    'commitmment' => !is_null($tokens->getCommitmentProofTimestampToken()),
                    'update' => !is_null($tokens->getHonorCertificateProofTimestampToken()),
                ],
            ],
        ]);
    }

    /**
     * @Route("/verify")
     */
    public function verifySubscription()
    {
        $this->_setSubscription(self::MANDATORY_VERIFY);

        $response = $this->_subscriptionManager->verifySubscription($this->_subscription);

        return new JsonResponse([
            'code' => Response::HTTP_OK,
            'message' => 'The process is complete',
            'data' => [
                'subscription_state' => $response instanceof MobConnectSubscriptionTimestampsResponse ? $response->getContent() : $this->_subscription->getStatus(),
            ],
        ]);
    }

    private function _checkDependencies(array $mandatoryParams)
    {
        foreach ($mandatoryParams as $key => $param) {
            if (is_null($this->_request->get($param))) {
                throw new BadRequestHttpException('The mandatory param '.$param.' is missing');
            }
        }
    }

    /**
     * @return CarpoolProof|Proposal
     */
    private function _getDependency()
    {
        switch (true) {
            case $this->_subscription instanceof LongDistanceSubscription:
                /**
                 * @var Proposal
                 */
                $dependency = $this->_em->getRepository(Proposal::class)->find($this->_request->get(self::PARAM_DEPENDENCY_ID));

                if (is_null($dependency)) {
                    throw new NotFoundHttpException('The carpoolProof with the '.$this->_request->get(self::PARAM_DEPENDENCY_ID).' ID was not found');
                }

                break;

            case $this->_subscription instanceof ShortDistanceSubscription:
                /**
                 * @var CarpoolProof
                 */
                $dependency = $this->_em->getRepository(CarpoolProof::class)->find($this->_request->get(self::PARAM_DEPENDENCY_ID));

                if (is_null($dependency)) {
                    throw new NotFoundHttpException('The carpoolProof with the '.$this->_request->get(self::PARAM_DEPENDENCY_ID).' ID was not found');
                }

                break;
        }

        return $dependency;
    }

    private function _setSubscription(array $mandatoryParams): self
    {
        $this->_checkDependencies($mandatoryParams);

        // @var LongDistanceSubscription|ShortDistanceSubscription
        $this->_subscription = $this->_getRepository()->findOneBy([
            'subscriptionId' => $this->_request->get(self::PARAM_SUBSCRIPTION_ID),
        ]);

        if (is_null($this->_subscription)) {
            throw new NotFoundHttpException('The subscription with the '.$this->_request->get(self::PARAM_SUBSCRIPTION_ID).' ID was not found');
        }

        return $this;
    }

    /**
     * @return EntityRepository
     */
    private function _getRepository()
    {
        switch (strtolower(trim($this->_request->get(self::PARAM_SUBSCRIPTION_TYPE)))) {
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
