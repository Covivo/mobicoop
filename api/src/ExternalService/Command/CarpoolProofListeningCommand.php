<?php

namespace App\ExternalService\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarpoolProofListeningCommand extends Command
{
    private $_uri;
    private $_port;
    private $_username;
    private $_password;

    public function __construct(string $uri, int $port, string $username, string $password)
    {
        $this->_uri = $uri;
        $this->_port = $port;
        $this->_username = $username;
        $this->_password = $password;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:external-service:carpool-proof-listening')
            ->setDescription('Listening for proofs by external service.')
            ->setHelp('Listening for proofs by external service.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new AMQPStreamConnection($this->_uri, $this->_port, $this->_username, $this->_password);
        $channel = $connection->channel();

        $channel->exchange_declare('carpool.proof', 'fanout', false, false, false);

        list($queue_name) = $channel->queue_declare('', false, false, true, false);

        $channel->queue_bind($queue_name, 'carpool.proof');

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] ', $msg->getBody(), "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        try {
            $channel->consume();
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }

        $channel->close();
        $connection->close();
    }
}
