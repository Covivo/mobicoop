<?php

namespace App\Incentive\Command;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Carpool\Repository\CarpoolProofRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProofValidationCommand extends Command
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, CarpoolProofRepository $carpoolProofRepository)
    {
        $this->_eventDispatcher = $eventDispatcher;
        $this->_carpoolProofRepository = $carpoolProofRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:incentive:proof-validate')
            ->setDescription('Launches at EEC level, the manual validation of a proof.')
            ->setHelp('Launches at EEC level, the manual validation of a proof.')
            ->addArgument('proof_id', InputArgument::REQUIRED, 'The proof ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $carpoolProof = $this->_carpoolProofRepository->find($input->getArgument('proof_id'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The proof was not found');
        }

        $event = new CarpoolProofValidatedEvent($carpoolProof);
        $this->_eventDispatcher->dispatch(CarpoolProofValidatedEvent::NAME, $event);
    }
}
