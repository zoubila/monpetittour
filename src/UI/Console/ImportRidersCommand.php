<?php

declare(strict_types=1);

namespace App\UI\Console;

use App\Application\Handler\ImportRidersHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:riders:import')]
final class ImportRidersCommand extends Command
{
    public function __construct(
        private readonly ImportRidersHandler $importRiders,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->importRiders)();
        $output->writeln('Riders imported.');

        return Command::SUCCESS;
    }
}
