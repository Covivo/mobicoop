<?php

namespace App\Incentive\Command;

use App\Incentive\Service\Manager\SubscriptionManager;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateTokenCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(EntityManagerInterface $em, SubscriptionManager $subscriptionManager)
    {
        $this->_em = $em;
        $this->_subscriptionManager = $subscriptionManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-tokens')
            ->setDescription('Returns the subscription tokens.')
            ->setHelp('Returns the subscription tokens and if requested, obtain them before from moB.')
            ->addArgument('userId', InputArgument::REQUIRED, 'The id of the user')
            ->addArgument('update', InputArgument::OPTIONAL, 'Specifies whether missing tokens should be obtained')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var User
         */
        $user = $this->_em->getRepository(User::class)->find($input->getArgument('userId'));

        if (is_null($user)) {
            throw new NotFoundHttpException('The requested user was not found');
        }

        if (boolval($input->getArgument('update'))) {
            $user = $this->_subscriptionManager->updateTimestampTokens($user);
        }

        $output->writeln(json_encode([
            'user_id' => $user->getid(),
            'shortDistanceSubscription' => is_null($user->getShortDistanceSubscription()) ? null : [
                'incentiveToken' => $user->getShortDistanceSubscription()->getIncentiveProofTimestampToken(),
                'incentiveTokenSigninTime' => $user->getShortDistanceSubscription()->getIncentiveProofTimestampSigningTime(),
                'commitmentToken' => $user->getShortDistanceSubscription()->getCommitmentProofTimestampToken(),
                'commitmentTokenSigninTime' => $user->getShortDistanceSubscription()->getCommitmentProofTimestampSigningTime(),
                'honorCertificateToken' => $user->getShortDistanceSubscription()->gethonorCertificateProofTimestampToken(),
                'honorCertificateTokenSigninTime' => $user->getShortDistanceSubscription()->gethonorCertificateProofTimestampSigningTime(),
            ],
            'longDistanceSubscription' => is_null($user->getLongDistanceSubscription()) ? null : [
                'incentiveToken' => $user->getLongDistanceSubscription()->getIncentiveProofTimestampToken(),
                'incentiveTokenSigninTime' => $user->getLongDistanceSubscription()->getIncentiveProofTimestampSigningTime(),
                'commitmentToken' => $user->getLongDistanceSubscription()->getCommitmentProofTimestampToken(),
                'commitmentTokenSigninTime' => $user->getLongDistanceSubscription()->getCommitmentProofTimestampSigningTime(),
                'honorCertificateToken' => $user->getLongDistanceSubscription()->gethonorCertificateProofTimestampToken(),
                'honorCertificateTokenSigninTime' => $user->getLongDistanceSubscription()->gethonorCertificateProofTimestampSigningTime(),
            ],
        ]));
    }
}
