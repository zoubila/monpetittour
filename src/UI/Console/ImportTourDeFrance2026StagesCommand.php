<?php

declare(strict_types=1);

namespace App\UI\Console;

use App\Application\Handler\ImportTourDeFrance2026StagesHandler;
use App\Infrastructure\External\Letour\LetourScrapingUnavailable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tour-de-france-2026:import-stages')]
final class ImportTourDeFrance2026StagesCommand extends Command
{
    public function __construct(private readonly ImportTourDeFrance2026StagesHandler $importStages)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $importedCount = ($this->importStages)();
        } catch (LetourScrapingUnavailable $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(sprintf('%d Tour de France 2026 stages imported.', $importedCount));

        return Command::SUCCESS;
    }
}
