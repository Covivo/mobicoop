<?php

namespace App\Carpool\Command;

use App\Carpool\Repository\CarpoolProofRepository;
use App\DataProvider\Entity\CarpoolProofGouvProviderV3;
use App\DataProvider\Service\RPCv3\Tools;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarpoolProofSendHistory extends Command
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var Tools
     */
    private $_tools;

    /**
     * @var string
     */
    private $_uri;

    /**
     * @var string
     */
    private $_token;

    /**
     * @var string
     */
    private $_prefix;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        LoggerInterface $logger,
        Tools $tools,
        string $uri,
        string $token,
        string $prefix
    ) {
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_logger = $logger;
        $this->_tools = $tools;

        $this->_uri = $uri;
        $this->_token = $token;
        $this->_prefix = $prefix;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:carpool:proof-send-history')
            ->setDescription('Sends proofs history to RPC.')
            ->setHelp('Sends proofs history to RPC.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $proofs = array_merge(
        //     $this->_carpoolProofRepository->findProofsToSendAsHistory(),
        //     $this->_carpoolProofRepository->findProofsToSendAsHistory(false)
        // );

        // $provider = new CarpoolProofGouvProviderV3($this->_tools, $this->_uri, $this->_token, $this->_prefix, $this->_logger);

        // foreach ($proofs as $proof) {
        //     $provider->postCollection($proof);
        // }
    }
}
