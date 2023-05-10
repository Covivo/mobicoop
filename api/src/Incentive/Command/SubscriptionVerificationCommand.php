<?php

namespace App\Incentive\Command;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Incentive\Service\Validation\SubscriptionValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionVerificationCommand extends Command
{
    private const ALLOWED_SUBSCRIPTION_TYPES = ['long', 'short'];

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var InputInterface
     */
    private $_input;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_subscription;

    /**
     * @var int
     */
    private $_subscriptionId;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    /**
     * @var string
     */
    private $_subscriptionType;

    /**
     * @var SubscriptionValidation
     */
    private $_subscriptionValidation;

    public function __construct(EntityManagerInterface $em, SubscriptionManager $subscriptionManager, SubscriptionValidation $subscriptionValidation)
    {
        $this->_em = $em;
        $this->_subscriptionManager = $subscriptionManager;
        $this->_subscriptionValidation = $subscriptionValidation;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-verification')
            ->setDescription('Request verification of a subscription registered with mobConnect.')
            ->setHelp('Verify, with moB Connect, the subscription corresponding to the parameters.')
            ->addArgument('subscriptionType', InputArgument::REQUIRED, 'The type of subscription')
            ->addArgument('subscriptionId', InputArgument::REQUIRED, 'The id of the subscription')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;

        $this->_checkArguments();
        $this->_setSubscription();

        $this->_checkSubscription();

        return $this->_subscriptionManager->verifySubscription($this->_subscription);
    }

    private function _checkArguments()
    {
        $this->_setSubscriptionType();
        $this->_setSubscriptionId();
    }

    private function _checkSubscription(): void
    {
        if (!$this->_subscriptionValidation->isSubscriptionReadyForVerify($this->_subscription)) {
            throw new BadRequestHttpException("The {$this->_subscriptionType} subscription is not ready for verification");
        }
    }

    private function _setSubscriptionType(): self
    {
        if (!in_array($this->_input->getArgument('subscriptionType'), self::ALLOWED_SUBSCRIPTION_TYPES)) {
            throw new BadRequestHttpException('The subscriptionType parameter is incorrect. Please choose from: '.join(', ', self::ALLOWED_SUBSCRIPTION_TYPES));
        }

        $this->_subscriptionType = $this->_input->getArgument('subscriptionType');

        return $this;
    }

    private function _setSubscriptionId(): self
    {
        if (!preg_match('/^\d+$/', $this->_input->getArgument('subscriptionId'))) {
            throw new BadRequestHttpException('The subscriptionId parameter should be an integer');
        }

        $this->_subscriptionId = intval($this->_input->getArgument('subscriptionId'));

        return $this;
    }

    private function _setSubscription(): self
    {
        switch ($this->_subscriptionType) {
            case 'long':
                $repository = $this->_em->getRepository(LongDistanceSubscription::class);

                break;

            case 'short':
                $repository = $this->_em->getRepository(ShortDistanceSubscription::class);

                break;
        }

        $subscription = $repository->find($this->_subscriptionId);

        if (is_null($subscription)) {
            throw new NotFoundHttpException("The {$this->_subscriptionType} subscription was not found");
        }

        $this->_subscription = $subscription;

        return $this;
    }
}
