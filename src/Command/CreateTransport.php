<?php
namespace EventStreamApi\Command;

use EventStreamApi\DataPersister;
use EventStreamApi\Entity\Transport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTransport extends Command
{
    protected static $defaultName = 'esa:create-transport';

    public function __construct(private DataPersister $dataPersister)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Given the name of a transport and an option base64 encoded public key creates a transport for subscriptions to use.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the transport')
            ->addArgument('public_key', InputArgument::OPTIONAL, 'The base64 encoded public key for return events.', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string|null $publicKey
         */
        $publicKey = $input->getArgument('public_key');
        if ($publicKey && is_string($publicKey)) {
            $publicKey = base64_decode($publicKey);
            if(!$publicKey || !openssl_pkey_get_public($publicKey)) {
                $output->writeln("Invalid public key.");
                return Command::FAILURE;
            }
        }

        $name = $input->getArgument('name');
        if (!is_string($name)) {
            $output->writeln("Invalid name.");
            return Command::FAILURE;
        }

        $transport = new Transport($name, $publicKey);

        $this->dataPersister->persist($transport);

        return Command::SUCCESS;
    }
}