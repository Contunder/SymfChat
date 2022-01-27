<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\Helper\WebsocketHelper;

/**
 * @AsCommand( name: 'run:websocket-server',description: 'Run Socket')
 */
class WebsocketServerCommand extends Command
{

    private $doctrine;
    private $entityManager;

    /**
     * @param ManagerRegistry $doctrine
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
    }
    protected function configure(): void
    {
        $this
            ->setName('run:websocket-server')
            ->setDescription('Run Socket');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 3001;
        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebsocketHelper($this->doctrine, $this->entityManager)
                )
            ),
            $port
        );
        $server->run();
        return 0;
    }

}