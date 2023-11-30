<?php

namespace App\Incentive\Command;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Service\Manager\TimestampTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionTimestampsTokenCommand extends Command
{
    private const ALLOWED_TYPES = ['long', 'short'];

    private const DEFAULT_FILE_PATH = './public/eec/tokens';

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_currentSubscription;

    /**
     * @var TimestampTokenManager
     */
    private $_timestampTokenManager;

    public function __construct(EntityManagerInterface $em, TimestampTokenManager $TimestampTokenManager)
    {
        $this->_em = $em;
        $this->_timestampTokenManager = $TimestampTokenManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:subscription-mobTimestampsToken')
            ->setDescription('Get subscription timestamps token.')
            ->setHelp('Returns user subscription timestamps token from moB without any further action.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The type of the subscription (allowed long and short)')
            ->addOption('subscription', null, InputOption::VALUE_REQUIRED, 'The ID of the subscription to be processed')
            ->addOption('toFile', null, InputOption::VALUE_NONE, 'The response is given into a JSON file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');

        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new \LogicException('The subscription type is not allowed. Use among '.join(' OR ', self::ALLOWED_TYPES));
        }

        // Obtenir la souscription
        $this->_currentSubscription = Subscription::TYPE_LONG
            ? $this->_em->getRepository(LongDistanceSubscription::class)->find($input->getOption('subscription'))
            : $this->_em->getRepository(ShortDistanceSubscription::class)->find($input->getOption('subscription'));

        if (is_null($this->_currentSubscription)) {
            throw new NotFoundHttpException('The requested subscription was not found');
        }

        $tokens = json_encode($this->_timestampTokenManager->getMobTimestampToken($this->_currentSubscription)->getContent());

        if (!$input->getOption('toFile')) {
            $output->writeln($tokens);

            exit;
        }

        $this->writeFile($tokens);

        $output->writeln('The file has been created');
    }

    private function writeFile(string $fileContent)
    {
        $now = new \DateTime('now');

        $filename = self::DEFAULT_FILE_PATH.'/'.$now->format('YmdHms').'-'.$this->_currentSubscription->getUser()->getId().'-'.$this->_currentSubscription->getSubscriptionId().'.json';
        $file = fopen($filename, 'w');
        fwrite($file, $fileContent);
        fclose($file);
    }
}
