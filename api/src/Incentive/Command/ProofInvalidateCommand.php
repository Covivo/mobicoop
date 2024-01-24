<?php

namespace App\Incentive\Command;

use App\Carpool\Entity\CarpoolProof;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class ProofInvalidateCommand extends EecCommand
{
    protected function configure()
    {
        $this
            ->setName('app:incentive:proof-invalidate')
            ->setDescription('Invalidate manually a proof.')
            ->setHelp('From its CarpoolProof ID, manually reset a subscription.')
            ->addOption('proof', null, InputOption::VALUE_REQUIRED, 'The CarpoolProof ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_currentInput = $input;

        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($input->getOption('proof'));

        if (is_null($carpoolProof)) {
            $this->throwException(Response::HTTP_NOT_FOUND, 'The requested proof was not found');
        }

        $this->_subscriptionManager->invalidateProof($carpoolProof);

        $output->writeln('The incentive has been updated');
    }
}
